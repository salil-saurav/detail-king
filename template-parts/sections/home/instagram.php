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
         'eyebrow_animate' => 'fade-up',
         'animate' => 'fade-up',
      ]);
      ?>

      <?php if ($ctaText !== '') : ?>
         <a class="btn-outline-light-dk btn-arrow home-instagram__cta" data-animate="fade-up"
            href="<?= esc_url($ctaUrl); ?>" target="_blank" rel="noopener noreferrer">
            <?= esc_html($ctaText); ?>
         </a>
      <?php endif; ?>
   </div>

   <?php if ($images) : ?>
      <ul class="home-instagram__row" data-hscroll style="--dk-ig-count:<?= esc_attr((string) count($images)); ?>">
         <?php foreach ($images as $row) :
            $url = $meta->imageUrl($row['ig_image'] ?? null, '');
            if ($url === '') {
               continue;
            }
            /* Per-tile permalink when the editor has one (and the real feed
               integration will fill these in — see TASK-BRIEF §1.3); until then
               the tile still goes somewhere sensible, the profile the section's
               own CTA points at. Without this every tile renders as a bare image
               and the recording's "View Post" hover has nothing to hang on. */
            $link = (string) ($row['ig_url'] ?? '');
            if ($link === '') {
               $link = $ctaUrl;
            }
         ?>
            <li class="home-instagram__cell" data-animate="zoom">
               <?php if ($link !== '') : ?>
                  <?php
                  /* Hovering a tile in the reference recording washes it gold and
                     labels it "View Post" (t 58-61, on whichever tile the pointer
                     is over). The label doubles as the link's accessible name,
                     which the bare image — alt="" by design, it is decorative
                     feed art — did not previously give it. */
                  ?>
                  <a class="home-instagram__link" href="<?= esc_url($link); ?>" target="_blank" rel="noopener noreferrer">
                     <img src="<?= esc_url($url); ?>" alt="" loading="lazy" decoding="async">
                     <span class="home-instagram__overlay">
                        <?= esc_html__('View Post', 'detailking'); ?>
                        <span aria-hidden="true">&#8599;</span>
                     </span>
                  </a>
               <?php else : ?>
                  <img src="<?= esc_url($url); ?>" alt="" loading="lazy" decoding="async">
               <?php endif; ?>
            </li>
         <?php endforeach; ?>
      </ul>
   <?php endif; ?>

</section>
