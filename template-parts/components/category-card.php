<?php

/**
 * Product category tile — the Shop landing page's category grid.
 *
 *   get_template_part('template-parts/components/category-card', null, ['term' => $term]);
 *
 * Image and description are WooCommerce/WordPress-native (category thumbnail,
 * term Description) so an editor manages them from the existing product_cat
 * screen — only the small eyebrow tag ("9H · LIGHT · SPORT") is a bespoke ACF
 * field (TermMeta_ProductCategory), because neither taxonomy has a native
 * equivalent for it.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$term = $args['term'] ?? null;

if (!$term instanceof WP_Term) {
   return;
}

$meta    = MetaHelper::getInstance();
$tagline = (string) $meta->field('category_tagline', $term, '');
$link    = get_term_link($term);
$link    = is_wp_error($link) ? '#' : $link;

$thumbId = get_term_meta($term->term_id, 'thumbnail_id', true);
$thumb   = $thumbId ? wp_get_attachment_image_url((int) $thumbId, 'medium_large') : '';

$count = (int) $term->count;
?>
<article class="cat-card" data-animate="fade">

   <a class="cat-card__media" href="<?= esc_url($link); ?>">
      <?php if ($thumb) : ?>
         <img src="<?= esc_url($thumb); ?>" alt="<?= esc_attr($term->name); ?>" loading="lazy" decoding="async">
      <?php endif; ?>

      <span class="cat-card__count">
         <?php
         /* translators: %d: number of products in this category. */
         printf(esc_html(_n('%d product', '%d products', $count, 'detailking')), $count);
         ?>
      </span>
   </a>

   <div class="cat-card__body">
      <?php if ($tagline !== '') : ?>
         <span class="cat-card__eyebrow"><?= esc_html($tagline); ?></span>
      <?php endif; ?>

      <h3 class="cat-card__title"><?= esc_html($term->name); ?></h3>

      <?php if ($term->description !== '') : ?>
         <p class="cat-card__text"><?= esc_html($term->description); ?></p>
      <?php endif; ?>

      <a class="cat-card__link" href="<?= esc_url($link); ?>">
         <?php esc_html_e('Shop Category', 'detailking'); ?> <span aria-hidden="true">&rarr;</span>
      </a>
   </div>

</article>
