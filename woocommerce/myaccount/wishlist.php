<?php

/**
 * My Account > Wishlisted — WishlistService's custom endpoint content
 * (`woocommerce_account_wishlist_endpoint`), same "reuse product-card.php,
 * don't hand-roll new card markup" house rule the shop grid/related rail
 * already follow — see product-card.php's own docblock.
 *
 * `wishlist_remove => true` swaps the card's save heart for a remove icon
 * that drops the card from the DOM once unsaved (global.js), so a customer
 * clearing their wishlist here sees it happen without a page reload.
 *
 * $products is an array of WC_Product, newest-saved first
 * (WishlistService::renderEndpoint()).
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;
?>
<section class="dk-account-card dk-account-wishlist">
   <h2 class="dk-account-card__title"><?php esc_html_e('Wishlisted', 'detailking'); ?></h2>

   <?php if ($products) : ?>
      <div class="dk-account-wishlist__grid">
         <?php foreach ($products as $product) : ?>
            <?php
            $productPost = get_post($product->get_id());
            if (!$productPost) continue;
            get_template_part('template-parts/components/product-card', null, [
               'product'         => $productPost,
               'wishlist_remove' => true,
            ]);
            ?>
         <?php endforeach; ?>
      </div>
   <?php else : ?>
      <p class="body-base"><?php esc_html_e("You haven't saved anything yet — tap the heart on a product to add it here.", 'detailking'); ?></p>
      <a class="btn-gold btn-arrow" href="<?= esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
         <?php esc_html_e('Browse Products', 'detailking'); ?>
      </a>
   <?php endif; ?>
</section>
