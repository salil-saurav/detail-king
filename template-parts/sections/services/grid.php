<?php

/**
 * Our Services card grid — design band y 790…5182 (node 159:20 → 159:23).
 *
 * One `Article` per dk_service, alternating photo-left/text-right and
 * text-left/photo-right (CSS `:nth-child(even)` — no PHP odd/even branching
 * needed). Every service gets the same 4 blocks: numbered label, heading
 * (service_short_name — see filter-bar.php for why), teaser, feature-chip row,
 * Book/View actions. `service_grid_caption` optionally overlays a numbered
 * badge + caption on the photo — the comp only fills it in for one card
 * (Add-On Services); see figma-data/our-services-spec.md.
 *
 * `data-animate` sits on each `<article>`, not the wrapping `<section>` — the
 * global.js observer needs ~12% of a target's own height to intersect the
 * viewport before it reveals, and this section (7 cards, thousands of px
 * tall, more on mobile where cards stack) can never satisfy that ratio as one
 * target. Wrapping the section left the whole grid permanently invisible at
 * mobile widths, where it never gets the ~13% overlap it needs. Per-card is
 * both correct (each card is its own viewport-scale unit) and nicer (reveals
 * one at a time on scroll).
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();

$services = get_posts([
   'post_type'        => 'dk_service',
   'posts_per_page'   => -1,
   'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
   'suppress_filters' => false,
]);
?>
<section class="services-grid section--light">
   <div class="container-dk">
      <div class="svc-grid">
         <?php foreach ($services as $i => $service) :
            $terms   = get_the_terms($service, 'service_category');
            $catSlug = ($terms && !is_wp_error($terms)) ? $terms[0]->slug : '';

            $shortName = (string) $meta->field('service_short_name', $service->ID, get_the_title($service));
            $teaser    = (string) $meta->field('service_teaser', $service->ID, '');
            $features  = $meta->fieldRows('service_features', $service->ID);
            $caption   = (string) $meta->field('service_grid_caption', $service->ID, '');

            $thumb = get_the_post_thumbnail_url($service, 'large');
            $url   = (string) get_permalink($service);
            ?>
            <article class="svc-article" data-dk-filter-cats="<?= esc_attr($catSlug); ?>" data-animate="fade">
               <div class="svc-article__media">
                  <?php if ($thumb) : ?>
                     <img src="<?= esc_url($thumb); ?>" alt="<?= esc_attr($shortName); ?>" loading="lazy" decoding="async">
                  <?php endif; ?>
                  <?php if ($caption !== '') : ?>
                     <span class="svc-article__badge"><?= esc_html(sprintf('%02d', $i + 1)); ?></span>
                     <p class="svc-article__caption"><?= esc_html($caption); ?></p>
                  <?php endif; ?>
               </div>

               <div class="svc-article__body">
                  <div class="svc-article__label">
                     <span class="svc-article__rule" aria-hidden="true"></span>
                     <?= esc_html(sprintf(__('SERVICE %02d', 'detailking'), $i + 1)); ?>
                  </div>

                  <h2 class="svc-article__title"><?= esc_html($shortName); ?></h2>

                  <?php if ($teaser !== '') : ?>
                     <p class="svc-article__text"><?= esc_html($teaser); ?></p>
                  <?php endif; ?>

                  <?php if ($features) : ?>
                     <ul class="svc-article__features">
                        <?php foreach ($features as $row) :
                           $text = (string) ($row['feature_text'] ?? '');
                           if ($text === '') {
                              continue;
                           }
                           ?>
                           <li class="svc-article__feature">
                              <?php get_template_part('template-parts/components/glyph', null, ['glyph' => 'sparkle']); ?>
                              <?= esc_html($text); ?>
                           </li>
                        <?php endforeach; ?>
                     </ul>
                  <?php endif; ?>

                  <div class="svc-article__actions">
                     <a class="btn-gold btn-arrow" href="<?= esc_url($url); ?>"><?= esc_html__('Book This Service', 'detailking'); ?></a>
                     <a class="svc-article__link" href="<?= esc_url($url); ?>">
                        <?= esc_html__('View Packages', 'detailking'); ?> <span aria-hidden="true">&rarr;</span>
                     </a>
                  </div>
               </div>
            </article>
         <?php endforeach; ?>
      </div>
   </div>
</section>
