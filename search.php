<?php

/**
 * Search results.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<section class="search-results-section py-5">
   <div class="container">

      <header class="page-header mb-4">
         <h1 class="page-title">
            <?php
            /* translators: %s: search query. */
            printf(esc_html__('Search results for: %s'), '<span>' . esc_html(get_search_query()) . '</span>');
            ?>
         </h1>
         <?php get_template_part('template-parts/global/breadcrumb'); ?>
      </header>

      <?php if (have_posts()) : ?>
         <div class="search-list">
            <?php while (have_posts()) : the_post(); ?>
               <article <?php post_class('search-result mb-4 pb-4 border-bottom'); ?>>
                  <h2 class="h5 mb-1"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                  <div class="text-muted small mb-2"><?= esc_html(get_post_type()); ?> · <?= esc_html(get_the_date()); ?></div>
                  <p><?= esc_html(wp_trim_words(get_the_excerpt(), 30, '…')); ?></p>
               </article>
            <?php endwhile; ?>
         </div>

         <?php the_posts_pagination(['mid_size' => 2]); ?>
      <?php else : ?>
         <p><?php esc_html_e('Sorry, nothing matched your search. Try again with different keywords.'); ?></p>
         <?php get_search_form(); ?>
      <?php endif; ?>
   </div>
</section>

<?php get_footer(); ?>
