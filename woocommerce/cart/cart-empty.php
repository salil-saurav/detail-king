<?php

/**
 * Empty cart — a separate template from cart.php, not a branch inside it:
 * the `[woocommerce_cart]` shortcode calls this file directly when the cart
 * has nothing in it (WC_Shortcode_Cart::output()), so cart.php is never
 * reached at all in that state. Same rules as cart.php: no get_header()/
 * get_footer(), page.php already provided both.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;
?>
<div class="dk-cart-empty">
   <p><?php esc_html_e('Your cart is empty — browse the shop to find something for your car.', 'detailking'); ?></p>

   <?php if (wc_get_page_id('shop') > 0) : ?>
      <a class="btn-gold" href="<?= esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
         <?php esc_html_e('Continue Shopping', 'detailking'); ?>
      </a>
   <?php endif; ?>
</div>
