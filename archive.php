<?php

/**
 * Archive template — categories, tags, custom post types, dates, authors.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<section class="archive-section py-5">
   <div class="container">

      <header class="page-header mb-4">
         <h1 class="page-title"><?php the_archive_title(); ?></h1>
         <?php
         the_archive_description('<div class="archive-description text-muted">', '</div>');
         get_template_part('template-parts/global/breadcrumb');
         ?>
      </header>

      <?php if (have_posts()) : ?>
         <div class="row g-4">
            <?php while (have_posts()) : the_post(); ?>
               <div class="col-md-6 col-lg-4">
                  <article <?php post_class('post-card h-100'); ?>>
                     <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" class="d-block mb-3">
                           <?php the_post_thumbnail('medium_large', ['class' => 'img-fluid', 'loading' => 'lazy']); ?>
                        </a>
                     <?php endif; ?>
                     <h2 class="h5"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                     <p><?= esc_html(wp_trim_words(get_the_excerpt(), 22, '…')); ?></p>
                  </article>
               </div>
            <?php endwhile; ?>
         </div>

         <?php the_posts_pagination(['mid_size' => 2]); ?>
      <?php else : ?>
         <p><?php esc_html_e('Nothing found.'); ?></p>
      <?php endif; ?>
   </div>
</section>

<?php get_footer(); ?>
