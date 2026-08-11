<?php

/**
 * Single service — Intro ("Why X?"). Design band y 721…1439 (light), pad
 * 110px/225px. Same shape as About's Who We Are / Our Approach: `.dk-split` +
 * `section-heading` (eyebrow/title only) + two plain paragraphs (lead, then
 * body — not section-heading's single `text` arg) + `framed-photo` + an
 * optional background watermark word.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();

$lead      = (string) $meta->field('intro_lead');
$text      = (string) $meta->field('intro_text');
$watermark = (string) $meta->field('intro_watermark');

$image = $meta->imageUrl(
   $meta->field('intro_image'),
   get_the_post_thumbnail_url(get_the_ID(), 'large') ?: ''
);
?>
<section class="service-intro section--light" data-animate="fade">
   <?php if ($watermark !== '') : ?>
      <span class="dk-watermark dk-watermark--light service-intro__watermark" aria-hidden="true"><?= esc_html($watermark); ?></span>
   <?php endif; ?>

   <div class="container-dk dk-split service-intro__grid">
      <div class="service-intro__copy">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => (string) $meta->field('intro_eyebrow'),
            'title'   => (string) $meta->field('intro_title'),
            'gold'    => (string) $meta->field('intro_title_gold'),
            'size'    => 'heading-lg',
         ]);
         ?>
         <?php if ($lead !== '') : ?>
            <p class="service-intro__lead"><?= esc_html($lead); ?></p>
         <?php endif; ?>
         <?php if ($text !== '') : ?>
            <p class="body-base"><?= esc_html($text); ?></p>
         <?php endif; ?>
      </div>

      <?php
      get_template_part('template-parts/components/framed-photo', null, [
         'image'  => $image,
         'alt'    => get_the_title(),
         'border' => 'neutral',
      ]);
      ?>
   </div>
</section>
