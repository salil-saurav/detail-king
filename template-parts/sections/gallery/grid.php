<?php

/**
 * Gallery Masonry Grid & Filter section.
 * Design band y 616…2568 (Figma node 179:6054).
 *
 * Renders the filter pill bar in `hide` mode and the multi-column masonry grid
 * of `dk_gallery` items. `data-animate` is placed on each gallery card, never
 * on the grid wrapper, to guarantee scroll-reveal reliability at all viewport heights.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'gallery';

// 1. Prepare filter pills
$allLabel = (string) $meta->fieldOr('filter_all_label', $D, 'All Work');
$emptyText = (string) $meta->fieldOr('empty_text', $D, 'No gallery items found in this category.');

$filterItems = [
   ['slug' => 'all', 'label' => $allLabel ?: __('All Work', 'detailking')],
];

// Canonical display order for categories
$preferredOrder = [
   'detailing'       => __('Detailing', 'detailking'),
   'ceramic-pro'     => __('Ceramic Pro', 'detailking'),
   'grooming'        => __('Grooming', 'detailking'),
   'tinting'         => __('Window Tinting', 'detailking'),
   'interior'        => __('Interior', 'detailking'),
   'vinyl-wraps'     => __('Vinyl Wrap', 'detailking'),
   'ppf'             => __('PPF', 'detailking'),
   'add-ons'         => __('Add-Ons', 'detailking'),
];

$existingTerms = get_terms([
   'taxonomy'   => 'service_category',
   'hide_empty' => false,
]);

$termsBySlug = [];
if (!is_wp_error($existingTerms) && !empty($existingTerms)) {
   foreach ($existingTerms as $term) {
      $termsBySlug[$term->slug] = $term;
   }
}

// Add categories in preferred order
foreach ($preferredOrder as $slug => $fallbackLabel) {
   if (isset($termsBySlug[$slug])) {
      $label = $fallbackLabel;
      $filterItems[] = ['slug' => $slug, 'label' => $label];
      unset($termsBySlug[$slug]);
   }
}

// Append any remaining terms
foreach ($termsBySlug as $slug => $term) {
   $filterItems[] = ['slug' => $slug, 'label' => $term->name];
}

// 2. Fetch gallery items
$items = get_posts([
   'post_type'        => 'dk_gallery',
   'posts_per_page'   => -1,
   'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
   'suppress_filters' => false,
]);
?>
<section class="gallery-section" id="gallery-wall">
   <div class="container-dk">
      <div class="gallery-filter-wrap">
         <?php
         get_template_part('template-parts/components/filter-tabs', null, [
            'label'  => '',
            'items'  => $filterItems,
            'active' => 'all',
            'target' => '#gallery-grid',
            'mode'   => 'hide',
         ]);
         ?>
      </div>

      <div class="gallery-grid" id="gallery-grid">
         <?php if (!empty($items)) : ?>
            <?php foreach ($items as $item) :
               $terms = get_the_terms($item->ID, 'service_category');
               $catSlugs = (!is_wp_error($terms) && !empty($terms))
                  ? array_map(static fn($t) => $t->slug, $terms)
                  : ['all'];

               $eyebrow = (string) $meta->field('gallery_eyebrow', $item->ID);
               if ($eyebrow === '') {
                  $eyebrow = 'DETAIL KING';
               }

               $caption = (string) $meta->field('gallery_caption', $item->ID);
               $linkUrl = (string) $meta->field('gallery_link_url', $item->ID);
               if ($linkUrl === '') {
                  $linkUrl = '#';
               }

               $thumbId = get_post_thumbnail_id($item->ID);
               $title   = get_the_title($item);
               ?>
               <article class="gallery-card" data-dk-filter-cats="<?= esc_attr(implode(' ', $catSlugs)); ?>" data-animate="fade">
                  <a href="<?= esc_url($linkUrl); ?>" class="gallery-card__link" aria-label="<?= esc_attr($title); ?>">
                     <div class="gallery-card__media">
                        <?php if ($thumbId) : ?>
                           <?= wp_get_attachment_image($thumbId, 'large', false, [
                              'class'   => 'gallery-card__img',
                              'loading' => 'lazy',
                              'alt'     => esc_attr($title),
                           ]); ?>
                        <?php else : ?>
                           <img src="<?= esc_url(get_template_directory_uri() . '/assets/images/home/hero-bg.jpg'); ?>" class="gallery-card__img" alt="<?= esc_attr($title); ?>" loading="lazy">
                        <?php endif; ?>
                     </div>
                     <div class="gallery-card__overlay">
                        <span class="gallery-card__eyebrow"><?= esc_html($eyebrow); ?></span>
                        <h3 class="gallery-card__title"><?= esc_html($title); ?></h3>
                        <?php if ($caption !== '') : ?>
                           <p class="gallery-card__caption"><?= esc_html($caption); ?></p>
                        <?php endif; ?>
                     </div>
                  </a>
               </article>
            <?php endforeach; ?>
         <?php endif; ?>

         <div class="gallery-empty" role="status" aria-live="polite">
            <p class="gallery-empty__text"><?= esc_html($emptyText); ?></p>
         </div>
      </div>
   </div>
</section>
