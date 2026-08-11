<?php

/**
 * Single post.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<section class="single-section py-5">
   <div class="container">
      <div class="row g-5 justify-content-center">
         <div class="col-lg-8">
            <?php while (have_posts()) : the_post(); ?>
               <article <?php post_class('single-post'); ?>>

                  <header class="mb-4">
                     <h1 class="entry-title"><?php the_title(); ?></h1>
                     <div class="entry-meta text-muted small">
                        <?= esc_html(get_the_date()); ?> · <?php the_author(); ?>
                     </div>
                     <?php get_template_part('template-parts/global/breadcrumb'); ?>
                  </header>

                  <?php if (has_post_thumbnail()) : ?>
                     <div class="single-post__image mb-4">
                        <?php the_post_thumbnail('large', ['class' => 'img-fluid']); ?>
                     </div>
                  <?php endif; ?>

                  <div class="entry-content">
                     <?php the_content(); ?>
                     <?php
                     wp_link_pages([
                        'before' => '<div class="page-links">' . esc_html__('Pages:'),
                        'after'  => '</div>',
                     ]);
                     ?>
                  </div>

                  <?php if (has_tag()) : ?>
                     <div class="single-tags mt-4">
                        <?php the_tags('<span class="label">' . esc_html__('Tags:') . '</span> ', ', '); ?>
                     </div>
                  <?php endif; ?>

                  <nav class="post-nav d-flex justify-content-between mt-4" aria-label="<?php esc_attr_e('Post navigation'); ?>">
                     <div><?php previous_post_link('%link', '&larr; %title'); ?></div>
                     <div><?php next_post_link('%link', '%title &rarr;'); ?></div>
                  </nav>

                  <?php
                  if (comments_open() || get_comments_number()) {
                     comments_template();
                  }
                  ?>
               </article>
            <?php endwhile; ?>
         </div>
      </div>
   </div>
</section>

<?php get_footer(); ?>
