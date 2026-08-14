<?php

/**
 * Shop + category archive — full override, not a copy-and-tweak of Woo's own
 * archive-product.php. Two genuinely different page designs share this one
 * request type (see figma-data/shop-spec.md):
 *
 *   - is_shop(): the "Product Categories" comp — a tile grid of the real
 *     product_cat terms, no product loop at all.
 *   - is_product_category(): the "All Products" comp — sidebar filter +
 *     the real product grid (WC_Query's main loop).
 *
 * Woo's default content-wrapper hooks (woocommerce_before/after_main_content)
 * are deliberately not called — this page doesn't use Woo's wrapper markup,
 * so firing them would add unused hook points without doing anything useful.
 * wc_print_notices() is still called explicitly so add-to-cart / stock
 * messages aren't silently dropped.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

echo '<div class="shop-notices container-dk">';
wc_print_notices();
echo '</div>';

if (is_shop()) {
   get_template_part('template-parts/sections/shop/categories-hero');
   get_template_part('template-parts/sections/shop/categories-grid');
   get_template_part('template-parts/sections/shop/why-shop');
} else {
   get_template_part('template-parts/sections/shop/listing-hero');
   get_template_part('template-parts/sections/shop/listing');
}

get_footer();
