<?php

/**
 * Contact Page — Section 1: Form & Direct Contact Info.
 *
 * Light background container holding the Contact Form (posting via FormService /
 * FormRegistry) and the two dark direct-contact cards ("Reach Us Directly" &
 * "Our Locations").
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

$phone = (string) $meta->fieldOr('direct_phone', $D);
$email = (string) $meta->fieldOr('direct_email', $D);
$hours = (string) $meta->fieldOr('direct_hours', $D);
?>
<section class="contact-section contact-section--form section--light" id="contact-form-section" data-animate="fade">
   <div class="container-dk">
      <div class="contact-layout">

         <!-- Left column: Contact Form Card -->
         <div class="contact-form-card" data-animate="fade-left">
            <h2 class="contact-form-card__title">
               <?= esc_html((string) $meta->fieldOr('form_title', $D)); ?>
               <?php if ($meta->fieldOr('form_title_gold', $D) !== '') : ?>
                  <span class="text-gold-gradient"><?= esc_html((string) $meta->fieldOr('form_title_gold', $D)); ?></span>
               <?php endif; ?>
            </h2>

            <?php if ($meta->fieldOr('form_text', $D) !== '') : ?>
               <p class="contact-form-card__subtitle body-md">
                  <?= esc_html((string) $meta->fieldOr('form_text', $D)); ?>
               </p>
            <?php endif; ?>

            <form class="dk-form contact-form" data-sp-form="contact" method="post" novalidate>
               <div class="contact-form__grid">
                  <div class="dk-field">
                     <label class="dk-field__label" for="contact-name"><?= esc_html__('Name', 'detailking'); ?></label>
                     <input class="dk-field__input" id="contact-name" name="name" type="text" placeholder="<?= esc_attr__('Your name', 'detailking'); ?>" required autocomplete="name">
                  </div>

                  <div class="dk-field">
                     <label class="dk-field__label" for="contact-phone"><?= esc_html__('Phone', 'detailking'); ?></label>
                     <input class="dk-field__input" id="contact-phone" name="phone" type="tel" placeholder="<?= esc_attr__('Your phone', 'detailking'); ?>" autocomplete="tel">
                  </div>

                  <div class="dk-field">
                     <label class="dk-field__label" for="contact-email"><?= esc_html__('Email', 'detailking'); ?></label>
                     <input class="dk-field__input" id="contact-email" name="email" type="email" placeholder="<?= esc_attr__('you@email.com', 'detailking'); ?>" required autocomplete="email">
                  </div>

                  <div class="dk-field">
                     <label class="dk-field__label" for="contact-location"><?= esc_html__('Select Location', 'detailking'); ?></label>
                     <select class="dk-field__input dk-field__select" id="contact-location" name="location">
                        <option value=""><?= esc_html__('Choose a location', 'detailking'); ?></option>
                        <?php if ($locations) : ?>
                           <?php foreach ($locations as $loc) : ?>
                              <option value="<?= esc_attr(get_the_title($loc)); ?>"><?= esc_html(get_the_title($loc)); ?></option>
                           <?php endforeach; ?>
                        <?php else : ?>
                           <option value="Christchurch Studio"><?= esc_html__('Christchurch Studio', 'detailking'); ?></option>
                           <option value="Dunedin Studio"><?= esc_html__('Dunedin Studio', 'detailking'); ?></option>
                        <?php endif; ?>
                     </select>
                  </div>

                  <div class="dk-field dk-field--full">
                     <label class="dk-field__label" for="contact-message"><?= esc_html__('Message', 'detailking'); ?></label>
                     <textarea class="dk-field__input dk-field__textarea" id="contact-message" name="message" rows="4" placeholder="<?= esc_attr__('Tell us about your vehicle and what you need...', 'detailking'); ?>"></textarea>
                  </div>
               </div>

               <?php
               // Honeypot + timestamp trap for FormService
               ?>
               <p class="dk-form__trap" aria-hidden="true">
                  <label>
                     <?php esc_html_e('Leave this field empty', 'detailking'); ?>
                     <input type="text" name="dk_hp" tabindex="-1" autocomplete="off">
                  </label>
               </p>

               <button class="btn-gold btn-arrow dk-form__submit contact-form__submit" type="submit">
                  <?= esc_html((string) $meta->fieldOr('form_submit_text', $D)); ?>
                  <span class="btn-arrow__icon" aria-hidden="true">→</span>
               </button>

               <p class="dk-form__status sp-form-status" role="status" aria-live="polite"></p>
            </form>
         </div>

         <!-- Right column: Direct Info Cards -->
         <div class="contact-info">

            <!-- Card 1: Reach Us Directly -->
            <div class="contact-card contact-card--direct" data-animate="fade-right">
               <div class="contact-card__glow" aria-hidden="true"></div>
               <h3 class="contact-card__title heading-xs">
                  <?= esc_html((string) $meta->fieldOr('direct_title', $D)); ?>
               </h3>

               <ul class="contact-card__list">
                  <?php if ($phone !== '') : ?>
                     <li class="contact-card__item">
                        <div class="contact-card__iconbox" aria-hidden="true">
                           <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'phone']); ?>
                        </div>
                        <div class="contact-card__details">
                           <span class="contact-card__label"><?= esc_html((string) $meta->fieldOr('direct_phone_label', $D)); ?></span>
                           <a href="tel:<?= esc_attr((string) preg_replace('/[^\d+]/', '', $phone)); ?>" class="contact-card__value contact-card__value--gold">
                              <?= esc_html($phone); ?>
                           </a>
                        </div>
                     </li>
                  <?php endif; ?>

                  <?php if ($email !== '') : ?>
                     <li class="contact-card__item">
                        <div class="contact-card__iconbox" aria-hidden="true">
                           <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'mail']); ?>
                        </div>
                        <div class="contact-card__details">
                           <span class="contact-card__label"><?= esc_html((string) $meta->fieldOr('direct_email_label', $D)); ?></span>
                           <a href="mailto:<?= esc_attr($email); ?>" class="contact-card__value contact-card__value--gold">
                              <?= esc_html($email); ?>
                           </a>
                        </div>
                     </li>
                  <?php endif; ?>

                  <?php if ($hours !== '') : ?>
                     <li class="contact-card__item">
                        <div class="contact-card__iconbox" aria-hidden="true">
                           <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'clock']); ?>
                        </div>
                        <div class="contact-card__details">
                           <span class="contact-card__label"><?= esc_html((string) $meta->fieldOr('direct_hours_label', $D)); ?></span>
                           <span class="contact-card__value contact-card__value--muted">
                              <?= esc_html($hours); ?>
                           </span>
                        </div>
                     </li>
                  <?php endif; ?>
               </ul>
            </div>

            <!-- Card 2: Our Locations -->
            <div class="contact-card contact-card--locations" data-animate="fade-right">
               <div class="contact-card__glow" aria-hidden="true"></div>
               <h3 class="contact-card__title heading-xs">
                  <?= esc_html((string) $meta->fieldOr('direct_locations_title', $D)); ?>
               </h3>

               <div class="contact-locations-summary">
                  <?php if ($locations) : ?>
                     <?php foreach ($locations as $loc) :
                        $locId   = $loc->ID;
                        $badge   = (string) $meta->field('location_badge', $locId, '');
                        $address = (string) $meta->field('location_address', $locId, '');
                     ?>
                        <div class="contact-locations-summary__item">
                           <div class="contact-card__iconbox" aria-hidden="true">
                              <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'pin']); ?>
                           </div>
                           <div class="contact-card__details">
                              <span class="contact-card__label"><?= esc_html(strtoupper($badge ?: get_the_title($loc))); ?></span>
                              <p class="contact-card__value contact-card__value--address"><?= nl2br(esc_html($address)); ?></p>
                           </div>
                        </div>
                     <?php endforeach; ?>
                  <?php else : ?>
                     <div class="contact-locations-summary__item">
                        <div class="contact-card__iconbox" aria-hidden="true">
                           <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'pin']); ?>
                        </div>
                        <div class="contact-card__details">
                           <span class="contact-card__label">HEADQUARTER</span>
                           <p class="contact-card__value contact-card__value--address">72 Byron Street,<br>Sydenham,<br>Christchurch 8011</p>
                        </div>
                     </div>
                     <div class="contact-locations-summary__item">
                        <div class="contact-card__iconbox" aria-hidden="true">
                           <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'pin']); ?>
                        </div>
                        <div class="contact-card__details">
                           <span class="contact-card__label">BRANCH</span>
                           <p class="contact-card__value contact-card__value--address">31 Otaki Street, South<br>Dunedin, Dunedin 9012</p>
                        </div>
                     </div>
                  <?php endif; ?>
               </div>
            </div>

         </div>

      </div>
   </div>
</section>
