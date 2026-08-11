<?php

declare(strict_types=1);

namespace DetailKing\Theme\Helpers;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Small presentation helpers for templates.
 *
 * The primary-menu fallback renders a minimal menu so the header looks sane out
 * of the box. As soon as a real menu is assigned in Appearance > Menus, that
 * menu replaces the fallback automatically (see template-parts/header/nav-header.php).
 */
class TemplateHelper extends Singleton implements ServiceInterface
{
   public function register(): void {}

   /**
    * Primary navigation fallback — a single "Home" link. Replace with your own
    * items, or just assign a menu to the "Primary Menu" location.
    */
   public function primary_menu_fallback(): void
   {
      echo '<ul id="primary-menu" class="navbar-nav align-items-lg-center">';
      printf(
         '<li class="nav-item"><a class="nav-link" href="%s">%s</a></li>',
         esc_url(home_url('/')),
         esc_html__('Home')
      );
      echo '</ul>';
   }
}
