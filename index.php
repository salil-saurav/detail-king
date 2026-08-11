<?php

/**
 * The main template file.
 *
 * The most generic template in a WordPress theme and one of the two required
 * files (the other being style.css). Used as a fallback for any query the theme
 * does not provide a more specific template for.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<section class="content-section py-5">
   <div class="container">

      <header class="page-header mb-4">
         <h1 class="page-title"><?= esc_html(wp_get_document_title()); ?></h1>
         <?php get_template_part('template-parts/global/breadcrumb'); ?>
      </header>

      <?php if (have_posts()) : ?>
         <div class="row g-4">
            <?php while (have_posts()) : the_post(); ?>
               <div class="col-md-6 col-lg-4">
                  <article <?php post_class('post-card h-100'); ?>>
                     <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" class="post-card__image d-block mb-3">
                           <?php the_post_thumbnail('large', ['class' => 'img-fluid']); ?>
                        </a>
                     <?php endif; ?>
                     <div class="post-card__meta text-muted small mb-1"><?= esc_html(get_the_date()); ?></div>
                     <h2 class="h5 post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                     <p><?= esc_html(wp_trim_words(get_the_excerpt(), 24, '…')); ?></p>
                  </article>
               </div>
            <?php endwhile; ?>
         </div>

         <?php the_posts_pagination(); ?>
      <?php else : ?>
         <p><?php esc_html_e('Nothing found.'); ?></p>
      <?php endif; ?>
   </div>
</section>

<?php get_footer(); ?>
