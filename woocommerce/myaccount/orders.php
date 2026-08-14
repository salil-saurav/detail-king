<?php

/**
 * Orders — "previous and current orders" and "services purchased"
 * (BUILD-PLAN §7 Phase 1 step 11, TASK-BRIEF's required My Account fields).
 * Deliberately the same view for both — every real line item already is a
 * purchased service/package/product/membership (BUILD-PLAN §2's chosen
 * commerce model), so a second "services purchased" report would just
 * duplicate this one.
 *
 * This is Woo's own real orders.php logic/markup ($has_orders,
 * $customer_orders, $current_page, $wp_button_class — same variables Woo's
 * default template receives), wrapped in the theme's dark card + restyled
 * as stacked rows at mobile widths (assets/css/pages/account.css) rather
 * than left as a raw <table> — this build's own established lesson that a
 * repeating list collapses badly on narrow viewports if not explicitly
 * redesigned (CLAUDE.md gotchas) applies directly here. No order logic is
 * rewritten — same restyle-don't-rebuild rule cart.php/form-checkout.php
 * already follow.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

do_action('woocommerce_before_account_orders', $has_orders);
?>
<section class="dk-account-card dk-account-orders">
   <h2 class="dk-account-card__title"><?php esc_html_e('Orders', 'detailking'); ?></h2>

   <?php if ($has_orders) : ?>

      <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table dk-account-orders-table">
         <thead>
            <tr>
               <?php foreach (wc_get_account_orders_columns() as $column_id => $column_name) : ?>
                  <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr($column_id); ?>"><span class="nobr"><?php echo esc_html($column_name); ?></span></th>
               <?php endforeach; ?>
            </tr>
         </thead>

         <tbody>
            <?php foreach ($customer_orders->orders as $customer_order) : ?>
               <?php
               $order      = wc_get_order($customer_order);
               $item_count = $order->get_item_count() - $order->get_item_count_refunded();
               ?>
               <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
                  <?php foreach (wc_get_account_orders_columns() as $column_id => $column_name) : ?>
                     <?php $isOrderNumber = 'order-number' === $column_id; ?>
                     <?php if ($isOrderNumber) : ?>
                        <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>" scope="row">
                     <?php else : ?>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>">
                     <?php endif; ?>

                        <?php if (has_action('woocommerce_my_account_my_orders_column_' . $column_id)) : ?>
                           <?php do_action('woocommerce_my_account_my_orders_column_' . $column_id, $order); ?>
                        <?php elseif ($isOrderNumber) : ?>
                           <a href="<?php echo esc_url($order->get_view_order_url()); ?>" aria-label="<?php echo esc_attr(sprintf(__('View order number %s', 'detailking'), $order->get_order_number())); ?>">
                              <?php echo esc_html(_x('#', 'hash before order number', 'detailking') . $order->get_order_number()); ?>
                           </a>
                        <?php elseif ('order-date' === $column_id) : ?>
                           <time datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></time>
                        <?php elseif ('order-status' === $column_id) : ?>
                           <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                        <?php elseif ('order-total' === $column_id) : ?>
                           <?php
                           printf(
                              /* translators: 1: formatted order total 2: total order items */
                              esc_html(_n('%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'detailking')),
                              wp_kses_post($order->get_formatted_order_total()),
                              (int) $item_count
                           );
                           ?>
                        <?php elseif ('order-actions' === $column_id) : ?>
                           <?php $actions = wc_get_account_orders_actions($order); ?>
                           <?php foreach ($actions as $key => $action) : ?>
                              <a href="<?php echo esc_url($action['url']); ?>" class="woocommerce-button<?php echo esc_attr($wp_button_class); ?> button dk-account-orders-action <?php echo sanitize_html_class($key); ?>"><?php echo esc_html($action['name']); ?></a>
                           <?php endforeach; ?>
                        <?php endif; ?>

                     <?php if ($isOrderNumber) : ?>
                        </th>
                     <?php else : ?>
                        </td>
                     <?php endif; ?>
                  <?php endforeach; ?>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>

      <?php do_action('woocommerce_before_account_orders_pagination'); ?>

      <?php if (1 < $customer_orders->max_num_pages) : ?>
         <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination dk-account-pagination">
            <?php if (1 !== $current_page) : ?>
               <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>"><?php esc_html_e('Previous', 'detailking'); ?></a>
            <?php endif; ?>
            <?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
               <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>"><?php esc_html_e('Next', 'detailking'); ?></a>
            <?php endif; ?>
         </div>
      <?php endif; ?>

   <?php else : ?>

      <p class="body-base"><?php esc_html_e('No order has been made yet.', 'detailking'); ?></p>
      <a class="btn-gold btn-arrow" href="<?= esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
         <?php esc_html_e('Browse Products', 'detailking'); ?>
      </a>

   <?php endif; ?>
</section>
<?php
do_action('woocommerce_after_account_orders', $has_orders);
