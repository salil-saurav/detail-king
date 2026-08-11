<?php

/**
 * Single service — the embedded 3-step booking widget. Design band y
 * 1439…3443 (varies with package count), node e.g. `172:963` (Window Tinting).
 *
 * This *is* BUILD-PLAN's "Booking wizard" — turns out to be embedded on the
 * service page, not a separate route (see figma-data/service-single-spec.md).
 * This pass builds the full static UI: step indicator, package cards (step 1,
 * looped from dk_package — count genuinely varies per service), and the
 * step-2 details form with a real server-computed price estimate. Nothing
 * submits yet — `data-sp-form`, live recalculation on selection change, and
 * the step-3 Add-Ons content are for the dedicated booking-wizard pass, once
 * every service has real package/addon data to wire against.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta      = MetaHelper::getInstance();
$serviceId = get_the_ID();

$packages = get_posts([
   'post_type'        => 'dk_package',
   'posts_per_page'   => -1,
   'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
   'suppress_filters' => false,
   'meta_query'       => [[
      'key'   => 'package_service',
      'value' => $serviceId,
   ]],
]);

$selected = null;
foreach ($packages as $package) {
   if ($meta->field('package_selected', $package->ID, false)) {
      $selected = $package;
      break;
   }
}
if (!$selected && $packages) {
   $selected = $packages[0];
}

$vehicleSizes = $meta->rowsOr('vehicle_sizes', 'global');
$defaultSize  = $vehicleSizes[1] ?? ($vehicleSizes[0] ?? null);

$estimatedPrice = null;
if ($selected && $defaultSize) {
   $basePrice = (float) $meta->field('package_price', $selected->ID, 0);
   if ($basePrice > 0) {
      $estimatedPrice = $basePrice * (float) ($defaultSize['size_multiplier'] ?? 1);
   }
}

$locations = get_posts([
   'post_type'        => 'dk_location',
   'posts_per_page'   => -1,
   'orderby'          => ['menu_order' => 'ASC'],
   'suppress_filters' => false,
]);

$steps = [
   ['num' => 1, 'label' => __('Choose Package', 'detailking'), 'active' => true],
   ['num' => 2, 'label' => __('Your Details', 'detailking'), 'active' => false],
   ['num' => 3, 'label' => __('Recommended Add-Ons', 'detailking'), 'active' => false],
];
?>
<section class="service-booking section--dark" id="dk-booking" data-animate="fade">
   <div class="container-dk">

      <div class="service-booking__head">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => (string) $meta->optOr('booking_widget_eyebrow'),
            'title'   => (string) $meta->optOr('booking_widget_title'),
            'gold'    => (string) $meta->optOr('booking_widget_title_gold'),
            'size'    => 'display-md',
            'align'   => 'center',
            'rules'   => 'both',
            'text'    => (string) $meta->field('booking_intro_text'),
         ]);
         ?>
      </div>

      <ol class="service-booking__steps">
         <?php foreach ($steps as $step) : ?>
            <li class="service-booking__step<?= $step['active'] ? ' is-active' : ''; ?>">
               <span class="service-booking__stepnum" aria-hidden="true"><?= esc_html((string) $step['num']); ?></span>
               <span class="service-booking__steplabel"><?= esc_html($step['label']); ?></span>
            </li>
         <?php endforeach; ?>
      </ol>

      <?php if ($packages) : ?>
         <div class="service-packages">
            <?php foreach ($packages as $package) :
               $isSelected = $selected && $package->ID === $selected->ID;
               $price      = (float) $meta->field('package_price', $package->ID, 0);
               $badge      = $isSelected ? (string) $meta->field('package_badge', $package->ID) : '';
               $desc       = (string) $meta->field('package_description', $package->ID);
               $thumb      = get_the_post_thumbnail_url($package, 'large');
               ?>
               <article class="service-package<?= $isSelected ? ' is-selected' : ''; ?>">
                  <div class="service-package__media">
                     <?php if ($thumb) : ?>
                        <img src="<?= esc_url($thumb); ?>" alt="" loading="lazy" decoding="async">
                     <?php endif; ?>
                     <?php if ($badge !== '') : ?>
                        <span class="service-package__badge"><?= esc_html($badge); ?></span>
                     <?php endif; ?>
                     <span class="service-package__check" aria-hidden="true">&#10003;</span>
                  </div>
                  <div class="service-package__body">
                     <h3 class="service-package__title"><?= esc_html(get_the_title($package)); ?></h3>
                     <?php if ($desc !== '') : ?>
                        <p class="service-package__desc"><?= esc_html($desc); ?></p>
                     <?php endif; ?>
                     <?php if ($price > 0) : ?>
                        <p class="service-package__price">
                           <span class="service-package__pricelabel"><?= esc_html__('From', 'detailking'); ?></span>
                           <span class="text-gold-gradient">$<?= esc_html((string) round($price)); ?></span>
                        </p>
                     <?php endif; ?>
                  </div>
               </article>
            <?php endforeach; ?>
         </div>
      <?php endif; ?>

      <div class="service-details">
         <h3 class="service-details__title"><?= esc_html__('Your Details', 'detailking'); ?></h3>
         <p class="service-details__subtitle"><?= esc_html__('Pricing adjusts to your vehicle size. Tell us when and where to take care of it.', 'detailking'); ?></p>

         <form class="dk-form service-details__form" novalidate>
            <div class="dk-form__grid">
               <div class="dk-field">
                  <label class="dk-field__label body-sm" for="dk-svc-package"><?= esc_html__('Selected Package', 'detailking'); ?></label>
                  <?php $selectedPrice = $selected ? (float) $meta->field('package_price', $selected->ID, 0) : 0; ?>
                  <select class="dk-field__input" id="dk-svc-package" name="package" disabled>
                     <?php if ($selected) : ?>
                        <option>
                           <?= esc_html(get_the_title($selected)); ?>
                           <?php if ($selectedPrice > 0) : ?>
                              &mdash; <?= esc_html(sprintf(__('from $%s', 'detailking'), round($selectedPrice))); ?>
                           <?php endif; ?>
                        </option>
                     <?php else : ?>
                        <option><?= esc_html__('No packages yet', 'detailking'); ?></option>
                     <?php endif; ?>
                  </select>
               </div>
               <div class="dk-field">
                  <label class="dk-field__label body-sm" for="dk-svc-vehicle"><?= esc_html__('Car Type', 'detailking'); ?></label>
                  <select class="dk-field__input" id="dk-svc-vehicle" name="vehicle_type">
                     <?php foreach ($vehicleSizes as $size) : ?>
                        <option<?= ($defaultSize && $size === $defaultSize) ? ' selected' : ''; ?>><?= esc_html((string) ($size['size_label'] ?? '')); ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="dk-field">
                  <label class="dk-field__label body-sm" for="dk-svc-date"><?= esc_html__('Drop Date', 'detailking'); ?></label>
                  <input class="dk-field__input" id="dk-svc-date" name="drop_date" type="date">
               </div>
               <div class="dk-field">
                  <label class="dk-field__label body-sm" for="dk-svc-time"><?= esc_html__('Drop Time', 'detailking'); ?></label>
                  <input class="dk-field__input" id="dk-svc-time" name="drop_time" type="time">
               </div>
               <div class="dk-field service-details__location">
                  <label class="dk-field__label body-sm" for="dk-svc-location"><?= esc_html__('Location', 'detailking'); ?></label>
                  <select class="dk-field__input" id="dk-svc-location" name="location">
                     <?php if ($locations) : ?>
                        <?php foreach ($locations as $location) : ?>
                           <option><?= esc_html(get_the_title($location)); ?></option>
                        <?php endforeach; ?>
                     <?php else : ?>
                        <option><?= esc_html__('Studio Drop-Off', 'detailking'); ?></option>
                     <?php endif; ?>
                  </select>
               </div>
            </div>

            <div class="service-details__pricerow">
               <span class="service-details__pricelabel"><?= esc_html__('Estimated Price', 'detailking'); ?></span>
               <span class="service-details__priceval text-gold-gradient">
                  <?= $estimatedPrice !== null ? '$' . esc_html((string) round($estimatedPrice)) : '&mdash;'; ?>
               </span>
            </div>

            <?php
            /* Static-UI pass (see file doc comment): a real click target, no
               handler yet. Not `disabled` — everything above is a genuinely
               fillable field, only Submit has nothing to submit to. */
            ?>
            <button class="btn-gold btn-arrow dk-form__submit" type="button">
               <?= esc_html__('Continue', 'detailking'); ?>
            </button>

            <p class="service-details__disclaimer"><?= esc_html__('Pricing is an estimate and confirmed at booking based on your vehicle.', 'detailking'); ?></p>
         </form>
      </div>

   </div>
</section>
