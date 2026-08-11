<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Class DebloaterService
 *
 * Centralized service for disabling WordPress bloat, enforcing the Classic
 * Editor, and cleaning up the Admin/Frontend environment.
 *
 * All cleanup features are filterable via 'detailking/theme/debloater/config'.
 */
class DebloaterService extends Singleton implements ServiceInterface
{
   private array $config;

   public function register(): void
   {
      $this->config = $this->getConfig();

      if (is_admin()) {
         $this->registerAdminCleanup();
      }

      $this->registerGlobalCleanup();
      $this->registerCommentDisabling();
      $this->registerClassicEditor();
   }

   private function getConfig(): array
   {
      $config = [
         'admin_bar_nodes' => [
            'wp-logo',
            'about',
            'wporg',
            'documentation',
            'support-forums',
            'feedback',
            'updates',
            'comments',
            'customize'
         ],
         'dashboard_widgets' => [
            'dashboard_primary',
            'dashboard_secondary',
            'dashboard_quick_press',
            'dashboard_activity',
            'dashboard_right_now',
            'dashboard_recent_comments'
         ],
         'frontend_styles' => [
            'wp-block-library',
            'wp-block-library-theme',
            'classic-theme-styles',
            'global-styles',
            'wc-block-style'
         ],
         'frontend_scripts' => [
            'wp-emoji-release',
            'wp-embed',
            // 'jquery-migrate'
         ],
         /* WooCommerce registers these on every front-end request regardless of
            whether the page has any WooCommerce content — the header's cart link
            is themed entirely by dk-nav__cart in header.css and needs none of it.
            136KB of CSS + 2 scripts, dequeued everywhere except actual shop
            pages (kept there since those templates DO use WC's own markup). */
         'woocommerce_styles' => [
            'wc-blocks-style',
            'woocommerce-layout',
            'woocommerce-smallscreen',
            'woocommerce-general',
         ],
         'woocommerce_scripts' => [
            'sourcebuster-js',
            'wc-order-attribution',
         ],
         'head_cleanup' => [
            'wp_generator',
            'rsd_link',
            'wlwmanifest_link',
            'wp_shortlink_wp_head',
            'rest_output_link_wp_head',
            'wp_oembed_add_discovery_links',
            'wp_oembed_add_host_js'
         ],
         'features' => [
            'disable_emojis'        => true,
            'disable_comments'      => true,
            'disable_xmlrpc'        => true,
            'disable_global_styles' => true,
            'disable_jquery'        => true,
         ]
      ];

      /**
       * Filter the debloater configuration.
       *
       * @param array $config The cleanup configuration arrays.
       */
      return apply_filters('detailking/theme/debloater/config', $config);
   }

   // -------------------------------------------------------------------------
   // Registration Methods
   // -------------------------------------------------------------------------

   private function registerAdminCleanup(): void
   {
      add_action('wp_dashboard_setup', [$this, 'removeDashboardWidgets']);
      add_action('admin_bar_menu', [$this, 'cleanAdminBar'], 999);
      add_action('admin_head', [$this, 'removeContextualHelp']);
      add_action('admin_enqueue_scripts', [$this, 'cleanAdminAssets'], 100);
   }

   private function registerGlobalCleanup(): void
   {
      foreach ($this->config['head_cleanup'] as $hook) {
         remove_action('wp_head', $hook);
      }

      if ($this->config['features']['disable_emojis']) {
         $this->disableEmojis();
      }

      if ($this->config['features']['disable_global_styles']) {
         $this->disableGlobalStyles();
      }

      add_action('wp_enqueue_scripts', [$this, 'cleanFrontendAssets'], 100);

      if (class_exists('WooCommerce')) {
         add_action('wp_enqueue_scripts', [$this, 'dequeueWooCommerceAssetsOnNonShopPages'], 100);
      }

      if ($this->config['features']['disable_xmlrpc']) {
         add_filter('xmlrpc_enabled', '__return_false');
      }

      if (!empty($this->config['features']['disable_jquery'])) {
         add_action('wp_enqueue_scripts', [$this, 'removeJquery'], 100);
      }
   }

   private function registerCommentDisabling(): void
   {
      if (!$this->config['features']['disable_comments']) {
         return;
      }

      add_action('init', [$this, 'disableCommentsSupport']);
      add_filter('comments_open', '__return_false', 20, 2);
      add_filter('pings_open', '__return_false', 20, 2);
      add_filter('comments_array', '__return_empty_array', 10, 2);
      add_action('admin_menu', [$this, 'removeCommentsMenu'], 999);
      add_action('wp_before_admin_bar_render', [$this, 'removeAdminBarComments']);

      add_action('template_redirect', [$this, 'blockCommentSubmission'], 1);
      add_filter('comments_template', [$this, 'disableCommentTemplate'], 20);
   }

   private function registerClassicEditor(): void
   {
      add_filter('use_block_editor_for_post', '__return_false', 10);
      add_filter('use_block_editor_for_post_type', '__return_false', 10);
      add_filter('use_widgets_block_editor', '__return_false', 10);
      add_filter('use_block_editor_for_nav_menu', '__return_false', 10);
   }

   // -------------------------------------------------------------------------
   // Cleanup Logic
   // -------------------------------------------------------------------------

   public function disableEmojis(): void
   {
      remove_action('admin_print_styles', 'print_emoji_styles');
      remove_action('wp_head', 'print_emoji_detection_script', 7);
      remove_action('admin_print_scripts', 'print_emoji_detection_script');
      remove_action('wp_print_styles', 'print_emoji_styles');
      remove_filter('the_content_feed', 'wp_staticize_emoji');
      remove_filter('comment_text_rss', 'wp_staticize_emoji');
      remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

      add_filter('emoji_svg_url', '__return_false');
   }

   public function disableGlobalStyles(): void
   {
      remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
      remove_action('wp_footer', 'wp_enqueue_global_styles');
      remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

      add_filter('wp_enqueue_scripts', function (): void {
         wp_dequeue_style('global-styles');
         wp_dequeue_style('wp-global-styles');
      }, 100);
   }

   public function removeDashboardWidgets(): void
   {
      foreach ($this->config['dashboard_widgets'] as $widget) {
         remove_meta_box($widget, 'dashboard', 'normal');
         remove_meta_box($widget, 'dashboard', 'side');
         remove_meta_box($widget, 'dashboard', 'advanced');
      }
   }

   public function cleanAdminBar(\WP_Admin_Bar $wp_admin_bar): void
   {
      foreach ($this->config['admin_bar_nodes'] as $node) {
         $wp_admin_bar->remove_node($node);
      }
   }

   public function cleanAdminAssets(): void
   {
      wp_dequeue_style('wp-block-library');
      wp_dequeue_style('wp-block-editor');

      if (class_exists('WooCommerce')) {
         wp_dequeue_style('wc-admin-style');
         wp_dequeue_style('wc-admin-dashboard');
      }

      add_filter('wp_default_scripts', function ($scripts): void {
         if (isset($scripts->registered['jquery'])) {
            $scripts->registered['jquery']->deps = array_diff(
               $scripts->registered['jquery']->deps,
               ['jquery-migrate']
            );
         }
      });
   }

   public function cleanFrontendAssets(): void
   {
      foreach ($this->config['frontend_styles'] as $style) {
         wp_dequeue_style($style);
         wp_deregister_style($style);
      }

      foreach ($this->config['frontend_scripts'] as $script) {
         wp_dequeue_script($script);
         wp_deregister_script($script);
      }
   }

   /**
    * WooCommerce enqueues its own layout/blocks CSS and order-attribution JS
    * on every front-end request, regardless of whether the page has any
    * WooCommerce content. The header's cart link is themed entirely by
    * dk-nav__cart in the theme's own header.css and needs none of it, so this
    * ~136KB of CSS + 2 scripts only has anywhere to matter on shop pages.
    *
    * Runs at priority 100 on wp_enqueue_scripts, after WooCommerce registers
    * everything at its default priority 10.
    */
   public function dequeueWooCommerceAssetsOnNonShopPages(): void
   {
      $isShopPage = is_woocommerce() || is_cart() || is_checkout() || is_account_page();

      if ($isShopPage) {
         return;
      }

      foreach ($this->config['woocommerce_styles'] as $style) {
         wp_dequeue_style($style);
      }

      foreach ($this->config['woocommerce_scripts'] as $script) {
         wp_dequeue_script($script);
      }
   }

   /**
    * Remove jQuery from the front end. The theme ships Bootstrap's bundle
    * (no jQuery), so core jQuery is dropped for visitors. The admin and the
    * customizer (where core relies on jQuery) are left untouched; this runs on
    * wp_enqueue_scripts, which does not fire on the login screen.
    */
   public function removeJquery(): void
   {
      if (is_admin() || is_customize_preview()) {
         return;
      }

      foreach (['jquery', 'jquery-core', 'jquery-migrate'] as $handle) {
         wp_dequeue_script($handle);
         wp_deregister_script($handle);
      }
   }

   public function removeContextualHelp(): void
   {
      $screen = get_current_screen();
      if ($screen instanceof \WP_Screen) {
         $screen->remove_help_tabs();
      }
   }

   // -------------------------------------------------------------------------
   // Comment Disabling Logic
   // -------------------------------------------------------------------------

   public function disableCommentsSupport(): void
   {
      $post_types = get_post_types(['public' => true]);
      foreach ($post_types as $post_type) {
         if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
         }
      }
   }

   public function blockCommentSubmission(): void
   {
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && !comments_open()) {
         if (isset($_POST['comment_post_ID'])) {
            wp_die(
               __('Commenting is disabled on this site.'),
               __('Comment Disabled'),
               ['response' => 403]
            );
         }
      }
   }

   public function removeCommentsMenu(): void
   {
      remove_menu_page('edit-comments.php');
   }

   public function removeAdminBarComments(): void
   {
      global $wp_admin_bar;
      if (isset($wp_admin_bar)) {
         $wp_admin_bar->remove_menu('comments');
      }
   }

   /**
    * Return an empty comments template to prevent PHP errors when a template
    * tries to load comments.php.
    */
   public function disableCommentTemplate(string $theme_template): string
   {
      return ABSPATH . WPINC . '/theme-compat/comments.php';
   }
}
