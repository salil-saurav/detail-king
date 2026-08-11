<?php

/**
 * Template for displaying single pages.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<section class="content-section py-5">
   <div class="container">
      <?php while (have_posts()) : the_post(); ?>

         <header class="page-header mb-4">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <?php get_template_part('template-parts/global/breadcrumb'); ?>
         </header>

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

      <?php endwhile; ?>
   </div>
</section>

<?php get_footer(); ?>
