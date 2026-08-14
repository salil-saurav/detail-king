<?php

/**
 * Product Detail — full override, same reasoning as archive-product.php:
 * the comp (node 185:8134, see figma-data/shop-spec.md §C) doesn't use Woo's
 * default content-wrapper markup, so firing woocommerce_before/after_main_content
 * would add hook points with nothing to attach to.
 *
 * Still uses Woo's own functions for anything that carries real logic
 * (gallery zoom/lightbox, price HTML incl. sale %, the add-to-cart form,
 * related products) rather than re-deriving them — see product-hero.php and
 * product-related.php.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

echo '<div class="shop-notices container-dk">';
wc_print_notices();
echo '</div>';

while (have_posts()) :
   the_post();

   global $product;

   if (post_password_required()) {
      echo get_the_password_form();
      continue;
   }

   get_template_part('template-parts/sections/shop/product-hero');
   get_template_part('template-parts/sections/shop/product-related');
endwhile;

get_footer();
