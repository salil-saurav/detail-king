<?php

/**
 * About — Our Approach. Design band y 3723.4…4931.7 (beige).
 *
 * Same two-col grid pattern as Who We Are (copy + framed photo, neutral
 * border), then a 4-step process row below. Steps repeater — survives 1..N.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta  = MetaHelper::getInstance();
$D     = 'about';
$steps = $meta->fieldRowsOr('about_steps', $D);

$image = $meta->imageUrl($meta->field('approach_image'), get_template_directory_uri() . '/assets/images/about/approach-detailing.jpg');
?>
<section class="about-approach section--beige" data-animate="fade">
   <div class="about-approach__inner">

      <div class="container-dk dk-split about-approach__grid">
         <div class="about-approach__copy">
            <?php
            get_template_part('template-parts/components/section-heading', null, [
               'eyebrow' => $meta->fieldOr('approach_eyebrow', $D),
               'title'   => $meta->fieldOr('approach_title', $D),
               'gold'    => $meta->fieldOr('approach_title_gold', $D),
               'size'    => 'display-md',
            ]);
            ?>
            <p class="body-md"><?= esc_html((string) $meta->fieldOr('approach_text_1', $D)); ?></p>
            <p class="body-md"><?= esc_html((string) $meta->fieldOr('approach_text_2', $D)); ?></p>
         </div>

         <?php
         get_template_part('template-parts/components/framed-photo', null, [
            'image'  => $image,
            'alt'    => 'Careful detailing approach',
            'border' => 'neutral',
         ]);
         ?>
      </div>

      <?php if ($steps) : ?>
         <div class="about-approach__steps">
            <?php foreach ($steps as $step) :
               $number = (string) ($step['step_number'] ?? '');
               $title  = (string) ($step['step_title'] ?? '');
               $text   = (string) ($step['step_text'] ?? '');
            ?>
               <div class="about-approach__step card-light">
                  <?php if ($number !== '') : ?>
                     <span class="dk-step__num" aria-hidden="true"><?= esc_html($number); ?></span>
                  <?php endif; ?>
                  <?php if ($title !== '') : ?>
                     <h3 class="about-approach__step-title"><?= esc_html($title); ?></h3>
                  <?php endif; ?>
                  <?php if ($text !== '') : ?>
                     <p class="about-approach__step-text body-base"><?= esc_html($text); ?></p>
                  <?php endif; ?>
               </div>
            <?php endforeach; ?>
         </div>
      <?php endif; ?>

   </div>
</section>
