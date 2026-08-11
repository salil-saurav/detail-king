<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Registers the top-level "Site Content" admin menu that groups the theme's
 * content options pages (Global Options, and any you add) as sub-pages.
 *
 * The parent page itself has no fields; clicking it redirects to the first
 * sub-page (redirect => true). Each content options page attaches via
 * 'parent_slug' => SiteContentMenu::PARENT_SLUG (see GlobalFields).
 *
 * Registered on acf/init at priority 5 — before the sub-pages (priority 10) —
 * so the parent exists when its children declare their parent_slug.
 */
class SiteContentMenu extends Singleton implements ServiceInterface
{
   public const PARENT_SLUG = 'site-content';

   public function register(): void
   {
      add_action('acf/init', [$this, 'registerMenu'], 5);
   }

   public function registerMenu(): void
   {
      if (!function_exists('acf_add_options_page')) {
         return;
      }

      acf_add_options_page([
         'page_title' => __('Site Content'),
         'menu_title' => __('Site Content'),
         'menu_slug'  => self::PARENT_SLUG,
         'capability' => 'edit_theme_options',
         'position'   => 2,
         'icon_url'   => 'dashicons-layout',
         'redirect'   => true,
      ]);
   }
}
