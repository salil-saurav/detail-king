<?php

/**
 * Site header wrapper.
 *
 * The nav floats over the hero on every frame in the comp, so it is absolutely
 * positioned and the hero supplies its own top padding. Templates that have no
 * dark hero behind the nav (a plain page, a 404, search results) would put white
 * text on a cream background, so those get the solid variant instead.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

/**
 * Whether this view renders its own dark hero directly under the nav.
 *
 * Filterable so a page template can opt in or out without editing this file —
 * a new template that draws its own hero adds itself via the filter rather than
 * being special-cased here.
 */
$hasDarkHero = (bool) apply_filters(
   'detailking/theme/has_dark_hero',
   is_front_page() || is_singular(['dk_service', 'dk_gallery', 'post']) || is_page() || is_archive() || is_home()
);
?>
<header id="site-header" class="site-header<?= $hasDarkHero ? '' : ' site-header--solid'; ?>">
   <?php get_template_part('template-parts/header/nav-header', null, ['solid' => !$hasDarkHero]); ?>
</header>
