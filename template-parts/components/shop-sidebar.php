<?php

/**
 * Category archive sidebar — category checklist, price filter, "Need Advice?"
 * CTA (node 183:7649, see figma-data/shop-spec.md §B).
 *
 * Below 1280px this becomes an off-canvas drawer (initShopSidebarDrawer() in
 * global.js), opened by the floating filter circle in listing.php. The
 * `.shop-sidebar-overlay` wrapper is `display: contents` at desktop — so
 * `.shop-sidebar` stays the grid's direct child there — and only becomes the
 * fixed backdrop below the breakpoint (shop.css).
 *
 * Both filters are real multi-select checkboxes / a plain range input, but
 * neither navigates any more — initShopFilters() in shop.js intercepts both
 * and re-queries via ShopFilterService's admin-ajax endpoint, swapping
 * #shop-listing-grid in place. This is JS-only (no non-JS submit control on
 * either control, same as the price slider always was before this AJAX pass
 * existed), so there's no real-navigation fallback path to keep working
 * alongside — see ShopFilterService's own class doc for why that's a
 * deliberate scope call, not an oversight.
 *
 * The category term this page loaded on (from the URL) is still the initial
 * checked state; from there it's a real multi-select — checking a second
 * category broadens the grid to the union of both, unchecking everything
 * shows the full catalogue (empty tax_query = no category restriction).
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$currentTerm = is_product_category() ? get_queried_object() : null;
$currentMax  = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 300;

$terms = get_terms([
   'taxonomy'   => 'product_cat',
   'hide_empty' => false,
   'orderby'    => 'name',
   'exclude'    => [(int) get_option('default_product_cat', 0)],
]);

if (is_wp_error($terms)) {
   $terms = [];
}
?>
<div class="shop-sidebar-overlay" id="shop-sidebar-overlay">
<aside class="shop-sidebar" id="shop-sidebar">

   <div class="shop-sidebar__head-mobile">
      <span class="shop-sidebar__head-mobile-title"><?php esc_html_e('Filters', 'detailking'); ?></span>
      <button type="button" class="shop-sidebar__close" data-shop-sidebar-close aria-label="<?php esc_attr_e('Close filters', 'detailking'); ?>">
         <span aria-hidden="true">&times;</span>
      </button>
   </div>

   <div class="shop-sidebar__card">
      <div class="shop-sidebar__head">
         <h4 class="shop-sidebar__title"><?php esc_html_e('Category', 'detailking'); ?></h4>
         <a class="shop-sidebar__clear" href="<?= esc_url((string) get_permalink(wc_get_page_id('shop'))); ?>" data-dk-cat-clear>
            <?php esc_html_e('Clear', 'detailking'); ?>
         </a>
      </div>

      <ul class="shop-sidebar__list" id="shop-sidebar-cats">
         <?php foreach ($terms as $term) : ?>
            <?php $checked = $currentTerm && $currentTerm->term_id === $term->term_id; ?>
            <li>
               <label class="shop-sidebar__check">
                  <input type="checkbox" class="shop-sidebar__check-input" value="<?= esc_attr($term->slug); ?>" data-dk-cat-filter<?= $checked ? ' checked' : ''; ?>>
                  <span class="shop-sidebar__box" aria-hidden="true"></span>
                  <?= esc_html($term->name); ?>
               </label>
            </li>
         <?php endforeach; ?>
      </ul>
   </div>

   <div class="shop-sidebar__card">
      <h4 class="shop-sidebar__title"><?php esc_html_e('Max Price', 'detailking'); ?></h4>
      <form class="shop-sidebar__price" data-dk-price-filter>
         <input type="range" name="max_price" min="20" max="300" step="10" value="<?= esc_attr((string) $currentMax); ?>" aria-label="<?php esc_attr_e('Maximum price', 'detailking'); ?>">
         <div class="shop-sidebar__price-labels">
            <span>$20</span>
            <span data-dk-price-value>$<?= esc_html((string) $currentMax); ?></span>
         </div>
      </form>
   </div>

   <div class="shop-sidebar__advice">
      <h4 class="shop-sidebar__title"><?php esc_html_e('Need Advice?', 'detailking'); ?></h4>
      <p><?php esc_html_e('Not sure which product is right for your car? Our detailers are happy to help.', 'detailking'); ?></p>
      <a class="btn-gold shop-sidebar__ask" href="<?= esc_url(home_url('/contact/')); ?>">
         <?php esc_html_e('Ask Our Team', 'detailking'); ?> <span aria-hidden="true">&rarr;</span>
      </a>
   </div>

</aside>
</div>
