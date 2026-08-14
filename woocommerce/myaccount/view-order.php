<?php

/**
 * View Order — single order detail behind Orders (BUILD-PLAN §7 Phase 1
 * step 11). $order/$order_id are the same variables Woo's default template
 * receives. do_action('woocommerce_view_order', $order_id) is the real
 * logic here (line items, totals, addresses) — kept exactly as Woo renders
 * it, only the surrounding chrome is themed.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$notes = $order->get_customer_order_notes();
?>
<section class="dk-account-card dk-account-view-order">
   <p class="body-base">
      <?php
      echo wp_kses_post(apply_filters(
         'woocommerce_order_details_status',
         sprintf(
            /* translators: 1: order number 2: order date 3: order status */
            esc_html__('Order #%1$s was placed on %2$s and is currently %3$s.', 'detailking'),
            '<mark class="order-number">' . $order->get_order_number() . '</mark>',
            '<mark class="order-date">' . wc_format_datetime($order->get_date_created()) . '</mark>',
            '<mark class="order-status">' . wc_get_order_status_name($order->get_status()) . '</mark>'
         ),
         $order
      ));
      ?>
   </p>

   <?php if ($notes) : ?>
      <h3 class="dk-account-card__subtitle"><?php esc_html_e('Order updates', 'detailking'); ?></h3>
      <ol class="woocommerce-OrderUpdates commentlist notes dk-account-order-notes">
         <?php foreach ($notes as $note) : ?>
            <li class="woocommerce-OrderUpdate comment note">
               <p class="woocommerce-OrderUpdate-meta meta"><?php echo esc_html(date_i18n(__('l jS \o\f F Y, h:ia', 'detailking'), strtotime($note->comment_date))); ?></p>
               <div class="woocommerce-OrderUpdate-description description">
                  <?php echo wp_kses_post(wpautop(wptexturize($note->comment_content))); ?>
               </div>
            </li>
         <?php endforeach; ?>
      </ol>
   <?php endif; ?>

   <?php do_action('woocommerce_view_order', $order_id); ?>
</section>
