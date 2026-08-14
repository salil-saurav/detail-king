<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use DetailKing\Theme\Services\Forms\FormService;
use DetailKing\Theme\Services\Booking\BookingWidgetService;

defined('ABSPATH') || exit;

/**
 * Class AssetsService
 *
 * Configuration-driven asset manager with:
 *  - auto-versioning of local files via filemtime (cache busting for free)
 *  - conditional loading (register an asset with a callable condition)
 *  - critical-CSS preload pattern for styles registered via addPreloadStyle()
 *  - O(1) `type="module"` injection for selected script handles
 *
 * Register your project's assets in registerGlobalAssets() / registerPageAssets().
 * The boilerplate ships only Bootstrap, a blank global stylesheet, and the
 * front-end behaviour script — add your own below.
 */
class AssetsService extends Singleton implements ServiceInterface
{
   /** @var array<string, array> Stored script configurations */
   private array $scripts = [];

   /** @var array<string, array> Stored style configurations */
   private array $styles = [];

   /** @var array<string, bool> Handles that require type="module" (O(1) lookup) */
   private array $moduleScriptHandles = [];

   /** @var array<string, bool> Handles that use the preload pattern */
   private array $preloadStyleHandles = [];

   /** @var string Base URL for assets */
   private string $assetUrl;

   /** @var string Base directory path for assets (for filemtime) */
   private string $assetDir;

   private int $priority = 10;

   public function register(): void
   {
      // Parent (template) paths so bundled assets resolve under a child theme.
      $this->assetUrl = get_template_directory_uri() . '/assets';
      $this->assetDir = get_template_directory() . '/assets';

      $this->registerGlobalAssets();
      $this->registerPageAssets();
      $this->registerHooks();
   }

   // -------------------------------------------------------------------------
   // Asset Definitions
   // -------------------------------------------------------------------------

   /**
    * Assets loaded on every page.
    */
   private function registerGlobalAssets(): void
   {
      // Bootstrap 5 (bundled locally; the JS bundle includes Popper, no jQuery).
      $this->addStyle('bootstrap', '/lib/bootstrap/css/bootstrap.min.css', [], '5.3.8');
      $this->addScript('bootstrap', '/lib/bootstrap/js/bootstrap.bundle.min.js', [], '5.3.8', true);

      // Self-hosted Bebas Neue + Poppins (latin + latin-ext). Registered before
      // global.css so the @font-face rules exist when the type scale applies.
      $this->addStyle('dk-fonts', '/css/fonts.css', [], '1.0');

      // Theme global stylesheet + behaviour script.
      $this->addStyle('sp-global', '/css/global.css', ['bootstrap', 'dk-fonts']);
      $this->addScript('sp-main', '/js/global.js', ['bootstrap'], '1.0', true);

      /* Motion layer — implements animation-implementation-spec.md.
         GSAP 3.13 is free including ScrollTrigger (GreenSock standard licence,
         no Club membership needed). Vendored locally rather than CDN-loaded so
         the theme has no third-party runtime dependency. ~42KB gzipped for both
         (GSAP 25, ScrollTrigger 17). motion.js self-disables under
         prefers-reduced-motion.

         Lenis (inertial smooth-scroll, spec's Critical #1) was here and is
         deliberately gone: measured on this page under a synthetic wheel-scroll
         burst, it roughly halved rendered frame throughput (58 vs 106-117
         frames per 3s at 4x CPU throttle) and nearly doubled the worst single
         stall (183ms vs ~110ms) versus native scroll, because it turns one
         wheel tick into many more animation-frames of work (its own decaying
         easing on top of ScrollTrigger's per-frame update). Native scroll plus
         ScrollTrigger showed no measurable idle cost on its own — this was
         Lenis specifically, not the reveal/parallax animations. */
      $this->addScript('gsap', '/lib/motion/gsap.min.js', [], '3.13.0', true);
      $this->addScript('gsap-scrolltrigger', '/lib/motion/ScrollTrigger.min.js', ['gsap'], '3.13.0', true);
      $this->addScript('dk-motion', '/js/motion.js', ['gsap', 'gsap-scrolltrigger'], '1.0', true);

      // Layout chrome — on every page, so unconditional.
      $this->addStyle('dk-header', '/css/layout/header.css', ['sp-global']);
      $this->addStyle('dk-footer', '/css/layout/footer.css', ['sp-global']);

      // Custom-form (lead capture) AJAX handler. Loaded site-wide because forms
      // can appear anywhere; it self-disables when no [data-sp-form] element is
      // present on the page.
      $this->addScript('sp-forms', '/js/forms.js', [], '1.0', true);

      // Recommendation modal / cross-sell add-to-cart. Same "loaded site-wide,
      // self-disables" reasoning as sp-forms above — [data-dk-add-to-cart]
      // isn't confined to Woo/single-product contexts (membership-card.php's
      // CTA renders on the homepage), so a Woo-context-only gate silently
      // stranded that button with no listener attached. Costs nothing on a
      // page with none of its selectors present, same as the header search
      // overlay SearchService already renders unconditionally.
      $this->addScript('dk-cross-sell', '/js/cross-sell.js', [], '1.0', true);
   }

   /**
    * Page-conditional assets. Each is enqueued only when its condition returns
    * true. Add per-template / per-context stylesheets and scripts here.
    */
   private function registerPageAssets(): void
   {
      // Homepage. is_front_page() covers both a static front page and the posts
      // index being the front page; either way front-page.php is what renders.
      $isHome = static fn(): bool => is_front_page();

      $this->addStyle('dk-home', '/css/pages/home.css', ['sp-global'], '1.0', 'all', $isHome);
      $this->addScript('dk-home', '/js/pages/home.js', [], '1.0', true, $isHome);

      $isAbout = static fn(): bool => is_page_template('pages/template-about.php');

      $this->addStyle('dk-about', '/css/pages/about.css', ['sp-global'], '1.0', 'all', $isAbout);

      $isServices = static fn(): bool => is_page_template('pages/template-services.php');

      $this->addStyle('dk-services', '/css/pages/services.css', ['sp-global'], '1.0', 'all', $isServices);

      $isSingleService = static fn(): bool => is_singular('dk_service');

      $this->addStyle('dk-single-service', '/css/pages/single-service.css', ['sp-global'], '1.0', 'all', $isSingleService);
      $this->addScript('dk-booking-widget', '/js/booking-widget.js', [], '1.0', true, $isSingleService);

      $this->addStyle('dk-shop', '/css/pages/shop.css', ['sp-global'], '1.0', 'all', [$this, 'isWooContext']);
      $this->addScript('dk-shop', '/js/pages/shop.js', [], '1.0', true, [$this, 'isWooContext']);

      $this->addStyle('dk-account', '/css/pages/account.css', ['sp-global'], '1.0', 'all', static fn(): bool => is_account_page());

      $isGallery = static fn(): bool => is_page_template('pages/template-gallery.php');

      $this->addStyle('dk-gallery', '/css/pages/gallery.css', ['sp-global'], '1.0', 'all', $isGallery);

      $isContact = static fn(): bool => is_page_template('pages/template-contact.php');

      $this->addStyle('dk-contact', '/css/pages/contact.css', ['sp-global'], '1.0', 'all', $isContact);

      // Blog index (home.php, the posts page) and single post (single.php) share
      // one stylesheet.
      $isBlog = static fn(): bool => is_home() || is_singular('post');

      $this->addStyle('dk-blog', '/css/pages/blog.css', ['sp-global'], '1.0', 'all', $isBlog);
   }

   // -------------------------------------------------------------------------
   // Registration Helpers
   // -------------------------------------------------------------------------

   /**
    * @param array<string>         $deps
    * @param array<string, string> $attributes
    */
   public function addScript(
      string $handle,
      string $src,
      array $deps = [],
      string $ver = '1.0.0',
      bool $defer = true,
      ?callable $condition = null,
      array $attributes = []
   ): void {
      $src = $this->normalizeSource($src);
      $ver = $this->resolveVersion($src, $ver);

      $this->scripts[$handle] = compact('handle', 'src', 'deps', 'ver', 'defer', 'condition', 'attributes');

      if (($attributes['type'] ?? '') === 'module') {
         $this->moduleScriptHandles[$handle] = true;
      }
   }

   /**
    * @param array<string> $deps
    */
   public function addStyle(
      string $handle,
      string $src,
      array $deps = [],
      string $ver = '1.0.0',
      string $media = 'all',
      ?callable $condition = null
   ): void {
      $src = $this->normalizeSource($src);
      $ver = $this->resolveVersion($src, $ver);

      $this->styles[$handle] = compact('handle', 'src', 'deps', 'ver', 'media', 'condition');
   }

   public function addPreloadStyle(
      string $handle,
      string $src,
      array $deps = [],
      string $ver = '1.0.0',
      string $media = 'all',
      ?callable $condition = null
   ): void {
      $this->addStyle($handle, $src, $deps, $ver, $media, $condition);
      $this->preloadStyleHandles[$handle] = true;
   }

   // -------------------------------------------------------------------------
   // Execution
   // -------------------------------------------------------------------------

   public function enqueueAssets(): void
   {
      foreach ($this->scripts as $script) {
         if (!$this->checkCondition($script['condition'])) {
            continue;
         }

         // WordPress 6.3+ supports 'strategy' => 'defer'
         $args = [];
         if ($script['defer']) {
            $args['strategy'] = 'defer';
         }

         wp_enqueue_script($script['handle'], $script['src'], $script['deps'], $script['ver'], $args);
      }

      foreach ($this->styles as $style) {
         if (!$this->checkCondition($style['condition'])) {
            continue;
         }

         wp_enqueue_style($style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media']);
      }

      // Threaded-comments reply script on singular views.
      if (is_singular() && comments_open() && get_option('thread_comments')) {
         wp_enqueue_script('comment-reply');
      }

      // DebloaterService deregisters frontend jQuery, but WooCommerce hard-depends
      // on it (cart fragments, variation forms, checkout). Put it back, but only
      // in shop contexts, so the marketing pages stay jQuery-free.
      if ($this->isWooContext()) {
         wp_enqueue_script('jquery');
      }

      // Hand the form handler its REST root, session nonce and time-trap token.
      if (wp_script_is('sp-forms', 'enqueued')) {
         wp_localize_script('sp-forms', 'DetailKingForms', FormService::getInstance()->frontendData());
      }

      // Same for the single-service booking widget's own REST endpoint.
      if (wp_script_is('dk-booking-widget', 'enqueued')) {
         wp_localize_script('dk-booking-widget', 'DetailKingBooking', BookingWidgetService::getInstance()->frontendData());
      }

      // Same for the recommendation modal / cross-sell REST endpoint.
      if (wp_script_is('dk-cross-sell', 'enqueued')) {
         wp_localize_script('dk-cross-sell', 'DetailKingCrossSell', \DetailKing\Theme\Services\CrossSell\CrossSellService::getInstance()->frontendData());
      }
   }

   /**
    * Optimized filter callback. O(1) lookup instead of O(N).
    */
   public function applyScriptAttributes(string $tag, string $handle): string
   {
      if (isset($this->moduleScriptHandles[$handle])) {
         if (strpos($tag, 'type="module"') === false) {
            return str_replace('<script ', '<script type="module" ', $tag);
         }
      }
      return $tag;
   }

   // -------------------------------------------------------------------------
   // Hooks & Utilities
   // -------------------------------------------------------------------------

   /**
    * True in any WooCommerce front-end context that needs jQuery.
    *
    * Guarded with function_exists() throughout so the theme still boots with
    * WooCommerce deactivated.
    */
   private function isWooContext(): bool
   {
      if (!function_exists('is_woocommerce')) {
         return false;
      }

      return is_woocommerce()
         || is_cart()
         || is_checkout()
         || is_account_page()
         || is_wc_endpoint_url();
   }

   /**
    * Preload the two font files that are on the critical path for every page —
    * the Bebas display face and Poppins Light — so the first heading and the
    * first paragraph do not swap. The other weights load normally.
    */
   public function preloadFonts(): void
   {
      $fonts = [
         '/fonts/bebas-neue-400-latin.woff2',
         '/fonts/poppins-300-latin.woff2',
      ];

      foreach ($fonts as $font) {
         if (!is_file($this->assetDir . $font)) {
            continue;
         }

         printf(
            '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
            esc_url($this->assetUrl . $font)
         );
      }
   }

   private function registerHooks(): void
   {
      add_action('wp_enqueue_scripts', [$this, 'enqueueAssets'], $this->priority);
      add_action('wp_head', [$this, 'preloadFonts'], 1);
      add_filter('script_loader_tag', [$this, 'applyScriptAttributes'], 10, 2);

      add_filter('style_loader_tag', function (string $tag, string $handle, string $href): string {
         if (!isset($this->preloadStyleHandles[$handle])) {
            return $tag;
         }

         // Critical CSS preload pattern.
         $preload  = "<link rel='preload' as='style' href='{$href}' onload=\"this.onload=null;this.rel='stylesheet'\">";
         $noscript = "<noscript>{$tag}</noscript>";

         return $preload . $noscript;
      }, 10, 3);
   }

   private function checkCondition(?callable $condition): bool
   {
      return $condition === null || $condition();
   }

   /**
    * Convert relative paths to full URLs. External / protocol-relative URLs
    * are returned unchanged.
    */
   private function normalizeSource(string $src): string
   {
      if (str_starts_with($src, 'http') || str_starts_with($src, '//')) {
         return $src;
      }

      $src = '/' . ltrim($src, '/');
      return $this->assetUrl . $src;
   }

   /**
    * Auto-version local assets based on file modification time. External assets
    * keep their explicitly provided version.
    */
   private function resolveVersion(string $src, string $defaultVer): string
   {
      if (!str_starts_with($src, $this->assetUrl)) {
         return $defaultVer;
      }

      $relativePath = substr($src, strlen($this->assetUrl));
      $filePath     = $this->assetDir . $relativePath;

      if (is_file($filePath)) {
         $mtime = filemtime($filePath);
         if ($mtime !== false) {
            return (string) $mtime;
         }
      }

      return $defaultVer;
   }
}
