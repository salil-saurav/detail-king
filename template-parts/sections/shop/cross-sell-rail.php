<?php

/**
 * Cart page — "Recommended for you" 10%-off cross-sell rail (BUILD-PLAN §7
 * Phase 1 step 8). Mounted by CrossSellService::renderCartRail() on the
 * inert `detailking_cart_cross_sell` action already present at the end of
 * woocommerce/cart/cart.php.
 *
 * $args['product_ids'] is pre-computed by the caller (union of
 * wc_get_related_products() across every product already in the cart,
 * excluding cart items themselves) — this template only renders.
 *
 * data-animate sits on each card, never on this wrapping section: a section
 * this small stays well clear of the "repeating grid taller than the
 * viewport never reveals" bug already paid for once on Our Services (see
 * CLAUDE.md gotchas), but the house rule established there is to always put
 * the reveal on the repeating item, not the wrapper — followed here too.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$productIds = (array) ($args['product_ids'] ?? []);

if (!$productIds) {
   return;
}
?>
<section class="cross-sell-rail" aria-label="<?php esc_attr_e('Recommended for you', 'detailking'); ?>">
   <div class="cross-sell-rail__head">
      <span class="eyebrow eyebrow--rule-start" data-animate="fade-up"><?php esc_html_e('10% Off', 'detailking'); ?></span>
      <h2 class="cross-sell-rail__title" data-animate="fade-up">
         <?php esc_html_e('Recommended', 'detailking'); ?> <span class="text-gold-gradient"><?php esc_html_e('For You', 'detailking'); ?></span>
      </h2>
   </div>

   <div class="cross-sell-rail__grid">
      <?php foreach ($productIds as $productId) : ?>
         <?php $post = get_post($productId); ?>
         <?php if ($post) : ?>
            <?php
            get_template_part('template-parts/components/product-card', null, [
               'product'      => $post,
               'dark'         => true,
               'cross_sell'   => true,
               'discount_pct' => \DetailKing\Theme\Services\CrossSell\CrossSellService::DISCOUNT_PCT,
            ]);
            ?>
         <?php endif; ?>
      <?php endforeach; ?>
   </div>
</section>
