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
 * The weight matrix below is transcribed verbatim from the client's "PPF
 * calculator" doc (Google Doc, supplied 17 Aug 2026 — the calculation Shreya
 * promised on 29 Jul, TASK-BRIEF §5), replacing the 14 Aug placeholder rubric.
 * It supersedes the old 6-category model (grooming/detailing/ceramic-pro/ppf/
 * window-tinting/vinyl-wraps) entirely — the doc only ever scores 4 PPF /
 * Ceramic Pro outcomes, so this finder no longer recommends the other four
 * services (still reachable via Our Services). Full rationale + the
 * assumptions made where the doc was silent or ambiguous:
 * build/figma-data/protection-finder-scoring.md. Do not re-weight the matrix
 * here without updating that spec file too.
 *
 * Unlike BookingWidgetService/PackageBuilderService, this writes nothing — it
 * takes 5 answers and returns a computed match, nothing more.
 */
class ProtectionFinderService extends Singleton implements ServiceInterface
{
   public const REST_NAMESPACE = 'detailking/v1';

   /** The one dk_service every outcome below results in — all 4 are PPF tiers/finishes. */
   private const SERVICE_SLUG = 'ppf';

   /**
    * question => option => outcome slug => weight. Transcribed row-for-row
    * from the doc's Points Table.
    */
   private const WEIGHTS = [
      'vehicle' => [
         'luxury-sports' => ['full-car-ppf' => 15, 'stealth-matte-ppf' => 15, 'full-front-ppf-ceramic' => 10, 'front-basic-ppf-ceramic' => 5],
         'suv-ute'       => ['full-car-ppf' => 10, 'stealth-matte-ppf' => 10, 'full-front-ppf-ceramic' => 15, 'front-basic-ppf-ceramic' => 10],
         'sedan-hatch'   => ['full-car-ppf' => 10, 'stealth-matte-ppf' => 10, 'full-front-ppf-ceramic' => 10, 'front-basic-ppf-ceramic' => 10],
         'new-car'       => ['full-car-ppf' => 15, 'stealth-matte-ppf' => 15, 'full-front-ppf-ceramic' => 15, 'front-basic-ppf-ceramic' => 10],
      ],
      'priority' => [
         'stone-chips'        => ['full-car-ppf' => 20, 'stealth-matte-ppf' => 20, 'full-front-ppf-ceramic' => 15, 'front-basic-ppf-ceramic' => 10],
         'scratches'          => ['full-car-ppf' => 15, 'stealth-matte-ppf' => 15, 'full-front-ppf-ceramic' => 10, 'front-basic-ppf-ceramic' => 5],
         'sun-uv'             => ['full-car-ppf' => 5,  'stealth-matte-ppf' => 5,  'full-front-ppf-ceramic' => 15, 'front-basic-ppf-ceramic' => 15],
         'general-protection' => ['full-car-ppf' => 10, 'stealth-matte-ppf' => 10, 'full-front-ppf-ceramic' => 10, 'front-basic-ppf-ceramic' => 10],
         // 5th option, not in the doc's Q2 bullet list but scored in its table — see wizard.php's doc comment.
         'matte-stealth-look' => ['full-car-ppf' => 10, 'stealth-matte-ppf' => 20, 'full-front-ppf-ceramic' => 5,  'front-basic-ppf-ceramic' => 0],
      ],
      'usage' => [
         'city-driving'       => ['full-car-ppf' => 5,  'stealth-matte-ppf' => 5,  'full-front-ppf-ceramic' => 10, 'front-basic-ppf-ceramic' => 15],
         'highway-driving'    => ['full-car-ppf' => 20, 'stealth-matte-ppf' => 20, 'full-front-ppf-ceramic' => 15, 'front-basic-ppf-ceramic' => 10],
         'weekend-occasional' => ['full-car-ppf' => 5,  'stealth-matte-ppf' => 5,  'full-front-ppf-ceramic' => 10, 'front-basic-ppf-ceramic' => 10],
         'work-offroad'       => ['full-car-ppf' => 20, 'stealth-matte-ppf' => 20, 'full-front-ppf-ceramic' => 15, 'front-basic-ppf-ceramic' => 10],
      ],
      'level' => [
         'basic-protection'    => ['full-car-ppf' => 0,  'stealth-matte-ppf' => 0,  'full-front-ppf-ceramic' => 5,  'front-basic-ppf-ceramic' => 20],
         'front-protection'    => ['full-car-ppf' => 10, 'stealth-matte-ppf' => 10, 'full-front-ppf-ceramic' => 20, 'front-basic-ppf-ceramic' => 20],
         'full-car-protection' => ['full-car-ppf' => 20, 'stealth-matte-ppf' => 20, 'full-front-ppf-ceramic' => 15, 'front-basic-ppf-ceramic' => 5],
         'maximum-protection'  => ['full-car-ppf' => 20, 'stealth-matte-ppf' => 20, 'full-front-ppf-ceramic' => 15, 'front-basic-ppf-ceramic' => 5],
      ],
      'budget' => [
         'best-value' => ['full-car-ppf' => 5,  'stealth-matte-ppf' => 0,  'full-front-ppf-ceramic' => 10, 'front-basic-ppf-ceramic' => 20],
         'mid-range'  => ['full-car-ppf' => 10, 'stealth-matte-ppf' => 5,  'full-front-ppf-ceramic' => 20, 'front-basic-ppf-ceramic' => 20],
         'premium'    => ['full-car-ppf' => 20, 'stealth-matte-ppf' => 20, 'full-front-ppf-ceramic' => 15, 'front-basic-ppf-ceramic' => 10],
      ],
   ];

   /**
    * outcome slug => display title + the real dk_package tier (under `ppf`)
    * it resolves to. `full-car-ppf` and `stealth-matte-ppf` deliberately
    * share `ppf-full-cover` — there is no separate Stealth/Matte package yet,
    * so both outcomes point at the same tier/page and differ only in label.
    */
   private const OUTCOMES = [
      'full-car-ppf' => [
         'title'       => 'Full Car PPF',
         'packageSlug' => 'ppf-full-cover',
      ],
      'stealth-matte-ppf' => [
         'title'       => 'Full Car Stealth / Matte PPF',
         'packageSlug' => 'ppf-full-cover',
      ],
      'full-front-ppf-ceramic' => [
         'title'       => 'Full Front PPF + Ceramic Pro',
         'packageSlug' => 'ppf-full-front',
         'ceramicNote' => 'Paired with a Ceramic Pro coating across the rest of the vehicle for complete, all-over protection.',
      ],
      'front-basic-ppf-ceramic' => [
         'title'       => 'Front Basic PPF + Ceramic Pro',
         'packageSlug' => 'ppf-basic',
         'ceramicNote' => 'Paired with a Ceramic Pro coating across the rest of the vehicle for complete, all-over protection.',
      ],
   ];

   /**
    * Tie-break order when two outcomes score exactly equal. `stealth-matte-ppf`
    * sits last by default — Full Car PPF is the "home" outcome for that score
    * band — but moves to the front when the visitor's own priority answer was
    * "Matte / Stealth Look", per the doc's explicit override ("if the customer
    * selects Matte / Stealth Look, and the Stealth score is highest, recommend
    * Full Car Stealth / Matte PPF rather than Full Car PPF").
    */
   private const TIE_BREAK_DEFAULT  = ['full-car-ppf', 'full-front-ppf-ceramic', 'front-basic-ppf-ceramic', 'stealth-matte-ppf'];
   private const TIE_BREAK_STEALTH  = ['stealth-matte-ppf', 'full-car-ppf', 'full-front-ppf-ceramic', 'front-basic-ppf-ceramic'];

   private const QUESTIONS = ['vehicle', 'priority', 'usage', 'level', 'budget'];

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

         if ($val === '' || !isset(self::WEIGHTS[$q][$val])) {
            return new WP_REST_Response([
               'success' => false,
               'message' => __('Please answer all 5 questions.', 'detailking'),
            ], 422);
         }

         $answers[$q] = $val;
      }

      $scores   = $this->score($answers);
      $tieBreak = ($answers['priority'] ?? null) === 'matte-stealth-look'
         ? self::TIE_BREAK_STEALTH
         : self::TIE_BREAK_DEFAULT;

      arsort($scores);
      $ranked = array_keys($scores);

      // Tie-break exact-score ties by the declared priority order rather than
      // trusting arsort's insertion-order stability.
      $topScore = $scores[$ranked[0]];
      $tiedAtTop = array_filter($ranked, static fn($slug) => $scores[$slug] === $topScore);

      if (count($tiedAtTop) > 1) {
         foreach ($tieBreak as $slug) {
            if (in_array($slug, $tiedAtTop, true)) {
               $ranked = array_values(array_unique(array_merge([$slug], $ranked)));
               break;
            }
         }
      }

      $topSlug    = $ranked[0];
      $topOutcome = self::OUTCOMES[$topSlug];
      $topPackage = $this->findPackage($topOutcome['packageSlug']);
      $service    = $this->findService();

      if (!$topPackage || !$service) {
         return new WP_REST_Response([
            'success' => false,
            'message' => __('Could not compute a match right now. Please try again.', 'detailking'),
         ], 500);
      }

      $remaining = array_values(array_filter($ranked, static fn($s) => $s !== $topSlug));

      // Drop runner-up candidates that resolve to the exact same package tier
      // as the top pick — e.g. Full Car PPF / Stealth-Matte PPF share
      // ppf-full-cover, and "also worth considering" the identical tier under
      // a different label is a redundant link to the same page, not a
      // genuinely different second option.
      $remaining = array_values(array_filter(
         $remaining,
         static fn($s) => self::OUTCOMES[$s]['packageSlug'] !== $topOutcome['packageSlug']
      ));

      $secondScore  = isset($remaining[0]) ? $scores[$remaining[0]] : null;
      $tiedAtSecond = $secondScore === null ? [] : array_filter($remaining, static fn($slug) => $scores[$slug] === $secondScore);

      if (count($tiedAtSecond) > 1) {
         foreach ($tieBreak as $slug) {
            if ($slug !== $topSlug && in_array($slug, $tiedAtSecond, true)) {
               $remaining = array_values(array_unique(array_merge([$slug], $remaining)));
               break;
            }
         }
      }

      $runnerSlug    = $remaining[0] ?? null;
      $runnerOutcome = $runnerSlug ? self::OUTCOMES[$runnerSlug] : null;

      $maxScore = $this->outcomeMax($topSlug);
      $matchPct = $maxScore > 0 ? (int) round(($scores[$topSlug] / $maxScore) * 100) : 0;

      return new WP_REST_Response([
         'success'  => true,
         'matchPct' => $matchPct,
         'service'  => $this->resultOutput($topOutcome, $service, $topPackage),
         'runnerUp' => $runnerOutcome ? [
            'slug'      => $runnerSlug,
            'title'     => $runnerOutcome['title'],
            'permalink' => get_permalink($service),
         ] : null,
      ], 200);
   }

   /** @param array<string,string> $answers @return array<string,int> */
   private function score(array $answers): array
   {
      $scores = array_fill_keys(array_keys(self::OUTCOMES), 0);

      foreach ($answers as $question => $optionKey) {
         foreach (self::WEIGHTS[$question][$optionKey] ?? [] as $outcomeSlug => $weight) {
            $scores[$outcomeSlug] += $weight;
         }
      }

      return $scores;
   }

   /** Sum of each question's own best weight for this outcome — not a flat constant, in case a future edit changes it. */
   private function outcomeMax(string $outcomeSlug): int
   {
      $max = 0;

      foreach (self::WEIGHTS as $options) {
         $best = 0;
         foreach ($options as $weights) {
            $best = max($best, $weights[$outcomeSlug] ?? 0);
         }
         $max += $best;
      }

      return $max;
   }

   private function findService(): ?WP_Post
   {
      $posts = get_posts([
         'post_type'        => 'dk_service',
         'name'             => self::SERVICE_SLUG,
         'numberposts'      => 1,
         'post_status'      => 'publish',
         'suppress_filters' => false,
      ]);

      return $posts[0] ?? null;
   }

   private function findPackage(string $slug): ?WP_Post
   {
      $posts = get_posts([
         'post_type'        => 'dk_package',
         'name'             => $slug,
         'numberposts'      => 1,
         'post_status'      => 'publish',
         'suppress_filters' => false,
      ]);

      return $posts[0] ?? null;
   }

   /** @return array<string,mixed> */
   private function resultOutput(array $outcome, WP_Post $service, WP_Post $package): array
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

      $teaser = (string) (get_field('service_teaser', $service->ID) ?: '');
      if (!empty($outcome['ceramicNote'])) {
         $teaser = trim($teaser . ' ' . $outcome['ceramicNote']);
      }

      $price = get_field('package_price', $package->ID);

      return [
         'slug'      => $package->post_name,
         'title'     => $outcome['title'],
         'permalink' => get_permalink($service),
         'teaser'    => $teaser,
         'features'  => $featureLabels,
         'fromPrice' => ($price !== '' && $price !== null) ? (int) $price : null,
      ];
   }
}
