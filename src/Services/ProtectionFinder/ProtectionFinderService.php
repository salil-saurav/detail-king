<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\ProtectionFinder;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined('ABSPATH') || exit;

/**
 * Protection Finder — read-only scoring endpoint (REST detailking/v1/protection-finder).
 *
 * The weight matrix below is transcribed verbatim from
 * build/figma-data/protection-finder-scoring.md, authored 14 Aug 2026 as a
 * deliberate, internally-consistent placeholder — the client never supplied a
 * real scoring model (TASK-BRIEF.md §5). Do not re-weight it here; edit the
 * spec file and this constant together if it ever needs to change.
 *
 * Unlike BookingWidgetService/PackageBuilderService, this writes nothing — it
 * takes 5 answers and returns a computed match, nothing more.
 */
class ProtectionFinderService extends Singleton implements ServiceInterface
{
   public const REST_NAMESPACE = 'detailking/v1';

   /** category slug (real dk_service slug) => question => option => weight (0-3) */
   private const WEIGHTS = [
      'grooming' => [
         'vehicle'   => ['daily-driver' => 3, 'luxury-sports' => 1, 'suv-ute' => 2, 'brand-new' => 1],
         'priority'  => ['deep-gloss' => 2, 'chip-protection' => 0, 'easy-cleaning' => 3, 'comfort-privacy' => 1],
         'usage'     => ['city-short-trips' => 3, 'lots-of-highway' => 1, 'weekend-pride' => 1, 'work-tough-use' => 2],
         'longevity' => ['a-season' => 3, 'a-few-years' => 1, 'maximum-permanent' => 0],
         'budget'    => ['smart-value' => 3, 'balanced' => 1, 'premium' => 0],
      ],
      'detailing' => [
         'vehicle'   => ['daily-driver' => 2, 'luxury-sports' => 2, 'suv-ute' => 1, 'brand-new' => 3],
         'priority'  => ['deep-gloss' => 3, 'chip-protection' => 0, 'easy-cleaning' => 1, 'comfort-privacy' => 1],
         'usage'     => ['city-short-trips' => 1, 'lots-of-highway' => 1, 'weekend-pride' => 3, 'work-tough-use' => 0],
         'longevity' => ['a-season' => 3, 'a-few-years' => 1, 'maximum-permanent' => 0],
         'budget'    => ['smart-value' => 1, 'balanced' => 3, 'premium' => 2],
      ],
      'ceramic-pro' => [
         'vehicle'   => ['daily-driver' => 1, 'luxury-sports' => 3, 'suv-ute' => 1, 'brand-new' => 3],
         'priority'  => ['deep-gloss' => 3, 'chip-protection' => 1, 'easy-cleaning' => 3, 'comfort-privacy' => 1],
         'usage'     => ['city-short-trips' => 1, 'lots-of-highway' => 2, 'weekend-pride' => 2, 'work-tough-use' => 1],
         'longevity' => ['a-season' => 0, 'a-few-years' => 3, 'maximum-permanent' => 2],
         'budget'    => ['smart-value' => 0, 'balanced' => 2, 'premium' => 3],
      ],
      'ppf' => [
         'vehicle'   => ['daily-driver' => 1, 'luxury-sports' => 3, 'suv-ute' => 1, 'brand-new' => 2],
         'priority'  => ['deep-gloss' => 1, 'chip-protection' => 3, 'easy-cleaning' => 0, 'comfort-privacy' => 0],
         'usage'     => ['city-short-trips' => 2, 'lots-of-highway' => 3, 'weekend-pride' => 1, 'work-tough-use' => 2],
         'longevity' => ['a-season' => 2, 'a-few-years' => 2, 'maximum-permanent' => 3],
         'budget'    => ['smart-value' => 0, 'balanced' => 1, 'premium' => 3],
      ],
      'window-tinting' => [
         'vehicle'   => ['daily-driver' => 3, 'luxury-sports' => 1, 'suv-ute' => 2, 'brand-new' => 1],
         'priority'  => ['deep-gloss' => 0, 'chip-protection' => 0, 'easy-cleaning' => 1, 'comfort-privacy' => 3],
         'usage'     => ['city-short-trips' => 3, 'lots-of-highway' => 1, 'weekend-pride' => 0, 'work-tough-use' => 1],
         'longevity' => ['a-season' => 0, 'a-few-years' => 3, 'maximum-permanent' => 1],
         'budget'    => ['smart-value' => 3, 'balanced' => 2, 'premium' => 0],
      ],
      'vinyl-wraps' => [
         'vehicle'   => ['daily-driver' => 0, 'luxury-sports' => 2, 'suv-ute' => 1, 'brand-new' => 3],
         'priority'  => ['deep-gloss' => 1, 'chip-protection' => 0, 'easy-cleaning' => 0, 'comfort-privacy' => 0],
         'usage'     => ['city-short-trips' => 0, 'lots-of-highway' => 0, 'weekend-pride' => 3, 'work-tough-use' => 1],
         'longevity' => ['a-season' => 1, 'a-few-years' => 2, 'maximum-permanent' => 1],
         'budget'    => ['smart-value' => 0, 'balanced' => 1, 'premium' => 3],
      ],
   ];

   /** Tie-break order (first in this list wins an exact score tie). */
   private const TIE_BREAK = ['ppf', 'ceramic-pro', 'detailing', 'vinyl-wraps', 'window-tinting', 'grooming'];

   private const QUESTIONS = ['vehicle', 'priority', 'usage', 'longevity', 'budget'];

   public function register(): void
   {
      add_action('rest_api_init', [$this, 'registerRoutes']);
      add_action('wp_enqueue_scripts', [$this, 'enqueueAssets'], 20);
   }

   public function registerRoutes(): void
   {
      register_rest_route(self::REST_NAMESPACE, '/protection-finder', [
         'methods'             => WP_REST_Server::CREATABLE,
         'callback'            => [$this, 'handle'],
         'permission_callback' => [$this, 'verifyNonce'],
      ]);
   }

   public function verifyNonce(WP_REST_Request $request): bool|WP_Error
   {
      $nonce = $request->get_header('x_wp_nonce') ?: (string) $request->get_param('_wpnonce');

      if (!wp_verify_nonce($nonce, 'wp_rest')) {
         return new WP_Error(
            'dk_pf_invalid_nonce',
            __('Your session has expired. Please reload the page and try again.', 'detailking'),
            ['status' => 403]
         );
      }

      return true;
   }

   public function enqueueAssets(): void
   {
      if (!is_page_template('pages/template-protection-finder.php') && !is_page('protection-finder')) {
         return;
      }

      $themeUri = get_template_directory_uri();
      $themeDir = get_template_directory();

      $cssPath = '/assets/css/pages/protection-finder.css';
      wp_enqueue_style(
         'dk-protection-finder',
         $themeUri . $cssPath,
         ['sp-global', 'dk-fonts'],
         file_exists($themeDir . $cssPath) ? (string) filemtime($themeDir . $cssPath) : '1.0.0'
      );

      $jsPath = '/assets/js/pages/protection-finder.js';
      wp_enqueue_script(
         'dk-protection-finder',
         $themeUri . $jsPath,
         ['sp-main'],
         file_exists($themeDir . $jsPath) ? (string) filemtime($themeDir . $jsPath) : '1.0.0',
         ['strategy' => 'defer']
      );

      wp_localize_script('dk-protection-finder', 'DetailKingProtectionFinder', $this->frontendData());
   }

   public function frontendData(): array
   {
      return [
         'restUrl' => esc_url_raw(rest_url(self::REST_NAMESPACE . '/protection-finder')),
         'nonce'   => wp_create_nonce('wp_rest'),
      ];
   }

   // -------------------------------------------------------------------------
   // Scoring
   // -------------------------------------------------------------------------

   public function handle(WP_REST_Request $request): WP_REST_Response
   {
      $answers = [];

      foreach (self::QUESTIONS as $q) {
         $val = sanitize_key((string) $request->get_param($q));

         if ($val === '' || !isset(self::WEIGHTS['grooming'][$q][$val])) {
            return new WP_REST_Response([
               'success' => false,
               'message' => __('Please answer all 5 questions.', 'detailking'),
            ], 422);
         }

         $answers[$q] = $val;
      }

      $scores = $this->score($answers);

      arsort($scores);
      $ranked = array_keys($scores);

      // Tie-break exact-score ties by the declared priority order rather than
      // trusting arsort's insertion-order stability (correct today, but the
      // rubric doc's own tie-break list is the source of truth, not PHP's
      // sort implementation detail).
      $topScore = $scores[$ranked[0]];
      $tiedAtTop = array_filter($ranked, static fn($slug) => $scores[$slug] === $topScore);

      if (count($tiedAtTop) > 1) {
         foreach (self::TIE_BREAK as $slug) {
            if (in_array($slug, $tiedAtTop, true)) {
               $ranked = array_values(array_unique(array_merge([$slug], $ranked)));
               break;
            }
         }
      }

      $topSlug = $ranked[0];

      $remaining = array_values(array_filter($ranked, static fn($s) => $s !== $topSlug));
      $secondScore = $scores[$remaining[0]] ?? null;
      $tiedAtSecond = $secondScore === null ? [] : array_filter($remaining, static fn($slug) => $scores[$slug] === $secondScore);

      if (count($tiedAtSecond) > 1) {
         foreach (self::TIE_BREAK as $slug) {
            if ($slug !== $topSlug && in_array($slug, $tiedAtSecond, true)) {
               $remaining = array_values(array_unique(array_merge([$slug], $remaining)));
               break;
            }
         }
      }

      $runnerSlug = $remaining[0] ?? null;

      $topService    = $this->findService($topSlug);
      $runnerService = $runnerSlug ? $this->findService($runnerSlug) : null;

      if (!$topService) {
         return new WP_REST_Response([
            'success' => false,
            'message' => __('Could not compute a match right now. Please try again.', 'detailking'),
         ], 500);
      }

      $maxScore = $this->categoryMax($topSlug);
      $matchPct = $maxScore > 0 ? (int) round(($scores[$topSlug] / $maxScore) * 100) : 0;

      return new WP_REST_Response([
         'success'  => true,
         'matchPct' => $matchPct,
         'service'  => $this->serviceOutput($topService),
         'runnerUp' => $runnerService ? [
            'slug'      => $runnerService->post_name,
            'title'     => wp_specialchars_decode(get_the_title($runnerService), ENT_QUOTES),
            'permalink' => get_permalink($runnerService),
         ] : null,
      ], 200);
   }

   /** @param array<string,string> $answers @return array<string,int> */
   private function score(array $answers): array
   {
      $scores = [];

      foreach (self::WEIGHTS as $slug => $questions) {
         $total = 0;
         foreach ($answers as $q => $optionKey) {
            $total += $questions[$q][$optionKey] ?? 0;
         }
         $scores[$slug] = $total;
      }

      return $scores;
   }

   private function categoryMax(string $slug): int
   {
      $questions = self::WEIGHTS[$slug] ?? [];
      $max = 0;

      foreach ($questions as $options) {
         $max += !empty($options) ? max($options) : 0;
      }

      return $max;
   }

   private function findService(string $slug): ?WP_Post
   {
      $posts = get_posts([
         'post_type'        => 'dk_service',
         'name'              => $slug,
         'numberposts'       => 1,
         'post_status'       => 'publish',
         'suppress_filters'  => false,
      ]);

      return $posts[0] ?? null;
   }

   /** @return array<string,mixed> */
   private function serviceOutput(WP_Post $service): array
   {
      $features = get_field('service_features', $service->ID) ?: [];
      $features = is_array($features) ? array_slice($features, 0, 4) : [];

      $featureLabels = [];
      foreach ($features as $row) {
         $text = is_array($row) ? (string) ($row['feature_text'] ?? reset($row) ?: '') : (string) $row;
         if ($text !== '') {
            $featureLabels[] = $text;
         }
      }

      return [
         'slug'      => $service->post_name,
         'title'     => wp_specialchars_decode(get_the_title($service), ENT_QUOTES),
         'permalink' => get_permalink($service),
         'teaser'    => (string) (get_field('service_teaser', $service->ID) ?: ''),
         'features'  => $featureLabels,
         'fromPrice' => $this->minPackagePrice($service->ID),
      ];
   }

   /**
    * A la carte add-on dk_package rows that are linked to a real dk_service
    * post (so they show up in a plain package_service query) but are not one
    * of that service's own priced tiers — same exclusion lists
    * build-package/service.php already hardcodes for the identical reason
    * (BYOP's radio/checkbox split). Keep both lists in sync if either changes.
    */
   private const NON_TIER_PACKAGE_SLUGS = [
      'ppf-trunk-edge-kit', 'ppf-door-handle-protection', 'ppf-door-edge-protection',
      'ppf-rocker-panel-protection', 'ppf-splash-kit',
      'ceramic-pro-wheels-caliper', 'ceramic-pro-car-interior-coating',
   ];

   /** MIN(package_price) across real, published dk_package rows for this service — computed live, not hardcoded, so a real price edit flows straight through. */
   private function minPackagePrice(int $serviceId): ?int
   {
      $packages = get_posts([
         'post_type'        => 'dk_package',
         'numberposts'       => -1,
         'post_status'       => 'publish',
         'suppress_filters'  => false,
         'meta_query'        => [[
            'key'   => 'package_service',
            'value' => $serviceId,
         ]],
      ]);

      $prices = [];
      foreach ($packages as $package) {
         if (in_array($package->post_name, self::NON_TIER_PACKAGE_SLUGS, true)) {
            continue;
         }
         $price = get_field('package_price', $package->ID);
         if ($price !== '' && $price !== null) {
            $prices[] = (int) $price;
         }
      }

      return $prices ? min($prices) : null;
   }
}
