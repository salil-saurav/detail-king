<?php

/**
 * Single service — the embedded 3-step booking widget. Design band y
 * 1439…3443 (varies with package count), node e.g. `172:963` (Window Tinting).
 *
 * This *is* BUILD-PLAN's "Booking wizard" — turns out to be embedded on the
 * service page, not a separate route (see figma-data/service-single-spec.md).
 * Step 3 ("Recommended Add-Ons") has no designed content yet — that's step 8
 * (dk_addon + the recommendation popup), a later pass — so this stays a real
 * 2-step flow: choose a package (clickable cards, live price recalculation),
 * fill in details, and submit. Submission is real: `booking-widget.js` posts
 * to BookingWidgetService, which branches on `booking_mode` — instant-booking
 * services add the package's linked Woo product to the cart; Vinyl Wraps
 * (enquiry-only) writes a dk_booking record instead. No cart, no fetch, no
 * theme code here — this file only renders the markup + data attributes the
 * JS reads.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta        = MetaHelper::getInstance();
$serviceId   = get_the_ID();
$bookingMode = (string) $meta->field('booking_mode', $serviceId, 'instant_booking');

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
      $estimatedPrice = round($basePrice * (float) ($defaultSize['size_multiplier'] ?? 1));
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

$submitLabel = $bookingMode === 'enquiry'
   ? __('Send Enquiry', 'detailking')
   : __('Add to Cart', 'detailking');
?>
<section class="service-booking section--dark" id="dk-booking" data-animate="fade" data-booking-mode="<?= esc_attr($bookingMode); ?>">
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
            <li class="service-booking__step<?= $step['active'] ? ' is-active' : ''; ?><?= $step['num'] === 3 ? ' is-inert' : ''; ?>">
               <span class="service-booking__stepnum" aria-hidden="true"><?= esc_html((string) $step['num']); ?></span>
               <span class="service-booking__steplabel"><?= esc_html($step['label']); ?></span>
            </li>
         <?php endforeach; ?>
      </ol>

      <?php if ($packages) : ?>
         <div class="service-packages" role="group" aria-label="<?= esc_attr__('Choose a package', 'detailking'); ?>">
            <?php foreach ($packages as $package) :
               $isSelected  = $selected && $package->ID === $selected->ID;
               $isFeatured  = (bool) $meta->field('package_selected', $package->ID, false);
               $price       = (float) $meta->field('package_price', $package->ID, 0);
               $badge       = $isFeatured ? (string) $meta->field('package_badge', $package->ID) : '';
               $desc        = (string) $meta->field('package_description', $package->ID);
               $thumb       = get_the_post_thumbnail_url($package, 'large');
               ?>
               <button
                  type="button"
                  class="service-package<?= $isSelected ? ' is-selected' : ''; ?>"
                  aria-pressed="<?= $isSelected ? 'true' : 'false'; ?>"
                  data-pkg-id="<?= esc_attr((string) $package->ID); ?>"
                  data-pkg-price="<?= esc_attr((string) $price); ?>"
                  data-pkg-title="<?= esc_attr(get_the_title($package)); ?>"
               >
                  <span class="service-package__media">
                     <?php if ($thumb) : ?>
                        <img src="<?= esc_url($thumb); ?>" alt="" loading="lazy" decoding="async">
                     <?php endif; ?>
                     <?php if ($badge !== '') : ?>
                        <span class="service-package__badge"><?= esc_html($badge); ?></span>
                     <?php endif; ?>
                     <span class="service-package__check" aria-hidden="true">&#10003;</span>
                  </span>
                  <span class="service-package__body">
                     <span class="service-package__title"><?= esc_html(get_the_title($package)); ?></span>
                     <?php if ($desc !== '') : ?>
                        <span class="service-package__desc"><?= esc_html($desc); ?></span>
                     <?php endif; ?>
                     <?php if ($price > 0) : ?>
                        <span class="service-package__price">
                           <span class="service-package__pricelabel"><?= esc_html__('From', 'detailking'); ?></span>
                           <span class="text-gold-gradient">$<?= esc_html((string) round($price)); ?></span>
                        </span>
                     <?php endif; ?>
                  </span>
               </button>
            <?php endforeach; ?>
         </div>
      <?php endif; ?>

      <div class="service-details">
         <h3 class="service-details__title"><?= esc_html__('Your Details', 'detailking'); ?></h3>
         <p class="service-details__subtitle"><?= esc_html__('Pricing adjusts to your vehicle size. Tell us when and where to take care of it.', 'detailking'); ?></p>

         <form class="dk-form service-details__form" data-dk-booking-form>
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
                     <?php foreach ($vehicleSizes as $size) :
                        $label = (string) ($size['size_label'] ?? '');
                        if ($label === '') continue;
                        ?>
                        <option
                           value="<?= esc_attr(sanitize_title($label)); ?>"
                           data-slug="<?= esc_attr(sanitize_title($label)); ?>"
                           data-multiplier="<?= esc_attr((string) ($size['size_multiplier'] ?? 1)); ?>"
                           <?= ($defaultSize && $size === $defaultSize) ? ' selected' : ''; ?>
                        ><?= esc_html($label); ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="dk-field">
                  <label class="dk-field__label body-sm" for="dk-svc-name"><?= esc_html__('Full Name', 'detailking'); ?></label>
                  <input class="dk-field__input" id="dk-svc-name" name="full_name" type="text" autocomplete="name" required>
               </div>
               <div class="dk-field">
                  <label class="dk-field__label body-sm" for="dk-svc-phone"><?= esc_html__('Phone Number', 'detailking'); ?></label>
                  <input class="dk-field__input" id="dk-svc-phone" name="phone" type="tel" autocomplete="tel" required>
               </div>
               <div class="dk-field">
                  <label class="dk-field__label body-sm" for="dk-svc-email"><?= esc_html__('Email', 'detailking'); ?></label>
                  <input class="dk-field__input" id="dk-svc-email" name="email" type="email" autocomplete="email" required>
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
               <div class="dk-field service-details__location">
                  <label class="dk-field__label body-sm" for="dk-svc-notes"><?= esc_html__('Notes', 'detailking'); ?></label>
                  <input class="dk-field__input" id="dk-svc-notes" name="notes" type="text" placeholder="<?= esc_attr__('Anything else we should know?', 'detailking'); ?>">
               </div>
            </div>

            <div class="service-details__pricerow">
               <span class="service-details__pricelabel"><?= esc_html__('Estimated Price', 'detailking'); ?></span>
               <span class="service-details__priceval text-gold-gradient" data-dk-price-display>
                  <?= $estimatedPrice !== null ? '$' . esc_html((string) $estimatedPrice) : '&mdash;'; ?>
               </span>
            </div>

            <p class="dk-form__trap" aria-hidden="true">
               <label>
                  <?php esc_html_e('Leave this field empty', 'detailking'); ?>
                  <input type="text" name="dk_hp" tabindex="-1" autocomplete="off">
               </label>
            </p>

            <button class="btn-gold btn-arrow dk-form__submit" type="submit">
               <?= esc_html($submitLabel); ?>
            </button>

            <p class="dk-form__status" role="status" aria-live="polite"></p>

            <p class="service-details__disclaimer">
               <?= $bookingMode === 'enquiry'
                  ? esc_html__("We'll follow up by phone or email to confirm details and pricing.", 'detailking')
                  : esc_html__('Pricing is an estimate and confirmed at booking based on your vehicle.', 'detailking'); ?>
            </p>
         </form>
      </div>

   </div>
</section>
