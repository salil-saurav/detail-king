<?php

/**
 * Checkout — no Figma comp exists for this page (see TASK-BRIEF.md §11
 * "Missing designs"); derived from the established language. Structurally
 * this is very close to Woo's own form-checkout.php (it's mostly hooks
 * already) — the billing/shipping field sets, order review table and
 * payment method list all stay Woo's real templates/functions, restyled via
 * CSS rather than rebuilt, since none of it is custom logic this theme
 * should own.
 *
 * Reached via the `[woocommerce_checkout]` shortcode inside page.php's own
 * loop, same as cart.php — no get_header()/get_footer() or duplicate
 * title/breadcrumb here, page.php already rendered both. The shortcode also
 * branches to checkout/thankyou.php (order confirmation) and
 * checkout/cart-errors.php on their own — neither is overridden yet; both
 * fall back to Woo's default markup for now.
 *
 * Stripe is blocked on client credentials (TASK-BRIEF §7 step 10) — whatever
 * gateways are active in wp-admin (Woo's test gateways today) show here
 * automatically via woocommerce_checkout_payment; no gateway-specific code
 * lives in this template.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$checkout = WC()->checkout();
?>
<div class="dk-checkout-page">

   <?php wc_print_notices(); ?>

   <?php do_action('woocommerce_before_checkout_form', $checkout); ?>

   <?php if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) : ?>
      <p><?= esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'detailking'))); ?></p>
   <?php else : ?>

      <form name="checkout" method="post" class="dk-checkout-layout checkout woocommerce-checkout" action="<?= esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

         <div class="dk-checkout-fields">
            <?php if ($checkout->get_checkout_fields()) : ?>
               <?php do_action('woocommerce_checkout_before_customer_details'); ?>

               <div class="dk-checkout-card">
                  <?php do_action('woocommerce_checkout_billing'); ?>
               </div>

               <div class="dk-checkout-card">
                  <?php do_action('woocommerce_checkout_shipping'); ?>
               </div>

               <?php do_action('woocommerce_checkout_after_customer_details'); ?>
            <?php endif; ?>
         </div>

         <aside class="dk-checkout-summary">
            <div class="dk-checkout-card">
               <h2 id="order_review_heading"><?php esc_html_e('Your Order', 'detailking'); ?></h2>

               <?php do_action('woocommerce_checkout_before_order_review'); ?>

               <div id="order_review" class="woocommerce-checkout-review-order">
                  <?php do_action('woocommerce_checkout_order_review'); ?>
               </div>

               <?php do_action('woocommerce_checkout_after_order_review'); ?>
            </div>
         </aside>

      </form>

   <?php endif; ?>

   <?php do_action('woocommerce_after_checkout_form', $checkout); ?>

</div>
