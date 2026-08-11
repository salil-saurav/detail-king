<?php

/**
 * Why Detail King. Design band y 4970.2–5795.1, node 59:2308.
 *
 * Centred heading, then three features either side of a circular photo. The left
 * column is right-aligned and the right column left-aligned, both reading inward
 * toward the image.
 *
 * The comp note says "dashed gold rings slowly rotate around the centre car" —
 * the rings are CSS, not part of the photo.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$left      = $meta->fieldRowsOr('why_features_left', $D);
$right     = $meta->fieldRowsOr('why_features_right', $D);
$badge     = (string) $meta->fieldOr('why_image_badge', $D);
$watermark = (string) $meta->fieldOr('why_watermark', $D);

$image = $meta->imageUrl(
   $meta->field('why_image'),
   get_template_directory_uri() . '/assets/images/home/why-us-car.jpg'
);

/**
 * Render one feature row.
 *
 * The icon badge is rendered unconditionally — an empty glyph value must not
 * remove a flex child, because losing it silently re-aligns the whole row.
 */
$renderFeature = static function (array $row, string $from = 'fade-left'): void {
   $glyph = (string) ($row['feature_icon_glyph'] ?? 'crown');
   ?>
   <li class="why-feature" data-animate="<?= esc_attr($from); ?>">
      <span class="why-feature__icon" aria-hidden="true">
         <?php get_template_part('template-parts/components/glyph', null, ['glyph' => $glyph]); ?>
      </span>
      <span class="why-feature__body">
         <span class="why-feature__title subheading-xs"><?= esc_html((string) ($row['feature_title'] ?? '')); ?></span>
         <span class="why-feature__text body-base"><?= esc_html((string) ($row['feature_text'] ?? '')); ?></span>
      </span>
   </li>
   <?php
};
?>
<section class="home-why section--light" data-animate="fade">
   <?php if ($watermark !== '') : ?>
      <span class="dk-watermark dk-watermark--light home-why__watermark" aria-hidden="true"><?= esc_html($watermark); ?></span>
   <?php endif; ?>

   <div class="container-dk">

      <?php
      get_template_part('template-parts/components/section-heading', null, [
         'eyebrow' => $meta->fieldOr('why_eyebrow', $D),
         'title'   => $meta->fieldOr('why_heading', $D),
         'gold'    => $meta->fieldOr('why_heading_gold', $D),
         'size'    => 'display-sm',
         'align'   => 'center',
         'rules'   => 'both',
      ]);
      ?>

      <div class="home-why__cols">

         <ul class="why-features why-features--left">
            <?php foreach ($left as $row) { $renderFeature($row, 'fade-left'); } ?>
         </ul>

         <div class="home-why__figure">
            <span class="home-why__ring" aria-hidden="true"></span>
            <span class="home-why__ring home-why__ring--inner" aria-hidden="true"></span>
            <figure class="home-why__photo" data-animate="zoom-in">
               <img src="<?= esc_url($image); ?>" alt="" loading="lazy" decoding="async">
            </figure>
            <?php if ($badge !== '') : ?>
               <span class="home-why__badge label-sm"><?= esc_html($badge); ?></span>
            <?php endif; ?>
         </div>

         <ul class="why-features why-features--right">
            <?php foreach ($right as $row) { $renderFeature($row, 'fade-right'); } ?>
         </ul>

      </div>
   </div>
</section>
