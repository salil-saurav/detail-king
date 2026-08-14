<?php

/**
 * Shop landing category grid. Real product_cat terms — no ACF repeater, so a
 * category added from wp-admin appears here with zero template work, per
 * woocommerce.md's content-model rule (products/categories are a CPT/taxonomy
 * the theme doesn't own).
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$terms = get_terms([
   'taxonomy'   => 'product_cat',
   'hide_empty' => false,
   'orderby'    => 'name',
   'exclude'    => [(int) get_option('default_product_cat', 0)],
]);

if (is_wp_error($terms) || !$terms) {
   return;
}
?>
<section class="shop-cats" data-animate="fade">
   <div class="container-dk">
      <div class="shop-cats__grid">
         <?php foreach ($terms as $term) : ?>
            <?php get_template_part('template-parts/components/category-card', null, ['term' => $term]); ?>
         <?php endforeach; ?>
      </div>
   </div>
</section>
