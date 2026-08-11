<?php

/**
 * Google review card.
 *
 *   get_template_part('template-parts/components/review-card', null, ['review' => $post]);
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$review = $args['review'] ?? null;

if (!$review instanceof WP_Post) {
   return;
}

$meta = MetaHelper::getInstance();
$id   = $review->ID;

$text     = (string) $meta->field('review_text', $id, '');
$name     = (string) $meta->field('reviewer_name', $id, get_the_title($review));
$vehicle  = (string) $meta->field('reviewer_vehicle', $id, '');
$when     = (string) $meta->field('review_date', $id, '');
$rating   = (int) $meta->field('review_rating', $id, 5);
$initials = (string) $meta->field('reviewer_initials', $id, '');

/**
 * Derive initials only as a fallback. mb_* so a multi-byte first letter is not
 * chopped in half, which is what substr() would do.
 */
if ($initials === '' && $name !== '') {
   $parts = preg_split('/\s+/', trim($name)) ?: [];
   foreach (array_slice($parts, 0, 2) as $part) {
      $initials .= mb_strtoupper(mb_substr($part, 0, 1));
   }
}

/* The comp joins vehicle and date with a middle dot; either may be absent. */
$sub = implode(' · ', array_filter([$vehicle, $when], 'strlen'));
?>
<article class="review-card">

   <header class="review-card__top">
      <?php get_template_part('template-parts/components/stars', null, ['rating' => $rating]); ?>
      <span class="review-card__source" aria-hidden="true">
         <?php get_template_part('template-parts/components/google-mark'); ?>
      </span>
   </header>

   <?php if ($text !== '') : ?>
      <blockquote class="review-card__text review-text body-base">
         <?= esc_html(sprintf('“%s”', $text)); ?>
      </blockquote>
   <?php endif; ?>

   <footer class="review-card__foot">
      <span class="review-card__avatar body-base-semi" aria-hidden="true"><?= esc_html($initials); ?></span>
      <span class="review-card__who">
         <span class="review-card__name body-base-med"><?= esc_html($name); ?></span>
         <?php if ($sub !== '') : ?>
            <span class="review-card__meta body-sm"><?= esc_html($sub); ?></span>
         <?php endif; ?>
      </span>
   </footer>

</article>
