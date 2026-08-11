<?php

/**
 * About — Our Story. Design band y 1695…2789.6 (dark).
 *
 * Mirrored grid vs Who We Are: framed photo LEFT (gold-tinted border), copy
 * RIGHT. Giant "EST. 2016" watermark bottom-left, gold glow top-right.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'about';

$image     = $meta->imageUrl($meta->field('story_image'), get_template_directory_uri() . '/assets/images/about/our-story-foam-wash.jpg');
$watermark = (string) $meta->fieldOr('story_watermark', $D);
?>
<section class="about-story section--dark glow-gold" data-animate="fade">
   <?php if ($watermark !== '') : ?>
      <span class="dk-watermark dk-watermark--dark about-story__watermark" aria-hidden="true"><?= esc_html($watermark); ?></span>
   <?php endif; ?>

   <div class="container-dk dk-split about-story__grid">

      <?php
      get_template_part('template-parts/components/framed-photo', null, [
         'image'  => $image,
         'alt'    => 'Foam wash detailing',
         'border' => 'gold',
      ]);
      ?>

      <div class="about-story__copy">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => $meta->fieldOr('story_eyebrow', $D),
            'title'   => $meta->fieldOr('story_title', $D),
            'gold'    => $meta->fieldOr('story_title_gold', $D),
            'size'    => 'display-md',
            // Comp forces a break here — the colour boundary IS the line break
            // ("A Sophisticated," / "Professional Approach"), not a reflow.
            'break'   => true,
         ]);
         ?>
         <p class="body-md"><?= esc_html((string) $meta->fieldOr('story_text_1', $D)); ?></p>
         <p class="body-md"><?= esc_html((string) $meta->fieldOr('story_text_2', $D)); ?></p>
         <p class="body-md"><?= esc_html((string) $meta->fieldOr('story_text_3', $D)); ?></p>
      </div>

   </div>
</section>
