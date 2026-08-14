<?php

/**
 * Recommendation popup — 10%-off cross-sell modal (BUILD-PLAN §7 Phase 1
 * step 8). Rendered once, empty, as a direct child of <body> (same reason
 * SearchService::renderOverlay() does this: fixed positioning must be
 * viewport-relative, never trapped inside a transformed/sticky ancestor).
 *
 * assets/js/cross-sell.js populates [data-cross-sell-grid] from the
 * `crossSellHtml` a successful add-to-cart response returns — this template
 * never pre-fills it, since the seed product isn't known until an add
 * actually happens.
 *
 * Mirrors SearchService's overlay shell exactly (markup shape, hidden
 * attribute + .is-open class, role="dialog"/aria-modal) rather than the
 * plain data-sp-toggle class-toggle, which has no focus/escape handling.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;
?>
<div class="cross-sell-overlay" id="cross-sell-overlay" data-cross-sell-overlay hidden>
   <div class="cross-sell-panel card-glass" id="cross-sell-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Recommended for you', 'detailking'); ?>">
      <button type="button" class="cross-sell-panel__close btn" aria-label="<?php esc_attr_e('Close', 'detailking'); ?>" data-cross-sell-close>&times;</button>

      <div class="cross-sell-panel__head">
         <span class="eyebrow eyebrow--rule-start"><?php esc_html_e('Added to Cart', 'detailking'); ?></span>
         <h2 class="cross-sell-panel__title">
            <?php esc_html_e('Complete Your', 'detailking'); ?> <span class="text-gold-gradient"><?php esc_html_e('Order', 'detailking'); ?></span>
         </h2>
         <p class="cross-sell-panel__text"><?php esc_html_e('Customers often add these at 10% off — instantly, no extra checkout steps.', 'detailking'); ?></p>
      </div>

      <div class="cross-sell-panel__grid" data-cross-sell-grid></div>

      <a class="btn-outline-light-dk cross-sell-panel__cart-link" data-cross-sell-cart-link href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/')); ?>">
         <?php esc_html_e('Go to Cart', 'detailking'); ?> <span aria-hidden="true">&rarr;</span>
      </a>
   </div>
</div>
