<?php

/**
 * Contact Page — Section 2: Studio Locations & Map Embeds.
 *
 * Dark section holding the heading and the two studio location cards with plain
 * Google Map iframes and directions links.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'contact';

$locations = get_posts([
   'post_type'        => 'dk_location',
   'posts_per_page'   => -1,
   'orderby'          => ['menu_order' => 'ASC'],
   'suppress_filters' => false,
]);
?>
<section class="contact-section contact-section--studios section--dark" id="contact-studios">
   <div class="container-dk">

      <div class="contact-studios__head">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => (string) $meta->fieldOr('studios_eyebrow', $D),
            'title'   => (string) $meta->fieldOr('studios_title', $D),
            'gold'    => (string) $meta->fieldOr('studios_title_gold', $D),
            'text'    => (string) $meta->fieldOr('studios_text', $D),
            'size'    => 'display-md',
            'align'   => 'center',
            'rules'   => true,
            'break'   => true,
         ]);
         ?>
      </div>

      <div class="contact-studios__grid">
         <?php if ($locations) : ?>
            <?php foreach ($locations as $loc) :
               $locId   = $loc->ID;
               $title   = get_the_title($loc);
               // Extract city name if title is like "Christchurch Studio"
               $displayName = preg_replace('/\s+Studio$/i', '', $title) ?: $title;
               $badge   = (string) $meta->field('location_badge', $locId, '');
               $address = (string) $meta->field('location_address', $locId, '');
               $embed   = (string) $meta->field('location_map_embed', $locId, '');
               $dirUrl  = (string) $meta->field('location_directions_url', $locId, '');
               if ($dirUrl === '' && $address !== '') {
                  $dirUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($address);
               }
            ?>
               <article class="contact-studio-card" data-animate="fade">
                  <div class="contact-studio-card__header">
                     <div class="contact-studio-card__headline">
                        <h3 class="contact-studio-card__title"><?= esc_html($displayName); ?></h3>
                        <?php if ($badge !== '') : ?>
                           <span class="contact-studio-card__badge"><?= esc_html($badge); ?></span>
                        <?php endif; ?>
                     </div>
                     <?php if ($address !== '') : ?>
                        <p class="contact-studio-card__address"><?= esc_html($address); ?></p>
                     <?php endif; ?>
                  </div>

                  <div class="contact-studio-card__map">
                     <?php if ($embed !== '') : ?>
                        <iframe
                           src="<?= esc_url($embed); ?>"
                           width="100%"
                           height="340"
                           style="border:0;"
                           allowfullscreen=""
                           loading="lazy"
                           referrerpolicy="no-referrer-when-downgrade"
                           title="<?= esc_attr($title); ?> Map">
                        </iframe>
                     <?php endif; ?>
                  </div>

                  <?php if ($dirUrl !== '') : ?>
                     <div class="contact-studio-card__footer">
                        <a href="<?= esc_url($dirUrl); ?>" class="contact-studio-card__dirlink" target="_blank" rel="noopener noreferrer">
                           <?= esc_html__('Get Directions', 'detailking'); ?> <span class="contact-studio-card__arrow" aria-hidden="true">→</span>
                        </a>
                     </div>
                  <?php endif; ?>
               </article>
            <?php endforeach; ?>
         <?php else : ?>
            <!-- Fallback static cards when no CPT entries exist yet -->
            <article class="contact-studio-card" data-animate="fade">
               <div class="contact-studio-card__header">
                  <div class="contact-studio-card__headline">
                     <h3 class="contact-studio-card__title">Christchurch</h3>
                     <span class="contact-studio-card__badge">Headquarter</span>
                  </div>
                  <p class="contact-studio-card__address">72 Byron Street, Sydenham, Christchurch 8011</p>
               </div>
               <div class="contact-studio-card__map">
                  <iframe
                     src="https://maps.google.com/maps?q=72+Byron+Street,+Sydenham,+Christchurch+8011,+New+Zealand&amp;t=&amp;z=15&amp;ie=UTF8&amp;iwloc=&amp;output=embed"
                     width="100%"
                     height="340"
                     style="border:0;"
                     allowfullscreen=""
                     loading="lazy"
                     referrerpolicy="no-referrer-when-downgrade"
                     title="Christchurch Studio Map">
                  </iframe>
               </div>
               <div class="contact-studio-card__footer">
                  <a href="https://www.google.com/maps/dir/?api=1&amp;destination=72+Byron+Street,+Sydenham,+Christchurch+8011" class="contact-studio-card__dirlink" target="_blank" rel="noopener noreferrer">
                     Get Directions <span class="contact-studio-card__arrow" aria-hidden="true">→</span>
                  </a>
               </div>
            </article>

            <article class="contact-studio-card" data-animate="fade">
               <div class="contact-studio-card__header">
                  <div class="contact-studio-card__headline">
                     <h3 class="contact-studio-card__title">Dunedin</h3>
                     <span class="contact-studio-card__badge">Branch</span>
                  </div>
                  <p class="contact-studio-card__address">31 Otaki Street, South Dunedin, Dunedin 9012</p>
               </div>
               <div class="contact-studio-card__map">
                  <iframe
                     src="https://maps.google.com/maps?q=31+Otaki+Street,+South+Dunedin,+Dunedin+9012,+New+Zealand&amp;t=&amp;z=15&amp;ie=UTF8&amp;iwloc=&amp;output=embed"
                     width="100%"
                     height="340"
                     style="border:0;"
                     allowfullscreen=""
                     loading="lazy"
                     referrerpolicy="no-referrer-when-downgrade"
                     title="Dunedin Studio Map">
                  </iframe>
               </div>
               <div class="contact-studio-card__footer">
                  <a href="https://www.google.com/maps/dir/?api=1&amp;destination=31+Otaki+Street,+South+Dunedin,+Dunedin+9012" class="contact-studio-card__dirlink" target="_blank" rel="noopener noreferrer">
                     Get Directions <span class="contact-studio-card__arrow" aria-hidden="true">→</span>
                  </a>
               </div>
            </article>
         <?php endif; ?>
      </div>

   </div>
</section>
