<?php

/**
 * Memberships Loyalty Rewards section ("More Than Just Car Care").
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'memberships';

$items = $meta->fieldRowsOr('loyalty_items', $D);

?>
<section class="mship-loyalty section--dark" data-animate="fade">
   <div class="container-dk">

      <?php
      get_template_part('template-parts/components/section-heading', null, [
         'eyebrow' => $meta->fieldOr('loyalty_eyebrow', $D),
         'title'   => $meta->fieldOr('loyalty_title', $D),
         'gold'    => $meta->fieldOr('loyalty_title_gold', $D),
         'size'    => 'display-sm',
         'align'   => 'center',
         'rules'   => 'both',
         'text'    => $meta->fieldOr('loyalty_text', $D),
         'eyebrow_animate' => 'fade-up',
         'animate' => 'fade-up',
         'text_animate' => 'fade-up',
      ]);
      ?>

      <?php if (!empty($items)) : ?>
         <div class="mship-loyalty__grid">
            <?php foreach ($items as $item) :
               $icon  = (string) ($item['loyalty_icon'] ?? 'crown');
               $title = (string) ($item['loyalty_title'] ?? '');
               $text  = (string) ($item['loyalty_text'] ?? '');
            ?>
               <article class="mship-loyalty__card card-glass" data-animate>
                  <div class="mship-loyalty__icon-box">
                     <?php get_template_part('template-parts/components/glyph', null, ['glyph' => $icon]); ?>
                  </div>
                  <h3 class="mship-loyalty__card-title subheading-md"><?= esc_html($title); ?></h3>
                  <p class="mship-loyalty__card-text body-sm"><?= esc_html($text); ?></p>
               </article>
            <?php endforeach; ?>
         </div>
      <?php endif; ?>

   </div>
</section>
