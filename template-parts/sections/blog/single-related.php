<?php

/**
 * Related articles section for Blog Single view (Figma node 180:6705).
 *
 * Displays up to 3 related posts sharing the current post's category
 * (or fallback to latest posts).
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$postId = get_the_ID();
$categories = get_the_category($postId);
$catIds = !empty($categories) ? wp_list_pluck($categories, 'term_id') : [];

$queryArgs = [
   'post_type'      => 'post',
   'post_status'    => 'publish',
   'posts_per_page' => 3,
   'post__not_in'   => [$postId],
   'orderby'        => 'date',
   'order'          => 'DESC',
];

if (!empty($catIds)) {
   $queryArgs['category__in'] = $catIds;
}

$relatedQuery = new WP_Query($queryArgs);

// Fallback if not enough category matches
if ($relatedQuery->post_count < 3) {
   $needed = 3 - $relatedQuery->post_count;
   $exclude = array_merge([$postId], wp_list_pluck($relatedQuery->posts, 'ID'));
   $fallbackQuery = new WP_Query([
      'post_type'      => 'post',
      'post_status'    => 'publish',
      'posts_per_page' => $needed,
      'post__not_in'   => $exclude,
      'orderby'        => 'date',
      'order'          => 'DESC',
   ]);

   $relatedPosts = array_merge($relatedQuery->posts, $fallbackQuery->posts);
} else {
   $relatedPosts = $relatedQuery->posts;
}

wp_reset_postdata();

if (empty($relatedPosts)) {
   return;
}
?>

<section class="single-related" data-animate="fade">
   <div class="container-dk">
      <div class="single-related__header">
         <div class="single-related__eyebrow" data-animate="fade-up">
            <span class="single-related__rule" aria-hidden="true"></span>
            <span class="single-related__eyebrow-text"><?php esc_html_e('KEEP READING', 'detailking'); ?></span>
         </div>
         <h2 class="single-related__title" data-animate="fade-up">
            <?= sprintf(
               /* translators: %s: gold accent text */
               esc_html__('Related %s', 'detailking'),
               '<span class="text-gold-gradient">' . esc_html__('Articles', 'detailking') . '</span>'
            ); ?>
         </h2>
      </div>

      <div class="single-related__grid">
         <?php foreach ($relatedPosts as $post) : ?>
            <?php
            get_template_part('template-parts/sections/blog/card', null, [
               'post_id'  => $post->ID,
               'featured' => false,
               'class'    => 'blog-card--related',
            ]);
            ?>
         <?php endforeach; ?>
      </div>
   </div>
</section>
