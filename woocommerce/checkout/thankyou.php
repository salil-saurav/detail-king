<?php

/**
 * Order received / thank-you page (BUILD-PLAN §7 Phase 1, alongside step 11's
 * My Account work — the checkout flow needs *some* real landing after an
 * order completes). No Figma comp exists for this page (same "derived from
 * the established language" status as Cart/Checkout — see their own
 * docblocks).
 *
 * Reached via WC_Shortcode_Checkout's own order-received branch inside
 * page.php's loop, same contract as cart.php/form-checkout.php — no
 * get_header()/get_footer() or duplicate title/breadcrumb, page.php already
 * rendered both. `$order` is the same variable Woo's default template
 * receives (false when the order can't be found/viewed) — all real order
 * logic/hooks kept exactly as Woo renders them, only the surrounding chrome
 * and the "what's next" guidance block are new.
 *
 * @package DetailKing Theme
 * @var WC_Order|false $order
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
?>
<div class="woocommerce-order dk-checkout-card dk-thankyou">

   <?php if ($order) : ?>

      <?php do_action('woocommerce_before_thankyou', $order->get_id()); ?>

      <?php if ($order->has_status('failed')) : ?>

         <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">
            <?php esc_html_e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'detailking'); ?>
         </p>
         <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
            <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="btn-gold"><?php esc_html_e('Pay', 'detailking'); ?></a>
            <?php if (is_user_logged_in()) : ?>
               <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="btn-dark"><?php esc_html_e('My Account', 'detailking'); ?></a>
            <?php endif; ?>
         </p>

      <?php else : ?>

         <span class="eyebrow eyebrow--badge dk-thankyou__badge"><?php esc_html_e('Order Confirmed', 'detailking'); ?></span>
         <h1 class="dk-thankyou__title"><?php esc_html_e('Thank You For Your Order', 'detailking'); ?></h1>
         <p class="body-base dk-thankyou__lead"><?php esc_html_e("We've received your order and will be in touch shortly to confirm the details.", 'detailking'); ?></p>

         <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details dk-thankyou-overview">
            <li class="woocommerce-order-overview__order order">
               <?php esc_html_e('Order number:', 'detailking'); ?>
               <strong><?php echo esc_html($order->get_order_number()); ?></strong>
            </li>
            <li class="woocommerce-order-overview__date date">
               <?php esc_html_e('Date:', 'detailking'); ?>
               <strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong>
            </li>
            <?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email()) : ?>
               <li class="woocommerce-order-overview__email email">
                  <?php esc_html_e('Email:', 'detailking'); ?>
                  <strong><?php echo esc_html($order->get_billing_email()); ?></strong>
               </li>
            <?php endif; ?>
            <li class="woocommerce-order-overview__total total">
               <?php esc_html_e('Total:', 'detailking'); ?>
               <strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
            </li>
            <?php if ($order->get_payment_method_title()) : ?>
               <li class="woocommerce-order-overview__payment-method method">
                  <?php esc_html_e('Payment method:', 'detailking'); ?>
                  <strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
               </li>
            <?php endif; ?>
         </ul>

         <div class="dk-thankyou__next">
            <h2 class="dk-thankyou__subtitle"><?php esc_html_e("What's Next", 'detailking'); ?></h2>
            <p class="body-base">
               <?php esc_html_e("If your order includes a service booking, our team will confirm your drop-off date and time by phone or email. You can review your order any time from your account.", 'detailking'); ?>
            </p>
            <?php $address = (string) $meta->optOr('contact_address'); ?>
            <?php if ($address !== '') : ?>
               <p class="body-base dk-thankyou__address">
                  <strong><?php esc_html_e('Visit us:', 'detailking'); ?></strong> <?php echo esc_html($address); ?>
               </p>
            <?php endif; ?>
            <a class="btn-gold btn-arrow" href="<?= esc_url(wc_get_page_permalink('myaccount')); ?>">
               <?php esc_html_e('Go to My Account', 'detailking'); ?>
            </a>
         </div>

      <?php endif; ?>

      <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
      <?php do_action('woocommerce_thankyou', $order->get_id()); ?>

   <?php else : ?>

      <?php wc_get_template('checkout/order-received.php', ['order' => false]); ?>

   <?php endif; ?>

</div>
