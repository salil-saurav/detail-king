<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Registers core theme supports. Extend as needed per project.
 */
class ThemeService extends Singleton implements ServiceInterface
{
   public function register(): void
   {
      add_action('after_setup_theme', [$this, 'addThemeSupport']);
      add_action('after_setup_theme', [$this, 'registerMenus']);
      add_action('after_setup_theme', [$this, 'setContentWidth'], 0);
   }

   /**
    * Nav menu locations.
    *
    * The boilerplate called has_nav_menu('primary-menu') in nav-header.php and
    * has_nav_menu('footer-menu') in the footer, but never registered either
    * location — so both were permanently false and the header silently fell back
    * to a one-item "Home" menu.
    *
    * The comp's own frames disagree about the primary menu: the Ceramic Pro nav
    * shows Home/About/Services/Memberships/Shop/Contact, the Blog nav adds
    * Gallery and Blog, and the Gallery nav has Gallery but not Blog. Registering
    * the location makes that an editor decision rather than a hard-coded guess;
    * the seeded menu uses the superset.
    */
   public function registerMenus(): void
   {
      register_nav_menus([
         'primary-menu'    => __('Primary Menu (header)', 'detailking'),
         'footer-quick'    => __('Footer — Quick Links', 'detailking'),
         'footer-services' => __('Footer — Services', 'detailking'),
         'footer-legal'    => __('Footer — Legal', 'detailking'),
      ]);
   }

   public function addThemeSupport(): void
   {
      add_theme_support('automatic-feed-links');
      add_theme_support('title-tag');
      add_theme_support('post-thumbnails');
      add_theme_support('menus');

      // WooCommerce. Guarded so the theme is not asserting shop support on a
      // site where the plugin is inactive.
      if (class_exists('WooCommerce')) {
         add_theme_support('woocommerce');
         add_theme_support('wc-product-gallery-zoom');
         add_theme_support('wc-product-gallery-lightbox');
         add_theme_support('wc-product-gallery-slider');
      }

      add_theme_support('custom-logo', [
         'height'      => 80,
         'width'       => 80,
         'flex-width'  => true,
         'flex-height' => true,
      ]);
      add_theme_support('html5', [
         'search-form',
         'comment-form',
         'comment-list',
         'gallery',
         'caption',
         'style',
         'script',
      ]);
   }

   /**
    * Set the global content width used by oEmbed and wide images.
    */
   public function setContentWidth(): void
   {
      $GLOBALS['content_width'] = apply_filters('detailking/theme/content_width', 1320);
   }
}
