<?php

/**
 * Product card, matching the comp's shop rail.
 *
 *   get_template_part('template-parts/components/product-card', null, ['product' => $post]);
 *
 * Uses WooCommerce's own price HTML and add-to-cart URL rather than reading meta
 * directly, so sale prices, variable ranges, tax display and out-of-stock states
 * come out right without this template knowing about any of them.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$postObj = $args['product'] ?? null;

if (!$postObj instanceof WP_Post || !function_exists('wc_get_product')) {
   return;
}

$product = wc_get_product($postObj);

if (!$product) {
   return;
}

$thumb = get_the_post_thumbnail_url($postObj, 'medium_large');

/* Badge: "Sale" is derived; anything else is an editorial label on the product. */
$badge = '';
if ($product->is_on_sale()) {
   $badge = __('Sale', 'detailking');
}
$custom = get_post_meta($postObj->ID, '_dk_badge', true);
if (is_string($custom) && $custom !== '') {
   $badge = $custom;
}

$inStock = $product->is_in_stock();
?>
<article class="prod-card">

   <a class="prod-card__media" href="<?= esc_url((string) get_permalink($postObj)); ?>">
      <?php if ($thumb) : ?>
         <img src="<?= esc_url($thumb); ?>" alt="<?= esc_attr(get_the_title($postObj)); ?>" loading="lazy" decoding="async">
      <?php endif; ?>

      <?php if ($badge !== '') : ?>
         <span class="prod-card__badge body-base-med"><?= esc_html($badge); ?></span>
      <?php endif; ?>
   </a>

   <div class="prod-card__body">
      <h3 class="prod-card__title subheading-xs">
         <a href="<?= esc_url((string) get_permalink($postObj)); ?>"><?= esc_html(get_the_title($postObj)); ?></a>
      </h3>

      <?php $excerpt = $product->get_short_description(); ?>
      <?php if ($excerpt !== '') : ?>
         <p class="prod-card__text body-sm"><?= esc_html(wp_strip_all_tags($excerpt)); ?></p>
      <?php endif; ?>

      <div class="prod-card__foot">
         <span class="prod-card__price subheading-xs"><?= wp_kses_post($product->get_price_html()); ?></span>

         <?php if ($inStock && $product->is_purchasable() && $product->is_type('simple')) : ?>
            <a class="btn-dark prod-card__add"
               href="<?= esc_url($product->add_to_cart_url()); ?>"
               data-product_id="<?= esc_attr((string) $product->get_id()); ?>"
               rel="nofollow">
               <?php esc_html_e('Add to Cart', 'detailking'); ?> <span aria-hidden="true">+</span>
            </a>
         <?php else : ?>
            <?php /* Variable, external or out of stock: send them to the product page
                     rather than showing an add-to-cart that cannot work. */ ?>
            <a class="btn-dark prod-card__add" href="<?= esc_url((string) get_permalink($postObj)); ?>">
               <?= $inStock ? esc_html__('View Product', 'detailking') : esc_html__('Out of Stock', 'detailking'); ?>
            </a>
         <?php endif; ?>
      </div>
   </div>

</article>
