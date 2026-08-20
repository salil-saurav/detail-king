<?php

/**
 * Product card — shared by the homepage shop rail, the Shop category grid,
 * Product Detail's "Related Products" rail, and the recommendation
 * modal / cart cross-sell rail (BUILD-PLAN §7 Phase 1 step 8).
 *
 *   get_template_part('template-parts/components/product-card', null, [
 *      'product'      => $post,
 *      'dark'         => false,   // Related Products rail: bg #131315, gold price
 *      'cross_sell'   => false,   // true: discounted price + AJAX add button
 *      'discount_pct' => 0,       // e.g. 10 — only used when cross_sell is true
 *   ]);
 *
 * Uses WooCommerce's own price HTML and add-to-cart URL rather than reading meta
 * directly, so sale prices, variable ranges, tax display and out-of-stock states
 * come out right without this template knowing about any of them.
 *
 * cross_sell extends this one component's args rather than forking a second
 * card — the house rule already established here: "if a product display
 * looks even slightly different, extend this component's args, not
 * hand-roll new card markup." The discounted price shown here is display
 * only; the real discount is applied server-side by
 * CrossSellService::applyCrossSellDiscount() once the item is actually in
 * the cart (from get_regular_price(), same math, so the two never disagree).
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$postObj     = $args['product'] ?? null;
$dark        = (bool) ($args['dark'] ?? false);
$crossSell   = (bool) ($args['cross_sell'] ?? false);
$discountPct = (int) ($args['discount_pct'] ?? 0);

if (!$postObj instanceof WP_Post || !function_exists('wc_get_product')) {
   return;
}

$product = wc_get_product($postObj);

if (!$product) {
   return;
}

$thumb = get_the_post_thumbnail_url($postObj, 'medium_large');
$link  = (string) get_permalink($postObj);

/* Badge: "Sale" is derived; anything else is an editorial label on the product. */
$badge = '';
if ($product->is_on_sale()) {
   $badge = __('Sale', 'detailking');
}
$custom = get_post_meta($postObj->ID, '_dk_badge', true);
if (is_string($custom) && $custom !== '') {
   $badge = $custom;
}

$terms    = get_the_terms($postObj, 'product_cat');
$eyebrow  = ($terms && !is_wp_error($terms)) ? $terms[0]->name : '';

$inStock    = $product->is_in_stock();
$canAddHere = $inStock && $product->is_purchasable() && $product->is_type('simple');
?>
<article class="prod-card<?= $dark ? ' prod-card--dark' : ''; ?>" data-animate="fade">

   <a class="prod-card__media" href="<?= esc_url($link); ?>">
      <?php if ($thumb) : ?>
         <img src="<?= esc_url($thumb); ?>" alt="<?= esc_attr(get_the_title($postObj)); ?>" loading="lazy" decoding="async">
      <?php endif; ?>

      <?php if ($badge !== '') : ?>
         <span class="prod-card__badge"><?= esc_html($badge); ?></span>
      <?php endif; ?>
   </a>

   <?php /* Save/wishlist — visual only (localStorage), no account-linked wishlist
            feature exists in the brief; flagged as a design deviation in
            figma-data/shop-spec.md rather than built as a backend feature. */ ?>
   <button type="button" class="prod-card__save" data-dk-wishlist data-product-id="<?= esc_attr((string) $product->get_id()); ?>" aria-pressed="false" aria-label="<?php esc_attr_e('Save for later', 'detailking'); ?>">
      <span aria-hidden="true">&#9825;</span>
   </button>

   <div class="prod-card__body">
      <div>
         <?php if ($eyebrow !== '') : ?>
            <span class="prod-card__eyebrow"><?= esc_html($eyebrow); ?></span>
         <?php endif; ?>

         <h3 class="prod-card__title">
            <a href="<?= esc_url($link); ?>"><?= esc_html(get_the_title($postObj)); ?></a>
         </h3>
      </div>

      <div class="prod-card__foot">
         <?php if ($crossSell && $discountPct > 0 && $canAddHere) : ?>
            <?php
            $regularPrice    = (float) $product->get_regular_price();
            $discountedPrice = $regularPrice > 0 ? round($regularPrice * (1 - $discountPct / 100), 2) : 0;
            ?>
            <span class="prod-card__price prod-card__price--cross-sell">
               <del><?= wp_kses_post(wc_price($regularPrice)); ?></del>
               <ins><?= wp_kses_post(wc_price($discountedPrice)); ?></ins>
            </span>
         <?php else : ?>
            <span class="prod-card__price"><?= wp_kses_post($product->get_price_html()); ?></span>
         <?php endif; ?>

         <?php if ($crossSell && $canAddHere) : ?>
            <button type="button"
               class="prod-card__add"
               data-dk-cross-sell-add
               data-product-id="<?= esc_attr((string) $product->get_id()); ?>"
               aria-label="<?php esc_attr_e('Add to cart at the discounted price', 'detailking'); ?>">
               <span aria-hidden="true">+</span>
            </button>
         <?php elseif ($canAddHere) : ?>
            <a class="prod-card__add"
               href="<?= esc_url($product->add_to_cart_url()); ?>"
               data-product_id="<?= esc_attr((string) $product->get_id()); ?>"
               rel="nofollow"
               aria-label="<?php esc_attr_e('Add to cart', 'detailking'); ?>">
               <span aria-hidden="true">+</span>
            </a>
         <?php else : ?>
            <?php /* Variable, external or out of stock: send them to the product page
                     rather than showing an add-to-cart that cannot work. */ ?>
            <a class="prod-card__add"
               <?= $inStock ? '' : 'aria-disabled="true"'; ?>
               href="<?= esc_url($link); ?>"
               aria-label="<?= $inStock ? esc_attr__('View product', 'detailking') : esc_attr__('Out of stock', 'detailking'); ?>">
               <span aria-hidden="true">&rarr;</span>
            </a>
         <?php endif; ?>
      </div>
   </div>

</article>
