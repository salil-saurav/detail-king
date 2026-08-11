<?php

/**
 * Services grid. Design band y 1155.6–2953, node 59:2127.
 *
 * A 3-column grid of 474px tracks with 24px gaps (3x474 + 2x24 = 1470, the
 * content box). Two cards span two columns: the first and the last. Cards come
 * from the dk_service CPT; the final card is a *promo* linking to the package
 * builder, which is why it is not modelled as an eighth service.
 *
 * The comp shows six services + the promo. The query is not capped at six — if an
 * editor publishes a seventh service the grid simply gains a row, and the
 * 2-column spans are assigned by position so the rhythm survives any count.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$linkLabel  = (string) $meta->fieldOr('services_card_link_text', $D);
$promoTitle = (string) $meta->fieldOr('services_promo_title', $D);

$services = get_posts([
   'post_type'        => 'dk_service',
   'posts_per_page'   => 6,
   'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
   'suppress_filters' => false,
]);

$fallbackImg = get_template_directory_uri() . '/assets/images/home/';

/**
 * Which grid positions span two columns.
 *
 * The comp's rhythm is wide / narrow — narrow narrow narrow — narrow / wide, i.e.
 * cards 1 and 7 are double-width. Derived from position rather than hard-coded per
 * card so the layout does not break at another count.
 */
$total = count($services) + ($promoTitle !== '' ? 1 : 0);
$wide  = [0, $total - 1];

/**
 * The card label. The comp's cards read "Detailing" where the page heading reads
 * "Auto Detailing", so the short name wins here and the full title is the fallback.
 */
$shortName = static function (WP_Post $service) use ($meta): string {
   $short = (string) $meta->field('service_short_name', $service->ID, '');

   return $short !== '' ? $short : (string) get_the_title($service);
};
?>
<section class="home-services section--light" data-animate="fade">
   <div class="container-dk">

      <div class="home-services__head">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => $meta->fieldOr('services_eyebrow', $D),
            'title'   => $meta->fieldOr('services_heading', $D),
            'gold'    => $meta->fieldOr('services_heading_gold', $D),
            'size'    => 'display-md',
         ]);
         ?>

         <?php $cta = (string) $meta->fieldOr('services_cta_text', $D); ?>
         <?php if ($cta !== '') : ?>
            <a class="btn-dark btn-arrow home-services__cta"
               href="<?= esc_url((string) $meta->fieldOr('services_cta_url', $D) ?: home_url('/our-services/')); ?>">
               <?= esc_html($cta); ?>
            </a>
         <?php endif; ?>
      </div>

      <div class="home-services__grid">
         <?php
         $i = 0;
         foreach ($services as $service) :
            $thumb = get_the_post_thumbnail_url($service, 'large');
            $isWide = in_array($i, $wide, true);
            ?>
            <article class="svc-card<?= $isWide ? ' svc-card--wide' : ''; ?>" data-animate>
               <a class="svc-card__link" href="<?= esc_url((string) get_permalink($service)); ?>">
                  <span class="svc-card__media">
                     <?php if ($thumb) : ?>
                        <img src="<?= esc_url($thumb); ?>" alt="<?= esc_attr(get_the_title($service)); ?>" loading="lazy" decoding="async">
                     <?php endif; ?>
                  </span>

                  <span class="svc-card__num label-xs"><?= esc_html(sprintf('%02d', $i + 1)); ?></span>
                  <span class="svc-card__arrow" aria-hidden="true"></span>

                  <span class="svc-card__body">
                     <span class="svc-card__title subheading-md"><?= esc_html($shortName($service)); ?></span>
                     <?php
                     /* The teaser is hidden until hover and expands upward from the
                        bottom-anchored body — measured off the reference recording
                        (t 7.0–7.8: the hovered card's title sits 52px higher than
                        its neighbour's while both CTAs stay on the same line).
                        Clamped to two lines in CSS, so a long editor teaser cannot
                        push the title off the photo. */
                     $teaser = (string) $meta->field('service_teaser', $service->ID, '');
                     ?>
                     <?php if ($teaser !== '') : ?>
                        <span class="svc-card__text body-sm"><?= esc_html($teaser); ?></span>
                     <?php endif; ?>
                     <?php if ($linkLabel !== '') : ?>
                        <span class="svc-card__cta body-sm"><?= esc_html($linkLabel); ?> <span aria-hidden="true">&rarr;</span></span>
                     <?php endif; ?>
                  </span>
               </a>
            </article>
         <?php
            $i++;
         endforeach;
         ?>

         <?php if ($promoTitle !== '') :
            $promoImg = $meta->imageUrl($meta->field('services_promo_image'), $fallbackImg . 'promo-custom-build.jpg');
            $promoLabel = (string) $meta->fieldOr('services_promo_link_text', $D);
            ?>
            <article class="svc-card svc-card--promo<?= in_array($i, $wide, true) ? ' svc-card--wide' : ''; ?>">
               <a class="svc-card__link" href="<?= esc_url((string) $meta->fieldOr('services_promo_url', $D) ?: home_url('/build-your-package/')); ?>">
                  <span class="svc-card__media">
                     <img src="<?= esc_url($promoImg); ?>" alt="<?= esc_attr($promoTitle); ?>" loading="lazy" decoding="async">
                  </span>
                  <span class="svc-card__num label-xs"><?= esc_html(sprintf('%02d', $i + 1)); ?></span>
                  <span class="svc-card__arrow" aria-hidden="true"></span>
                  <span class="svc-card__body">
                     <span class="svc-card__title subheading-md"><?= esc_html($promoTitle); ?></span>
                     <?php if ($promoLabel !== '') : ?>
                        <span class="svc-card__cta body-sm"><?= esc_html($promoLabel); ?> <span aria-hidden="true">&rarr;</span></span>
                     <?php endif; ?>
                  </span>
               </a>
            </article>
         <?php endif; ?>
      </div>

      <?php $features = $meta->fieldRowsOr('services_features', $D); ?>
      <?php if ($features) : ?>
         <ul class="home-services__features">
            <?php foreach ($features as $row) : ?>
               <li class="svc-feature">
                  <span class="svc-feature__icon" aria-hidden="true"></span>
                  <span class="svc-feature__body">
                     <span class="svc-feature__title subheading-xs"><?= esc_html((string) ($row['feature_title'] ?? '')); ?></span>
                     <span class="svc-feature__text body-base"><?= esc_html((string) ($row['feature_text'] ?? '')); ?></span>
                  </span>
               </li>
            <?php endforeach; ?>
         </ul>
      <?php endif; ?>

   </div>
</section>
