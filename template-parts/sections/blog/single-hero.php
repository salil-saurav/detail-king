<?php

/**
 * Single post hero banner (Figma node 180:6583).
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$postId = get_the_ID();
$title  = get_the_title($postId);

$categories = get_the_category($postId);
$primaryCat = !empty($categories) ? $categories[0] : null;
$catName    = $primaryCat ? $primaryCat->name : __('Blog', 'detailking');
$catUrl     = $primaryCat ? get_category_link($primaryCat->term_id) : home_url('/blog/');

$blogPageId = (int) get_option('page_for_posts');
$blogUrl    = $blogPageId ? get_permalink($blogPageId) : home_url('/blog/');

// Reading time
$customReadTime = get_post_meta($postId, '_dk_reading_time', true);
if (empty($customReadTime) && function_exists('get_field')) {
   $customReadTime = get_field('reading_time', $postId);
}
if (!empty($customReadTime)) {
   $readingTime = (string) $customReadTime;
} else {
   $contentRaw = get_post_field('post_content', $postId);
   $wordCount  = str_word_count(strip_tags((string) $contentRaw));
   $minutes    = max(1, (int) ceil($wordCount / 200));
   $readingTime = $minutes . ' min read';
}

$thumbId  = get_post_thumbnail_id($postId);
$thumbUrl = $thumbId ? wp_get_attachment_image_url($thumbId, 'full') : '';
?>

<section class="single-hero" data-hero>
   <?php if ($thumbUrl) : ?>
      <div class="single-hero__bg" data-hero-bg aria-hidden="true">
         <img src="<?= esc_url($thumbUrl); ?>" alt="<?= esc_attr($title); ?>" fetchpriority="high">
      </div>
   <?php endif; ?>

   <div class="container-dk">
      <div class="single-hero__inner">
         <nav class="single-hero__breadcrumb" aria-label="<?php esc_attr_e('Breadcrumbs', 'detailking'); ?>">
            <ul class="breadcrumb">
               <li class="breadcrumb__item">
                  <a class="breadcrumb__link" href="<?= esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'detailking'); ?></a>
                  <span class="breadcrumb__separator mx-2" aria-hidden="true">/</span>
               </li>
               <li class="breadcrumb__item">
                  <a class="breadcrumb__link" href="<?= esc_url($blogUrl); ?>"><?php esc_html_e('Blog', 'detailking'); ?></a>
                  <span class="breadcrumb__separator mx-2" aria-hidden="true">/</span>
               </li>
               <li class="breadcrumb__item">
                  <span class="breadcrumb__current"><?= esc_html($catName); ?></span>
               </li>
            </ul>
         </nav>

         <div class="single-hero__badge-wrap" data-animate>
            <a href="<?= esc_url($catUrl); ?>" class="single-hero__badge">
               <span class="single-hero__badge-star" aria-hidden="true">★</span>
               <?= esc_html($catName); ?>
            </a>
         </div>

         <h1 class="single-hero__title" data-animate>
            <?= esc_html($title); ?>
         </h1>

         <div class="single-hero__meta" data-animate>
            <div class="single-hero__author-block">
               <span class="single-hero__avatar" aria-hidden="true">DK</span>
               <span class="single-hero__author"><?php esc_html_e('Detail King Team', 'detailking'); ?></span>
            </div>

            <span class="single-hero__dot" aria-hidden="true"></span>

            <time class="single-hero__date" datetime="<?= esc_attr(get_the_date('c', $postId)); ?>">
               <?= esc_html(get_the_date('j F Y', $postId)); ?>
            </time>

            <span class="single-hero__dot" aria-hidden="true"></span>

            <span class="single-hero__read-time">
               <?= esc_html($readingTime); ?>
            </span>
         </div>
      </div>
   </div>
</section>
