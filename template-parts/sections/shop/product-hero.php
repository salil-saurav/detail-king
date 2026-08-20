<?php

/**
 * Product Detail — breadcrumb, gallery, info panel (node 185:8135, see
 * figma-data/shop-spec.md §C). `global $product` is set by single-product.php's
 * loop (WooCommerce hooks wc_setup_product_data onto `the_post`).
 *
 * Gallery, price HTML and the add-to-cart form all go through Woo's own
 * functions rather than being re-derived here — see the inline notes below
 * for why each one is real logic, not markup this template should own.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

global $product;

if (!$product instanceof WC_Product) {
   return;
}

$terms   = get_the_terms(get_the_ID(), 'product_cat');
$eyebrow = ($terms && !is_wp_error($terms)) ? $terms[0]->name : '';

$tagsRaw = (string) MetaHelper::getInstance()->field('compat_tags', get_the_ID(), '');
$tags    = array_filter(array_map('trim', explode(',', $tagsRaw)));

$trustItems = MetaHelper::getInstance()->rowsOr('shop_trust_items');
?>
<section class="product-hero">
   <div class="container-dk">

      <?php get_template_part('template-parts/global/breadcrumb'); ?>

      <div class="product-hero__layout">

         <div class="product-hero__gallery" data-animate="fade-left">
            <?php
            /* Real logic, not markup to re-derive: gallery image ids, the
               placeholder fallback, and (via the theme support flags in
               ThemeService) zoom + lightbox + slider wiring all come from
               this one Woo function. */
            woocommerce_show_product_images();
            ?>
         </div>

         <div class="product-hero__info" data-animate="fade-right">
            <?php if ($eyebrow !== '') : ?>
               <span class="product-hero__eyebrow"><?= esc_html($eyebrow); ?></span>
            <?php endif; ?>

            <h1 class="product-hero__title"><?= esc_html(get_the_title()); ?></h1>

            <div class="product-hero__price">
               <?php
               /* Sale strike-through + "Save N%" both come from Woo's own
                  price HTML — hand-computing the percentage would drift the
                  moment a sale price changes. */
               echo wp_kses_post($product->get_price_html());
               ?>
            </div>

            <?php if ($product->get_short_description() !== '' || get_the_content() !== '') : ?>
               <div class="product-hero__desc">
                  <?= wp_kses_post(wpautop($product->get_short_description() ?: get_the_content())); ?>
               </div>
            <?php endif; ?>

            <?php if ($tags) : ?>
               <ul class="product-hero__tags">
                  <?php foreach ($tags as $tag) : ?>
                     <li class="product-hero__tag">
                        <span aria-hidden="true">&#10003;</span> <?= esc_html($tag); ?>
                     </li>
                  <?php endforeach; ?>
               </ul>
            <?php endif; ?>

            <div class="product-hero__cart">
               <?php
               /* The quantity stepper, the nonce, and add_to_cart_form action
                  all come from Woo's own template part — this is the one
                  place in the page that must survive an upgrade untouched. */
               woocommerce_template_single_add_to_cart();
               ?>
            </div>

            <?php if ($trustItems) : ?>
               <ul class="product-hero__trust">
                  <?php foreach ($trustItems as $row) : ?>
                     <?php $glyph = (string) ($row['trust_icon_glyph'] ?? 'shield'); ?>
                     <li>
                        <span class="product-hero__trust-icon" aria-hidden="true">
                           <?php get_template_part('template-parts/components/glyph', null, ['glyph' => $glyph]); ?>
                        </span>
                        <?= esc_html((string) ($row['trust_text'] ?? '')); ?>
                     </li>
                  <?php endforeach; ?>
               </ul>
            <?php endif; ?>
         </div>

      </div>
   </div>
</section>
