<?php

/**
 * Google reviews. Design band y 8724.8–9605.2, node 59:2667.
 *
 * Heading left, a Google summary card right, then three review cards in a rail.
 * Comp note: "Review slider: auto-advances every 4.5s, loops back to start".
 *
 * The average and review count are global options — the same figures appear on the
 * service pages, so they are not duplicated per page.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$reviews = get_posts([
   'post_type'        => 'dk_testimonial',
   'posts_per_page'   => 9,
   'orderby'          => ['menu_order' => 'ASC', 'date' => 'DESC'],
   'suppress_filters' => false,
]);

if (!$reviews) {
   return;
}

$average   = (string) $meta->optOr('reviews_average');
$cardTitle = (string) $meta->fieldOr('reviews_card_title', $D);
$cardNote  = (string) $meta->fieldOr('reviews_card_note', $D);
?>
<section class="home-reviews section--light" data-animate="fade">
   <div class="container-dk">

      <div class="home-reviews__head">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => $meta->fieldOr('reviews_eyebrow', $D),
            'title'   => $meta->fieldOr('reviews_heading', $D),
            'gold'    => $meta->fieldOr('reviews_heading_gold', $D),
            'size'    => 'display-md',
         ]);
         ?>

         <?php if ($average !== '') : ?>
            <div class="google-summary">
               <div class="google-summary__brand">
                  <?php get_template_part('template-parts/components/google-mark'); ?>
                  <span class="body-base-med"><?= esc_html($cardTitle); ?></span>
               </div>
               <div class="google-summary__score">
                  <span class="google-summary__value heading-sm"><?= esc_html($average); ?></span>
                  <?php get_template_part('template-parts/components/stars', null, ['rating' => 5]); ?>
                  <?php if ($cardNote !== '') : ?>
                     <span class="google-summary__note body-sm"><?= esc_html($cardNote); ?></span>
                  <?php endif; ?>
               </div>
            </div>
         <?php endif; ?>
      </div>

      <div class="dk-rail dk-rail--reviews" data-dk-rail data-dk-autoplay="4500">
         <ul class="dk-rail__track">
            <?php foreach ($reviews as $review) : ?>
               <li class="dk-rail__item">
                  <?php get_template_part('template-parts/components/review-card', null, ['review' => $review]); ?>
               </li>
            <?php endforeach; ?>
         </ul>
      </div>

      <?php /* See the note in shop.php — arrows track overflow, not item count. */ ?>
      <?php if (count($reviews) > 1) : ?>
         <div class="dk-rail__nav">
            <button class="dk-rail__btn" type="button" data-dk-rail-prev aria-label="<?php esc_attr_e('Previous reviews', 'detailking'); ?>">
               <span aria-hidden="true">&larr;</span>
            </button>
            <button class="dk-rail__btn" type="button" data-dk-rail-next aria-label="<?php esc_attr_e('Next reviews', 'detailking'); ?>">
               <span aria-hidden="true">&rarr;</span>
            </button>
         </div>
      <?php endif; ?>

   </div>
</section>
