<?php

/**
 * About — Equipment / What We Bring. Design band y 2789.6…3723.4 (light).
 *
 * Centred heading, "THE CRAFT" light watermark, 4-card equal-width row.
 * Repeater — survives 1..N (flex: 1 0 0 on each card, no fixed column count).
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta  = MetaHelper::getInstance();
$D     = 'about';
$cards = $meta->fieldRowsOr('about_equipment', $D);

$watermark = (string) $meta->fieldOr('equip_watermark', $D);
?>
<section class="about-equip section--light" data-animate="fade">
   <?php if ($watermark !== '') : ?>
      <span class="dk-watermark dk-watermark--light about-equip__watermark" aria-hidden="true"><?= esc_html($watermark); ?></span>
   <?php endif; ?>

   <div class="about-equip__inner">

      <?php
      get_template_part('template-parts/components/section-heading', null, [
         'eyebrow' => $meta->fieldOr('equip_eyebrow', $D),
         'title'   => $meta->fieldOr('equip_title', $D),
         'gold'    => $meta->fieldOr('equip_title_gold', $D),
         'text'    => $meta->fieldOr('equip_text', $D),
         'size'    => 'display-md',
         'align'   => 'center',
         'rules'   => 'both',
      ]);
      ?>

      <?php if ($cards) : ?>
         <div class="about-equip__row">
            <?php foreach ($cards as $card) :
               $glyph = (string) ($card['equip_glyph'] ?? 'gear');
               $title = (string) ($card['equip_title'] ?? '');
               $text  = (string) ($card['equip_text'] ?? '');
            ?>
               <div class="about-equip__card card-light">
                  <span class="about-equip__icon" aria-hidden="true">
                     <?php get_template_part('template-parts/components/glyph', null, ['glyph' => $glyph]); ?>
                  </span>
                  <?php if ($title !== '') : ?>
                     <h3 class="about-equip__card-title"><?= esc_html($title); ?></h3>
                  <?php endif; ?>
                  <?php if ($text !== '') : ?>
                     <p class="about-equip__card-text body-base"><?= esc_html($text); ?></p>
                  <?php endif; ?>
               </div>
            <?php endforeach; ?>
         </div>
      <?php endif; ?>

   </div>
</section>
