<?php

/**
 * Category archive body — sidebar + product grid (node 183:7622, "All
 * Products"). See figma-data/shop-spec.md §B.
 *
 * The main WP_Query IS the product query for this initial, server-rendered
 * page load (WooCommerce filters it via pre_get_posts on
 * is_product_category()/is_shop()). From here on, initShopFilters() (shop.js)
 * re-queries via ShopFilterService's admin-ajax endpoint and swaps
 * #shop-listing-grid's contents in place — see shop-sidebar.php's doc comment
 * for why that's a separate, JS-only path rather than a second query kept in
 * sync with this one.
 *
 * #shop-listing-main's data-page/data-max-pages are the load-more cursor;
 * shop.js owns both from here (resets to 1 on any filter change, increments
 * on "View More").
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

global $wp_query;

$hasPosts = have_posts();
?>
<section class="shop-listing">
   <div class="container-dk">
      <div class="shop-listing__layout">

         <?php get_template_part('template-parts/components/shop-sidebar'); ?>

         <div class="shop-listing__main" id="shop-listing-main" data-page="1" data-max-pages="<?= (int) $wp_query->max_num_pages; ?>">
            <div class="shop-listing__bar">
               <span class="shop-listing__count" id="shop-listing-count">
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

            <div class="shop-listing__grid" id="shop-listing-grid"<?= $hasPosts ? '' : ' hidden'; ?>>
               <?php while (have_posts()) : the_post(); ?>
                  <?php get_template_part('template-parts/components/product-card', null, ['product' => $post]); ?>
               <?php endwhile; ?>
            </div>

            <p class="shop-listing__empty" id="shop-listing-empty"<?= $hasPosts ? ' hidden' : ''; ?>>
               <?php esc_html_e('No products match these filters.', 'detailking'); ?>
            </p>

            <div class="shop-listing__more" id="shop-listing-more"<?= $wp_query->max_num_pages > 1 ? '' : ' hidden'; ?>>
               <a class="btn-dark" id="shop-listing-load-more" data-dk-shop-load-more href="<?= esc_url((string) get_next_posts_page_link($wp_query->max_num_pages)); ?>">
                  <?php esc_html_e('View More', 'detailking'); ?>
               </a>
            </div>
         </div>

      </div>
   </div>

   <!-- Off-canvas trigger, below 1280px only (shop.css). Opens
        #shop-sidebar-overlay; initShopSidebarDrawer() in global.js owns
        open/close, backdrop click and Escape. -->
   <button type="button"
      class="shop-sidebar-fab"
      id="shop-sidebar-toggle"
      aria-controls="shop-sidebar-overlay"
      aria-expanded="false"
      aria-label="<?php esc_attr_e('Open filters', 'detailking'); ?>">
      <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'filter']); ?>
   </button>
</section>
