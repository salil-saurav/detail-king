<?php

/**
 * Featured products rail. Design band y 7743.1–8724.8, node 59:2529.
 *
 * Products are the native WooCommerce post type — never modelled as ACF repeaters.
 * The whole section is skipped when WooCommerce is inactive or has no products, so
 * the homepage does not carry an empty rail on a site that is not selling yet.
 *
 * Comp note: "Horizontal snap-scroll product rail" — CSS scroll-snap, with the
 * arrows scrolling by one card.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

if (!class_exists('WooCommerce')) {
   return;
}

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$products = get_posts([
   'post_type'        => 'product',
   'posts_per_page'   => 8,
   'post_status'      => 'publish',
   'orderby'          => 'menu_order',
   'order'            => 'ASC',
   'suppress_filters' => false,
   'tax_query'       => [[
      'taxonomy' => 'product_visibility',
      'field'    => 'name',
      'terms'    => 'featured',
   ]],
]);

if (!$products) {
   return;
}

$watermark = (string) $meta->fieldOr('shop_watermark', $D);
$ctaText   = (string) $meta->fieldOr('shop_cta_text', $D);
$shopUrl   = function_exists('wc_get_page_id') && wc_get_page_id('shop') > 0
   ? (string) get_permalink(wc_get_page_id('shop'))
   : home_url('/shop/');
?>
<section class="home-shop" data-animate="fade">
   <?php if ($watermark !== '') : ?>
      <span class="dk-watermark dk-watermark--light home-shop__watermark" aria-hidden="true"><?= esc_html($watermark); ?></span>
   <?php endif; ?>

   <div class="container-dk">

      <div class="home-shop__head">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => $meta->fieldOr('shop_eyebrow', $D),
            'title'   => $meta->fieldOr('shop_heading', $D),
            'gold'    => $meta->fieldOr('shop_heading_gold', $D),
            'size'    => 'display-md',
            'eyebrow_animate' => 'fade-up',
            'animate' => 'fade-up',
         ]);
         ?>

         <?php if ($ctaText !== '') : ?>
            <a class="btn-dark btn-arrow home-shop__cta" data-animate="fade-up" href="<?= esc_url((string) $meta->fieldOr('shop_cta_url', $D) ?: $shopUrl); ?>">
               <?= esc_html($ctaText); ?>
            </a>
         <?php endif; ?>
      </div>

      <div class="dk-rail" data-dk-rail>
         <ul class="dk-rail__track">
            <?php foreach ($products as $product) : ?>
               <li class="dk-rail__item">
                  <?php get_template_part('template-parts/components/product-card', null, ['product' => $product]); ?>
               </li>
            <?php endforeach; ?>
         </ul>
      </div>

      <?php /* Rendered whenever there is more than one item: four 358px cards plus
               their gaps already overflow the 1470px column, which is why the comp
               shows arrows on a four-card rail. home.js disables them at each end. */ ?>
      <?php if (count($products) > 1) : ?>
         <div class="dk-rail__nav">
            <button class="dk-rail__btn" type="button" data-dk-rail-prev aria-label="<?php esc_attr_e('Previous products', 'detailking'); ?>">
               <span aria-hidden="true">&larr;</span>
            </button>
            <button class="dk-rail__btn" type="button" data-dk-rail-next aria-label="<?php esc_attr_e('Next products', 'detailking'); ?>">
               <span aria-hidden="true">&rarr;</span>
            </button>
         </div>
      <?php endif; ?>

   </div>
</section>
