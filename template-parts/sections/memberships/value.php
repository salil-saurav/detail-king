<?php

/**
 * Memberships Rewards That Add Up section ("Why Become A Member?").
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'memberships';

$watermark = (string) $meta->fieldOr('value_watermark', $D);
$checklist = $meta->fieldRowsOr('value_checklist', $D);

?>
<section class="mship-value section--light" data-animate="fade">
   <?php if ($watermark !== '') : ?>
      <span class="dk-watermark dk-watermark--light mship-value__watermark" aria-hidden="true"><?= esc_html($watermark); ?></span>
   <?php endif; ?>

   <div class="container-dk">
      <div class="mship-value__grid">

         <div class="mship-value__left">
            <?php
            get_template_part('template-parts/components/section-heading', null, [
               'eyebrow' => $meta->fieldOr('value_eyebrow', $D),
               'title'   => $meta->fieldOr('value_title', $D),
               'gold'    => $meta->fieldOr('value_title_gold', $D),
               'size'    => 'display-sm',
               'align'   => 'left',
               'rules'   => 'none',
               'text'    => $meta->fieldOr('value_text', $D),
            ]);
            ?>
         </div>

         <div class="mship-value__right">
            <?php if (!empty($checklist)) : ?>
               <div class="mship-value__checklist">
                  <?php foreach ($checklist as $item) :
                     $text = (string) ($item['item_text'] ?? '');
                     if ($text === '') continue;
                  ?>
                     <div class="mship-value__item card-light">
                        <span class="mship-value__tick" aria-hidden="true"></span>
                        <span class="mship-value__text body-base-med"><?= esc_html($text); ?></span>
                     </div>
                  <?php endforeach; ?>
               </div>
            <?php endif; ?>
         </div>

      </div>
   </div>
</section>
