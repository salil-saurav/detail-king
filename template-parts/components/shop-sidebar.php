<?php

/**
 * Category archive sidebar — category checklist, price filter, "Need Advice?"
 * CTA (node 183:7649, see figma-data/shop-spec.md §B).
 *
 * Both filters navigate (real page loads), not AJAX: the comp gives no
 * interaction note for this panel, and WooCommerce's own query already
 * understands `max_price` (WC_Query::price_filter_post_clauses(), active by
 * default on the main query) with zero extra wiring — so a plain link/range
 * input that changes the URL is the "CSS/hooks before hand-rolled logic"
 * option, not a shortcut. The category list is single-select via navigation
 * (one term per archive page) rather than a true multi-category AJAX filter;
 * the comp's checkboxes read as multi-select but nothing in the brief or the
 * frame's interaction notes specifies that behaviour — recorded as a residual
 * in figma-data/shop-spec.md.
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
<aside class="shop-sidebar">

   <div class="shop-sidebar__card">
      <div class="shop-sidebar__head">
         <h4 class="shop-sidebar__title"><?php esc_html_e('Category', 'detailking'); ?></h4>
         <?php if ($currentTerm) : ?>
            <a class="shop-sidebar__clear" href="<?= esc_url((string) get_permalink(wc_get_page_id('shop'))); ?>">
               <?php esc_html_e('Clear', 'detailking'); ?>
            </a>
         <?php endif; ?>
      </div>

      <ul class="shop-sidebar__list">
         <?php foreach ($terms as $term) : ?>
            <?php $active = $currentTerm && $currentTerm->term_id === $term->term_id; ?>
            <li>
               <a class="shop-sidebar__check<?= $active ? ' is-active' : ''; ?>" href="<?= esc_url((string) get_term_link($term)); ?>">
                  <span class="shop-sidebar__box" aria-hidden="true"></span>
                  <?= esc_html($term->name); ?>
               </a>
            </li>
         <?php endforeach; ?>
      </ul>
   </div>

   <div class="shop-sidebar__card">
      <h4 class="shop-sidebar__title"><?php esc_html_e('Max Price', 'detailking'); ?></h4>
      <form class="shop-sidebar__price" method="get" data-dk-price-filter>
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
