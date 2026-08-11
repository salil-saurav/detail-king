<?php

/**
 * FAQ. Design band y 11267.5–12303.1, node 59:2946.
 *
 * Image panel left with a dark overlay card, accordion right. FAQs come from the
 * dk_faq CPT filtered by the faq_group taxonomy, so the same answer can appear on
 * more than one page without being copied.
 *
 * The first item is open in the comp, so it is open here — implemented with <details>
 * so it works before JavaScript runs and stays keyboard-accessible for free.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$faqs = get_posts([
   'post_type'        => 'dk_faq',
   'posts_per_page'   => 12,
   'orderby'          => ['menu_order' => 'ASC', 'date' => 'ASC'],
   'suppress_filters' => false,
   'tax_query'        => [[
      'taxonomy' => 'faq_group',
      'field'    => 'slug',
      'terms'    => 'homepage',
      // An FAQ with no group set still shows, so a new entry is not invisible
      // until someone remembers to tick a box.
      'operator' => 'IN',
   ]],
]);

if (!$faqs) {
   // Fall back to ungrouped FAQs rather than rendering an empty accordion.
   $faqs = get_posts([
      'post_type'        => 'dk_faq',
      'posts_per_page'   => 12,
      'orderby'          => ['menu_order' => 'ASC', 'date' => 'ASC'],
      'suppress_filters' => false,
   ]);
}

if (!$faqs) {
   return;
}

$watermark = (string) $meta->fieldOr('faq_watermark', $D);
$panelLead = (string) $meta->fieldOr('faq_panel_lead', $D);
$panelText = (string) $meta->fieldOr('faq_panel_text', $D);

$panelImage = $meta->imageUrl(
   $meta->field('faq_image'),
   get_template_directory_uri() . '/assets/images/home/faq-studio.jpg'
);
?>
<section class="home-faq section--light" data-animate="fade">
   <?php if ($watermark !== '') : ?>
      <span class="dk-watermark dk-watermark--light home-faq__watermark" aria-hidden="true"><?= esc_html($watermark); ?></span>
   <?php endif; ?>

   <div class="container-dk home-faq__inner">

      <div class="home-faq__panel">
         <?php if ($panelImage !== '') : ?>
            <img class="home-faq__image" src="<?= esc_url($panelImage); ?>" alt="" loading="lazy" decoding="async">
         <?php endif; ?>

         <?php if ($panelLead !== '' || $panelText !== '') : ?>
            <div class="home-faq__overlay">
               <p class="body-base">
                  <?php if ($panelLead !== '') : ?>
                     <strong class="text-gold"><?= esc_html($panelLead); ?></strong>
                  <?php endif; ?>
                  <?= esc_html($panelText); ?>
               </p>
            </div>
         <?php endif; ?>
      </div>

      <div class="home-faq__body">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => $meta->fieldOr('faq_eyebrow', $D),
            'title'   => $meta->fieldOr('faq_heading', $D),
            'gold'    => $meta->fieldOr('faq_heading_gold', $D),
            'size'    => 'display-sm',
         ]);
         ?>

         <div class="dk-accordion" data-dk-accordion>
            <?php foreach ($faqs as $i => $faq) : ?>
               <details class="dk-accordion__item"<?= $i === 0 ? ' open' : ''; ?>>
                  <summary class="dk-accordion__summary">
                     <span class="dk-accordion__q body-base-med"><?= esc_html(get_the_title($faq)); ?></span>
                     <span class="dk-accordion__toggle" aria-hidden="true"></span>
                  </summary>
                  <div class="dk-accordion__answer faq-answer body-base">
                     <?= wp_kses_post((string) $meta->field('faq_answer', $faq->ID, '')); ?>
                  </div>
               </details>
            <?php endforeach; ?>
         </div>
      </div>

   </div>
</section>
