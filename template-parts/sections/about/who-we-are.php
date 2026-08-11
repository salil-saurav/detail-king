<?php

/**
 * About — Who We Are. Design band y 833…1695 (light).
 *
 * Two-col grid: copy left, framed photo + "Since 2016" glass badge right.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'about';

$image = $meta->imageUrl($meta->field('who_image'), get_template_directory_uri() . '/assets/images/about/who-we-are-car.jpg');
?>
<section class="about-who section--light" data-animate="fade">
   <div class="container-dk dk-split about-who__grid">

      <div class="about-who__copy">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => $meta->fieldOr('who_eyebrow', $D),
            'title'   => $meta->fieldOr('who_title', $D),
            'gold'    => $meta->fieldOr('who_title_gold', $D),
            // 40px measured off the export — no utility class lands exactly
            // there, see about.css's .about-who__copy override.
            'size'    => 'heading-xxs',
         ]);
         ?>
         <p class="body-md"><?= esc_html((string) $meta->fieldOr('who_text_1', $D)); ?></p>
         <p class="body-md"><?= esc_html((string) $meta->fieldOr('who_text_2', $D)); ?></p>
      </div>

      <?php
      get_template_part('template-parts/components/framed-photo', null, [
         'image'      => $image,
         'alt'        => 'Detailed luxury car',
         'border'     => 'neutral',
         'badge_year' => $meta->fieldOr('who_badge_year', $D),
         'badge_text' => $meta->fieldOr('who_badge_text', $D),
      ]);
      ?>

   </div>
</section>
