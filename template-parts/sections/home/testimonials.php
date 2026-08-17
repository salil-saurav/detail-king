<?php

/**
 * Google reviews. Design band y 8724.8–9605.2, node 59:2667.
 *
 * Two render paths, in order of preference:
 *
 *  1. **Live reviews via the Trustindex plugin** (`reviews_shortcode`, a global
 *     option — client-connected, no Places API key, per TASK-BRIEF). The widget
 *     draws its own rating header ("EXCELLENT", stars, "Based on N reviews",
 *     Google mark) and its own prev/next slider, so the comp's summary card and
 *     this theme's `dk-rail` are both deliberately omitted on this path — they
 *     would restate the same numbers from a *seeded* source and contradict the
 *     live ones the moment a real review lands.
 *  2. **Fallback: the hand-seeded `dk_testimonial` rail** — the original comp
 *     build, kept intact. Renders whenever the shortcode is blank, the plugin is
 *     deactivated, or its widget is not finished being set up (in which case the
 *     plugin returns an admins-only error box, which is passed through so the
 *     problem is visible to an editor rather than silently swallowed).
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

/* Rendered here rather than inline below so the fallback can be chosen *before*
   any markup is printed. `ti-widget` is the wrapper class every Trustindex
   layout emits; its absence means the shortcode produced an error box or
   nothing at all, not a widget. */
$shortcode  = trim((string) $meta->optOr('reviews_shortcode'));
$widget     = $shortcode !== '' ? trim(do_shortcode($shortcode)) : '';
$hasWidget  = $widget !== '' && str_contains($widget, 'ti-widget');

$reviews = $hasWidget ? [] : get_posts([
   'post_type'        => 'dk_testimonial',
   'posts_per_page'   => 9,
   'orderby'          => ['menu_order' => 'ASC', 'date' => 'DESC'],
   'suppress_filters' => false,
]);

if (!$hasWidget && !$reviews) {
   /* Nothing real to show. Still surface the plugin's own admin-only error, if
      it produced one, so a half-configured widget doesn't just look like a
      missing section. */
   if ($widget !== '' && current_user_can('manage_options')) {
      echo '<section class="home-reviews section--light"><div class="container-dk">' . $widget . '</div></section>';
   }
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

         <?php if (!$hasWidget && $average !== '') : ?>
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

      <?php if ($hasWidget) : ?>
         <?php /* Already kses-filtered by the plugin's own shortcode handler. */ ?>
         <div class="home-reviews__widget"><?= $widget; ?></div>
      <?php else : ?>
         <div class="dk-rail dk-rail--reviews" data-dk-rail data-dk-autoplay="4500">
            <ul class="dk-rail__track">
               <?php foreach ($reviews as $review) : ?>
                  <li class="dk-rail__item">
                     <?php get_template_part('template-parts/components/review-card', null, ['review' => $review]); ?>
                  </li>
               <?php endforeach; ?>
            </ul>
         </div>
      <?php endif; ?>

      <?php /* See the note in shop.php — arrows track overflow, not item count. */ ?>
      <?php if (!$hasWidget && count($reviews) > 1) : ?>
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
