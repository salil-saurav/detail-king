<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Shop;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use WP_Query;

defined('ABSPATH') || exit;

/**
 * AJAX re-query for the Shop category archive's sidebar (shop-sidebar.php +
 * listing.php) — lets the category checkboxes (now real multi-select, not
 * one-term-per-navigation) and the max-price slider re-render the product
 * grid without a page load.
 *
 * Deliberately a plain admin-ajax endpoint (SearchService's shape), not a
 * second query merged into `pre_get_posts`: the grid this replaces is a
 * *secondary* concern of the archive's own main query, and the interaction
 * is JS-only exactly like the price slider already was before this file
 * existed (initPriceFilter() in shop.js had no non-JS submit control either)
 * — so there's no real navigation path this needs to keep working alongside.
 *
 * Builds the query with WC_Query's own public get_tax_query()/get_meta_query()
 * (catalog visibility + out-of-stock-hidden exclusions) rather than
 * hand-rolling that logic, so a secondary query here can never drift from
 * what the main shop query already hides.
 */
class ShopFilterService extends Singleton implements ServiceInterface
{
   /** Nonce action/name shared between PHP and JS. */
   private const NONCE_ACTION = 'detailking_shop_filter';

   public function register(): void
   {
      add_action('wp_ajax_' . self::NONCE_ACTION, [$this, 'handleFilter']);
      add_action('wp_ajax_nopriv_' . self::NONCE_ACTION, [$this, 'handleFilter']);
   }

   /**
    * Expose the AJAX URL + nonce to the front-end script. Called from
    * AssetsService alongside its other wp_script_is()-guarded localizes.
    */
   public function frontendData(): array
   {
      return [
         'ajaxUrl' => admin_url('admin-ajax.php'),
         'action'  => self::NONCE_ACTION,
         'nonce'   => wp_create_nonce(self::NONCE_ACTION),
         'i18n'    => [
            /* translators: %d: number of products. */
            'countSingular' => __('%d product', 'detailking'),
            'countPlural'   => __('%d products', 'detailking'),
            'empty'         => __('No products match these filters.', 'detailking'),
         ],
      ];
   }

   /**
    * Handle the filter AJAX request and return a rendered grid + count/
    * pagination state as JSON.
    */
   public function handleFilter(): void
   {
      check_ajax_referer(self::NONCE_ACTION, 'nonce');

      if (!function_exists('WC')) {
         wp_send_json_error(['message' => 'WooCommerce is not active.'], 500);
      }

      $cats = isset($_POST['cats'])
         ? array_filter(array_map('sanitize_title', (array) wp_unslash($_POST['cats'])))
         : [];

      $maxPrice = isset($_POST['max_price']) ? (int) $_POST['max_price'] : 0;
      $paged    = isset($_POST['paged']) ? max(1, (int) $_POST['paged']) : 1;
      $orderby  = isset($_POST['orderby']) ? sanitize_text_field(wp_unslash((string) $_POST['orderby'])) : '';

      $taxQuery = $cats ? [[
         'taxonomy' => 'product_cat',
         'field'    => 'slug',
         'terms'    => $cats,
         'operator' => 'IN',
      ]] : [];
      $taxQuery = WC()->query->get_tax_query($taxQuery, false);

      $metaQuery = WC()->query->get_meta_query([], false);
      if ($maxPrice > 0) {
         $metaQuery[] = [
            'key'     => '_price',
            'value'   => $maxPrice,
            'compare' => '<=',
            'type'    => 'NUMERIC',
         ];
      }

      $ordering = WC()->query->get_catalog_ordering_args($orderby);
      $perPage  = (int) apply_filters(
         'loop_shop_per_page',
         wc_get_default_products_per_row() * wc_get_default_product_rows_per_page()
      );

      $args = [
         'post_type'           => 'product',
         'post_status'         => 'publish',
         'posts_per_page'      => $perPage,
         'paged'               => $paged,
         'ignore_sticky_posts' => true,
         'no_found_rows'       => false,
         'tax_query'           => $taxQuery, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
         'meta_query'          => $metaQuery, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
         'orderby'             => $ordering['orderby'],
         'order'               => $ordering['order'],
      ];

      if (isset($ordering['meta_key'])) {
         $args['meta_key'] = $ordering['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
      }

      $query = new WP_Query($args);

      ob_start();
      foreach ($query->posts as $post) {
         get_template_part('template-parts/components/product-card', null, ['product' => $post]);
      }
      $html = (string) ob_get_clean();

      wp_reset_postdata();

      wp_send_json_success([
         'html'     => $html,
         'count'    => (int) $query->found_posts,
         'page'     => $paged,
         'maxPages' => (int) $query->max_num_pages,
      ]);
   }
}
