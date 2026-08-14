<?php

/**
 * Reusable Blog Card component for Blog Index (both Featured and Regular grid)
 * and Single Post Related Articles rail.
 *
 * Args:
 *   'post_id'  => (int) post ID (defaults to current post in loop)
 *   'featured' => (bool) whether to render the large 2-column featured layout
 *   'class'    => (string) additional CSS classes
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$postId = isset($args['post_id']) ? (int) $args['post_id'] : get_the_ID();
if (!$postId) {
   return;
}

$isFeatured = !empty($args['featured']);
$extraClass = isset($args['class']) ? (string) $args['class'] : '';

$permalink = get_permalink($postId);
$title     = get_the_title($postId);
$excerpt   = get_the_excerpt($postId);
$date      = get_the_date('j F Y', $postId);

// Category
$categories = get_the_category($postId);
$primaryCat = !empty($categories) ? $categories[0] : null;
$catName    = $primaryCat ? $primaryCat->name : __('General', 'detailking');
$catSlug    = $primaryCat ? $primaryCat->slug : 'all';

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

// Thumbnail
$thumbnailId = get_post_thumbnail_id($postId);
?>

<?php if ($isFeatured) : ?>
   <article class="blog-card blog-card--featured <?= esc_attr($extraClass); ?>" data-animate="fade" data-dk-filter-cats="<?= esc_attr($catSlug); ?>">
      <div class="blog-card__media">
         <a href="<?= esc_url($permalink); ?>" class="blog-card__media-link" tabindex="-1" aria-hidden="true">
            <?php if ($thumbnailId) : ?>
               <?= wp_get_attachment_image($thumbnailId, 'large', false, [
                  'class'   => 'blog-card__img',
                  'loading' => 'eager',
                  'alt'     => esc_attr($title),
               ]); ?>
            <?php else : ?>
               <div class="blog-card__placeholder"></div>
            <?php endif; ?>
         </a>
         <span class="blog-card__badge-featured"><?= esc_html__('Featured', 'detailking'); ?></span>
      </div>

      <div class="blog-card__body">
         <div class="blog-card__meta">
            <span class="blog-card__category"><?= esc_html($catName); ?></span>
            <span class="blog-card__dot" aria-hidden="true">·</span>
            <time class="blog-card__date" datetime="<?= esc_attr(get_the_date('c', $postId)); ?>"><?= esc_html($date); ?></time>
            <span class="blog-card__dot" aria-hidden="true">·</span>
            <span class="blog-card__read-time"><?= esc_html($readingTime); ?></span>
         </div>

         <h2 class="blog-card__title">
            <a href="<?= esc_url($permalink); ?>"><?= esc_html($title); ?></a>
         </h2>

         <?php if ($excerpt !== '') : ?>
            <p class="blog-card__excerpt"><?= esc_html($excerpt); ?></p>
         <?php endif; ?>

         <a href="<?= esc_url($permalink); ?>" class="blog-card__cta">
            <?= esc_html__('Read Article', 'detailking'); ?>
            <span class="blog-card__arrow" aria-hidden="true">→</span>
         </a>
      </div>
   </article>

<?php else : ?>

   <article class="blog-card <?= esc_attr($extraClass); ?>" data-animate="fade" data-dk-filter-cats="<?= esc_attr($catSlug); ?>">
      <div class="blog-card__media">
         <a href="<?= esc_url($permalink); ?>" class="blog-card__media-link" tabindex="-1" aria-hidden="true">
            <?php if ($thumbnailId) : ?>
               <?= wp_get_attachment_image($thumbnailId, 'medium_large', false, [
                  'class'   => 'blog-card__img',
                  'loading' => 'lazy',
                  'alt'     => esc_attr($title),
               ]); ?>
            <?php else : ?>
               <div class="blog-card__placeholder"></div>
            <?php endif; ?>
         </a>
         <?php if ($catName !== '') : ?>
            <span class="blog-card__badge-cat"><?= esc_html($catName); ?></span>
         <?php endif; ?>
      </div>

      <div class="blog-card__body">
         <div class="blog-card__meta">
            <time class="blog-card__date" datetime="<?= esc_attr(get_the_date('c', $postId)); ?>"><?= esc_html($date); ?></time>
            <span class="blog-card__dot" aria-hidden="true">·</span>
            <span class="blog-card__read-time"><?= esc_html($readingTime); ?></span>
         </div>

         <h3 class="blog-card__title">
            <a href="<?= esc_url($permalink); ?>"><?= esc_html($title); ?></a>
         </h3>

         <?php if ($excerpt !== '') : ?>
            <p class="blog-card__excerpt"><?= esc_html($excerpt); ?></p>
         <?php endif; ?>

         <a href="<?= esc_url($permalink); ?>" class="blog-card__cta">
            <?= esc_html__('Read Article', 'detailking'); ?>
            <span class="blog-card__arrow" aria-hidden="true">→</span>
         </a>
      </div>
   </article>
<?php endif; ?>
