<?php

/**
 * Template for displaying single pages — the fallback for every Page still on
 * WordPress' "Default Template": Cart, Checkout, My Account, Contact,
 * Memberships, Login, Signup and the rest. Woo's cart/checkout templates
 * (woocommerce/cart/cart.php, woocommerce/checkout/form-checkout.php) rely on
 * this file to render the title/breadcrumb via page-banner and the
 * .container-dk wrapper before their own shortcode markup runs — see their
 * own docblocks.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

get_header();

$meta = MetaHelper::getInstance();

while (have_posts()) : the_post();

   get_template_part('template-parts/global/page-banner', null, [
      'eyebrow' => $meta->field('hero_eyebrow'),
      'title'   => $meta->field('hero_title') ?: get_the_title(),
      'text'    => $meta->field('hero_text'),
      'bg'      => $meta->imageUrl($meta->field('hero_bg_image')),
   ]);
?>

   <section class="content-section py-5">
      <div class="container-dk">

         <div class="entry-content">
            <?php
            the_content();
            wp_link_pages([
               'before' => '<div class="page-links">' . esc_html__('Pages:'),
               'after'  => '</div>',
            ]);
            ?>
         </div>

         <?php
         if (comments_open() || get_comments_number()) {
            comments_template();
         }
         ?>

      </div>
   </section>

<?php endwhile; ?>

<?php get_footer(); ?>
