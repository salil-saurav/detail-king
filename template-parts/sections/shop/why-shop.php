<?php

/**
 * Shop landing "Why Shop" band — dark section, giant watermark behind a
 * 4-card feature grid. Page-level copy (doesn't repeat on any other frame),
 * so it's a PageMeta_Shop field, not a global option.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta   = MetaHelper::getInstance();
$D      = 'shop';
$pageId = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;

$features  = $meta->fieldRowsOr('why_features', $D, $pageId);
$watermark = (string) $meta->fieldOr('why_watermark', $D, $pageId);
?>
<section class="shop-why section--dark" data-animate="fade">
   <?php if ($watermark !== '') : ?>
      <span class="dk-watermark dk-watermark--dark shop-why__watermark" aria-hidden="true"><?= esc_html($watermark); ?></span>
   <?php endif; ?>

   <div class="container-dk">
      <?php
      get_template_part('template-parts/components/section-heading', null, [
         'eyebrow' => $meta->fieldOr('why_eyebrow', $D, $pageId),
         'title'   => $meta->fieldOr('why_heading', $D, $pageId),
         'gold'    => $meta->fieldOr('why_heading_gold', $D, $pageId),
         'text'    => $meta->fieldOr('why_text', $D, $pageId),
         'size'    => 'display-md',
         'eyebrow_animate' => 'fade-up',
         'animate' => 'fade-up',
         'text_animate' => 'fade-up',
      ]);
      ?>

      <ul class="shop-why__grid">
         <?php foreach ($features as $row) : ?>
            <?php $glyph = (string) ($row['feature_icon_glyph'] ?? 'shield'); ?>
            <li class="shop-why__item" data-animate>
               <span class="shop-why__icon" aria-hidden="true">
                  <?php get_template_part('template-parts/components/glyph', null, ['glyph' => $glyph]); ?>
               </span>
               <h3 class="shop-why__title"><?= esc_html((string) ($row['feature_title'] ?? '')); ?></h3>
               <p class="shop-why__text"><?= esc_html((string) ($row['feature_text'] ?? '')); ?></p>
            </li>
         <?php endforeach; ?>
      </ul>
   </div>
</section>
