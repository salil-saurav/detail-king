<?php

namespace DetailKing\Theme\Helpers;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Registers the theme's nav menu locations and adds Bootstrap-friendly classes
 * to the primary menu's list items and links.
 */
class MenuHelper extends Singleton implements ServiceInterface
{
   public function register(): void
   {
      register_nav_menus([
         'primary-menu' => __('Primary Menu'),
         'footer-menu'  => __('Footer Menu'),
      ]);

      add_filter('nav_menu_css_class', [$this, 'add_additional_class_on_li'], 10, 3);
      add_filter('nav_menu_item_attributes', [$this, 'add_footer_animation_attribute'], 10, 3);
      add_filter('nav_menu_link_attributes', [$this, 'add_specific_menu_location_atts'], 10, 3);
   }

   public function add_additional_class_on_li($classes, $item, $args)
   {
      if (isset($args->theme_location) && $args->theme_location === 'primary-menu') {
         $classes[] = 'nav-item';
      }
      return $classes;
   }

   public function add_specific_menu_location_atts($atts, $item, $args)
   {
      if (isset($args->theme_location) && $args->theme_location === 'primary-menu') {
         $existing_class = $atts['class'] ?? '';
         $atts['class'] = trim($existing_class . ' nav-link');
      }
      return $atts;
   }

   public function add_footer_animation_attribute($atts, $item, $args)
   {
      if (isset($args->theme_location) && str_starts_with($args->theme_location, 'footer-')) {
         $atts['data-animate'] = 'fade-left';
      }
      return $atts;
   }
}
