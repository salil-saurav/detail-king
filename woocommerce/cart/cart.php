<?php

/**
 * Cart — no Figma comp exists for this page (see TASK-BRIEF.md §11 "Missing
 * designs"); derived from the established dark/gold card language rather
 * than copying Woo's default table markup wholesale.
 *
 * Reached via the `[woocommerce_cart]` shortcode inside page.php's own
 * loop — WooCommerce only hijacks the full page template for the shop and
 * single-product/category views (see WC_Template_Loader::
 * get_template_loader_default_file(), which lists neither cart nor
 * checkout), so this file must NOT call get_header()/get_footer() itself or
 * duplicate page.php's own title/breadcrumb — that page already rendered
 * both before including this. The empty-cart state never reaches this file
 * at all; the shortcode calls cart/cart-empty.php directly for that.
 *
 * Still built entirely on real Woo data/functions (cart contents, product
 * image/name/price, quantity input, coupon form, cart totals) — a custom
 * card layout instead of Woo's <table>, not custom cart logic.
 *
 * The 10%-discount cross-sell rail promised in the brief (§7 step 8) is a
 * separate, not-yet-built feature (recommendation-modal.php) — the hook
 * below is where it attaches once built, left inert on purpose rather than
 * half-implementing the discount logic here.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;
?>
<div class="dk-cart-page">

   <?php wc_print_notices(); ?>

   <?php do_action('woocommerce_before_cart'); ?>

   <form class="dk-cart-layout" action="<?= esc_url(wc_get_cart_url()); ?>" method="post">

      <div class="dk-cart-lines">
         <?php do_action('woocommerce_before_cart_contents'); ?>

         <?php foreach (WC()->cart->get_cart() as $cartItemKey => $cartItem) : ?>
            <?php
            $productObj = apply_filters('woocommerce_cart_item_product', $cartItem['data'], $cartItem, $cartItemKey);
            $productId  = apply_filters('woocommerce_cart_item_product_id', $cartItem['product_id'], $cartItem, $cartItemKey);

            if (!$productObj instanceof WC_Product || !$productObj->exists() || $cartItem['quantity'] <= 0) {
               continue;
            }

            $name      = apply_filters('woocommerce_cart_item_name', $productObj->get_name(), $cartItem, $cartItemKey);
            $permalink = apply_filters('woocommerce_cart_item_permalink', $productObj->is_visible() ? $productObj->get_permalink($cartItem) : '', $cartItem, $cartItemKey);
            $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $productObj->get_image('thumbnail'), $cartItem, $cartItemKey);
            ?>
            <div class="dk-cart-line" data-cart-item-key="<?= esc_attr($cartItemKey); ?>">

               <a class="dk-cart-line__media" href="<?= esc_url($permalink); ?>"><?= wp_kses_post($thumbnail); ?></a>

               <div class="dk-cart-line__body">
                  <a class="dk-cart-line__title" href="<?= esc_url($permalink); ?>"><?= wp_kses_post($name); ?></a>
                  <span class="dk-cart-line__price"><?= wp_kses_post(WC()->cart->get_product_price($productObj)); ?></span>
                  <?php
                  /* Booking details (vehicle size already reads via the product
                     name; date/time/location/notes come from BookingWidgetService's
                     `woocommerce_get_item_data` hook) — the only place a customer
                     sees what they picked before checkout. */
                  $itemData = wc_get_formatted_cart_item_data($cartItem);
                  if ($itemData !== '') : ?>
                     <div class="dk-cart-line__meta"><?= wp_kses_post($itemData); ?></div>
                  <?php endif; ?>
               </div>

               <?php if ($productObj->is_sold_individually()) : ?>
                  <span class="dk-cart-line__qty-fixed">1</span>
               <?php else : ?>
                  <?php
                  /* $productObj is passed explicitly: left null, Woo reads
                     `$GLOBALS['product']` (wc-template-functions.php), which this
                     custom template never sets — a PHP 8.4 "undefined array key"
                     warning at best, and at worst the *wrong* product's quantity
                     rules (step / min / max) applied to this line, since any
                     earlier product loop on the page leaves its own last product
                     in that global. */
                  woocommerce_quantity_input([
                     'input_name'  => "cart[{$cartItemKey}][qty]",
                     'input_value' => $cartItem['quantity'],
                     'max_value'   => $productObj->get_max_purchase_quantity(),
                     'min_value'   => '0',
                  ], $productObj);
                  ?>
               <?php endif; ?>

               <span class="dk-cart-line__subtotal"><?= wp_kses_post(apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($productObj, $cartItem['quantity']), $cartItem, $cartItemKey)); ?></span>

               <a role="button" href="<?= esc_url(wc_get_cart_remove_url($cartItemKey)); ?>" class="dk-cart-line__remove" aria-label="<?= esc_attr(sprintf(__('Remove %s from cart', 'detailking'), wp_strip_all_tags($name))); ?>">&times;</a>

            </div>
         <?php endforeach; ?>

         <?php do_action('woocommerce_cart_contents'); ?>
         <?php do_action('woocommerce_after_cart_contents'); ?>

         <div class="dk-cart-actions">
            <div class="dk-cart-coupon">
               <?php if (wc_coupons_enabled()) : ?>
                  <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e('Coupon code', 'detailking'); ?>">
                  <button type="submit" class="btn-outline-light-dk" name="apply_coupon" value="<?php esc_attr_e('Apply', 'detailking'); ?>">
                     <?php esc_html_e('Apply', 'detailking'); ?>
                  </button>
               <?php endif; ?>
            </div>

            <button type="submit" class="btn-dark" name="update_cart" value="<?php esc_attr_e('Update Cart', 'detailking'); ?>">
               <?php esc_html_e('Update Cart', 'detailking'); ?>
            </button>
         </div>

         <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
      </div>

      <aside class="dk-cart-summary">
         <h2 class="dk-cart-summary__title"><?php esc_html_e('Order Summary', 'detailking'); ?></h2>

         <?php
         /**
          * `woocommerce_cart_totals` is a callback name, not an action — the
          * real hook is `woocommerce_cart_collaterals` (see Woo's own
          * cart.php). This also fires woocommerce_cross_sell_display, Woo's
          * native cross-sell block, which doubles as a placeholder for the
          * brief's 10%-discount recommendation rail until that's built.
          */
         do_action('woocommerce_cart_collaterals');
         ?>

         <a href="<?= esc_url(wc_get_checkout_url()); ?>" class="btn-gold dk-cart-summary__checkout">
            <?php esc_html_e('Proceed to Checkout', 'detailking'); ?> <span aria-hidden="true">&rarr;</span>
         </a>
      </aside>

   </form>

   <?php do_action('woocommerce_after_cart'); ?>

   <?php
   /**
    * Cross-sell rail attaches here once recommendation-modal.php's
    * cart-page counterpart is built (TASK-BRIEF §7 step 8) — not yet
    * implemented, so nothing is hooked on this action today.
    */
   do_action('detailking_cart_cross_sell');
   ?>

</div>
