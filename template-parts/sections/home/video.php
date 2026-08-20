<?php

/**
 * Studio video + craft stats. Design band y 3643.2–4970.2, node 59:2263.
 *
 * A rounded gold-tinted video card with corner badges, a centred play button and
 * a caption, then a 4-up stat row with vertical rules over an outlined watermark.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$thumb = $meta->imageUrl(
   $meta->field('video_thumbnail'),
   get_template_directory_uri() . '/assets/images/home/video-thumbnail.jpg'
);

$videoUrl  = (string) $meta->fieldOr('video_url', $D);
$badge     = (string) $meta->fieldOr('video_badge', $D);
$metaText  = (string) $meta->fieldOr('video_meta', $D);
$caption   = (string) $meta->fieldOr('video_caption', $D);
$watermark = (string) $meta->fieldOr('video_watermark', $D);
$stats     = $meta->fieldRowsOr('craft_stats', $D);
?>
<section class="home-video section--dark" data-animate="fade">
   <div class="container-dk">

      <?php
      get_template_part('template-parts/components/section-heading', null, [
         'eyebrow' => $meta->fieldOr('video_eyebrow', $D),
         'title'   => $meta->fieldOr('video_heading', $D),
         'gold'    => $meta->fieldOr('video_heading_gold', $D),
         // 88px: the comp's cap height is 64, and the two lines sit on an 89px pitch.
         'size'    => 'display-md',
         'block_animate' => 'fade-up',
      ]);
      ?>

      <div class="home-video__frame" data-animate="fade-up">
         <figure class="home-video__media">
            <img src="<?= esc_url($thumb); ?>" alt="" loading="lazy" decoding="async">
         </figure>

         <?php if ($badge !== '') : ?>
            <span class="home-video__badge eyebrow"><?= esc_html($badge); ?></span>
         <?php endif; ?>

         <?php if ($metaText !== '') : ?>
            <span class="home-video__meta eyebrow"><?= esc_html($metaText); ?></span>
         <?php endif; ?>

         <?php if ($videoUrl !== '') : ?>
            <a class="home-video__play" href="<?= esc_url($videoUrl); ?>" data-dk-lightbox
               aria-label="<?php esc_attr_e('Play the studio film', 'detailking'); ?>">
               <span aria-hidden="true"></span>
            </a>
         <?php else : ?>
            <?php /* No URL: keep the button as decoration rather than link to nowhere. */ ?>
            <span class="home-video__play home-video__play--static" aria-hidden="true"><span></span></span>
         <?php endif; ?>

         <?php if ($caption !== '') : ?>
            <figcaption class="home-video__caption eyebrow"><?= esc_html($caption); ?></figcaption>
         <?php endif; ?>
      </div>

      <?php if ($stats) : ?>
         <div class="home-video__statswrap">
            <?php if ($watermark !== '') : ?>
               <span class="dk-watermark dk-watermark--dark" aria-hidden="true"><?= esc_html($watermark); ?></span>
            <?php endif; ?>

            <ul class="dk-stats dk-stats--craft">
               <?php foreach ($stats as $row) : ?>
                  <li class="dk-stats__item" data-animate="fade-up">
                     <span class="dk-stats__value heading-lg text-gold-gradient" data-count-to="<?= esc_attr((string) ($row['stat_value'] ?? '')); ?>">
                        <?= esc_html((string) ($row['stat_value'] ?? '')); ?>
                     </span>
                     <span class="dk-stats__label body-sm"><?= esc_html((string) ($row['stat_label'] ?? '')); ?></span>
                  </li>
               <?php endforeach; ?>
            </ul>
         </div>
      <?php endif; ?>

   </div>
</section>
