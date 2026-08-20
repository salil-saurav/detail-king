<?php

/**
 * Product Detail — "Related Products" dark rail (node 185:8213, see
 * figma-data/shop-spec.md §C). Real Woo relation (shared category/tag) via
 * wc_get_related_products(), not a hand-picked list.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

global $product;

if (!$product instanceof WC_Product) {
   return;
}

$relatedIds = wc_get_related_products($product->get_id(), 4);

if (!$relatedIds) {
   return;
}
?>
<section class="product-related section--dark" data-animate="fade">
   <div class="container-dk">

      <div class="product-related__head">
         <span class="eyebrow eyebrow--rule-start" data-animate="fade-up"><?php esc_html_e('You May Also Like', 'detailking'); ?></span>
         <h2 class="product-related__title" data-animate="fade-up">
            <?php esc_html_e('Related', 'detailking'); ?> <span class="text-gold-gradient"><?php esc_html_e('Products', 'detailking'); ?></span>
         </h2>
      </div>

      <div class="product-related__grid">
         <?php foreach ($relatedIds as $relatedId) : ?>
            <?php $relatedPost = get_post($relatedId); ?>
            <?php if ($relatedPost) : ?>
               <?php get_template_part('template-parts/components/product-card', null, ['product' => $relatedPost, 'dark' => true]); ?>
            <?php endif; ?>
         <?php endforeach; ?>
      </div>

   </div>
</section>
