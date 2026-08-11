<?php

/**
 * Primary navigation — the comp's floating pill nav.
 *
 * Geometry, measured off the Figma nav node (175:3070) rather than eyeballed:
 *   pill      1550 x 86, y=22, radius 100px, 41px inner padding
 *   fill      rgba(11,11,12,.93) + backdrop-filter blur(10px)
 *   border    1px rgba(255,255,255,.08)
 *   shadow    0 20px 60px rgba(0,0,0,.4)
 *   menu      six items, gap exactly 42px
 *   groups    logo / menu / actions sit at equal 180.9px gaps, which plain
 *             justify-content:space-between reproduces exactly
 *
 * The nav overlays the hero on every frame, so it is positioned rather than in
 * flow, and the hero owns the top padding it needs.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();

$brand    = (string) $meta->optOr('header_brand_name');
$account  = (string) $meta->optOr('header_account_text');
$ctaText  = (string) $meta->optOr('header_cta_text');
$ctaUrl   = (string) $meta->optOr('header_cta_url');

// Falls back to the logo shipped from the comp, so the nav is never brand-less.
$logoFallback = get_template_directory_uri() . '/assets/images/brand/logo.png';
$logo = $meta->optImageTag('header_logo', 'full', [
   'class' => 'site-brand__logo',
   'alt'   => $brand,
], $logoFallback);

// "Login / My Account" points at WooCommerce's account page when the shop is
// live, and at the WP login otherwise.
$accountUrl = wp_login_url();

if (function_exists('wc_get_page_id')) {
   $accountPageId = wc_get_page_id('myaccount');
   $accountPermalink = $accountPageId > 0 ? get_permalink($accountPageId) : false;

   if ($accountPermalink) {
      $accountUrl = $accountPermalink;
   }
}
?>
<nav class="dk-nav<?= !empty($args['solid']) ? ' dk-nav--solid' : ''; ?>" aria-label="<?php esc_attr_e('Primary', 'detailking'); ?>">
   <div class="dk-nav__pill">

      <!-- Brand -->
      <a class="site-brand" href="<?= esc_url(home_url('/')); ?>" aria-label="<?= esc_attr($brand); ?>">
         <?php if (has_custom_logo()) : ?>
            <?php the_custom_logo(); ?>
         <?php else : ?>
            <?= $logo; ?>
         <?php endif; ?>
      </a>

      <!-- Mobile toggler. [data-sp-toggle] is the house convention; global.js
           toggles .is-open on the target id. -->
      <button
         class="dk-nav__toggle"
         type="button"
         aria-controls="primary-nav"
         aria-expanded="false"
         aria-label="<?php esc_attr_e('Toggle navigation', 'detailking'); ?>"
         data-sp-toggle="primary-nav">
         <span></span><span></span><span></span>
      </button>

      <!-- Primary menu -->
      <div class="dk-nav__menu primary-nav" id="primary-nav">
         <?php
         if (has_nav_menu('primary-menu')) {
            wp_nav_menu([
               'theme_location' => 'primary-menu',
               'container'      => false,
               'menu_class'     => 'dk-menu',
               'menu_id'        => 'primary-menu',
               'depth'          => 2,
               'fallback_cb'    => false,
            ]);
         }
         ?>
      </div>

      <!-- Actions -->
      <div class="dk-nav__actions">
         <a class="dk-nav__account" href="<?= esc_url($accountUrl); ?>">
            <?= esc_html($account); ?>
         </a>

         <?php if ($ctaText !== '') : ?>
            <a class="btn-gold dk-nav__cta" href="<?= esc_url($ctaUrl ?: home_url('/contact/')); ?>">
               <?= esc_html($ctaText); ?>
            </a>
         <?php endif; ?>
      </div>

   </div>
</nav>
