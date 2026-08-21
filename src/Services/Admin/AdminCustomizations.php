<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Admin;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Admin-area customizations: enqueues admin assets, replaces the admin footer
 * text, and removes the dashboard welcome panel.
 */
class AdminCustomizations extends Singleton implements ServiceInterface
{
   public function register(): void
   {
      add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
      add_action('login_enqueue_scripts', [$this, 'enqueueAdminAssets']);
      add_filter('admin_footer_text', [$this, 'customizeFooterText']);
      add_filter('login_headerurl', [$this, 'loginLogoUrl']);
      add_filter('login_headertext', [$this, 'loginLogoText']);
      remove_action('welcome_panel', 'wp_welcome_panel');
   }

   /**
    * Enqueue admin stylesheet/script if present, auto-versioned by filemtime.
    */
   public function enqueueAdminAssets(): void
   {
      // Parent (template) paths so admin assets resolve under a child theme.
      $dir = get_template_directory();
      $uri = get_template_directory_uri();

      $css = '/assets/css/admin.css';
      if (file_exists($dir . $css)) {
         wp_enqueue_style('detailking-admin', $uri . $css, [], (string) filemtime($dir . $css));
      }

      $js = '/assets/js/admin.js';
      if (file_exists($dir . $js)) {
         wp_enqueue_script('detailking-admin', $uri . $js, [], (string) filemtime($dir . $js), true);
      }
   }

   /**
    * Replace the "Thank you for creating with WordPress" footer text.
    */
   public function customizeFooterText(): string
   {
      $default = sprintf(
         /* translators: %s: site name (linked to home). */
         __('Built with %s'),
         sprintf('<a href="%s">%s</a>', esc_url(home_url('/')), esc_html(get_bloginfo('name')))
      );

      return apply_filters('detailking/theme/admin/footer_text', $default);
   }

   /**
    * Point the login screen's logo link at the site homepage instead of
    * wordpress.org.
    */
   public function loginLogoUrl(): string
   {
      return home_url('/');
   }

   /**
    * Swap the logo's title/alt text from "Powered by WordPress" to the
    * site name.
    */
   public function loginLogoText(): string
   {
      return get_bloginfo('name');
   }
}
