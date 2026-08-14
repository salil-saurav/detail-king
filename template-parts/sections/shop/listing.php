<?php

/**
 * Category archive body — sidebar + product grid (node 183:7622, "All
 * Products"). See figma-data/shop-spec.md §B.
 *
 * The main WP_Query IS the product query here (WooCommerce filters it via
 * pre_get_posts on is_product_category()/is_shop()) — no second query to
 * build or keep in sync.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

global $wp_query;
?>
<section class="shop-listing">
   <div class="container-dk">
      <div class="shop-listing__layout">

         <?php get_template_part('template-parts/components/shop-sidebar'); ?>

         <div class="shop-listing__main">
            <div class="shop-listing__bar">
               <span class="shop-listing__count">
                  <?php
                  printf(
                     /* translators: %d: number of products. */
                     esc_html(_n('%d product', '%d products', $wp_query->found_posts, 'detailking')),
                     (int) $wp_query->found_posts
                  );
                  ?>
               </span>

               <?php if (function_exists('woocommerce_catalog_ordering')) : ?>
                  <div class="shop-listing__sort">
                     <span class="shop-listing__sort-label"><?php esc_html_e('Sort by', 'detailking'); ?></span>
                     <?php woocommerce_catalog_ordering(); ?>
                  </div>
               <?php endif; ?>
            </div>

            <?php if (have_posts()) : ?>
               <div class="shop-listing__grid">
                  <?php while (have_posts()) : the_post(); ?>
                     <?php get_template_part('template-parts/components/product-card', null, ['product' => $post]); ?>
                  <?php endwhile; ?>
               </div>

               <?php if ($wp_query->max_num_pages > 1) : ?>
                  <div class="shop-listing__more">
                     <a class="btn-dark" href="<?= esc_url((string) get_next_posts_page_link($wp_query->max_num_pages)); ?>">
                        <?php esc_html_e('View More', 'detailking'); ?>
                     </a>
                  </div>
               <?php endif; ?>
            <?php else : ?>
               <p class="shop-listing__empty"><?php esc_html_e('No products in this category yet — check back soon.', 'detailking'); ?></p>
            <?php endif; ?>
         </div>

      </div>
   </div>
</section>
