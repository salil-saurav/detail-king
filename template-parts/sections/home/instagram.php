<?php

/**
 * Instagram strip. Design band y 9605.2–10215.1, node 59:2775.
 *
 * Comp note: "Full-bleed edge-to-edge feed (no container)". The heading block keeps
 * the 1470px content box; the image row deliberately breaks out to the viewport.
 *
 * The row divides evenly at any count, so an editor adding a seventh image gets
 * seven equal columns rather than a hole.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$images  = $meta->fieldRowsOr('instagram_images', $D);
$ctaText = (string) $meta->fieldOr('instagram_cta_text', $D);
$ctaUrl  = (string) $meta->fieldOr('instagram_cta_url', $D);
$handle  = (string) $meta->fieldOr('instagram_eyebrow', $D);
?>
<section class="home-instagram section--dark" data-animate="fade">

   <?php if ($handle !== '') : ?>
      <span class="dk-watermark dk-watermark--dark home-instagram__watermark" aria-hidden="true"><?= esc_html($handle); ?></span>
   <?php endif; ?>

   <div class="container-dk home-instagram__head">
      <?php
      get_template_part('template-parts/components/section-heading', null, [
         'eyebrow' => $handle,
         'title'   => $meta->fieldOr('instagram_heading', $D),
         'gold'    => $meta->fieldOr('instagram_heading_gold', $D),
         'size'    => 'display-sm',
      ]);
      ?>

      <?php if ($ctaText !== '') : ?>
         <a class="btn-outline-light-dk btn-arrow home-instagram__cta"
            href="<?= esc_url($ctaUrl); ?>" target="_blank" rel="noopener noreferrer">
            <?= esc_html($ctaText); ?>
         </a>
      <?php endif; ?>
   </div>

   <?php if ($images) : ?>
      <ul class="home-instagram__row" style="--dk-ig-count:<?= esc_attr((string) count($images)); ?>">
         <?php foreach ($images as $row) :
            $url = $meta->imageUrl($row['ig_image'] ?? null, '');
            if ($url === '') {
               continue;
            }
            $link = (string) ($row['ig_url'] ?? '');
            ?>
            <li class="home-instagram__cell">
               <?php if ($link !== '') : ?>
                  <a href="<?= esc_url($link); ?>" target="_blank" rel="noopener noreferrer">
                     <img src="<?= esc_url($url); ?>" alt="" loading="lazy" decoding="async">
                  </a>
               <?php else : ?>
                  <img src="<?= esc_url($url); ?>" alt="" loading="lazy" decoding="async">
               <?php endif; ?>
            </li>
         <?php endforeach; ?>
      </ul>
   <?php endif; ?>

</section>
