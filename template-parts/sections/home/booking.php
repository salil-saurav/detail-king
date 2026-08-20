<?php

/**
 * Booking form. Design band y 10215.1–11267.5, node 59:2813.
 *
 * Copy left, a dark form card right with ten fields in two columns.
 *
 * The form posts through StackPress's FormService (see FormRegistry) rather than a
 * plugin, so submissions land in the Leads list and the spam hardening — honeypot,
 * time-trap, throttle — is already handled. The service/package/add-on selects are
 * populated from the CPTs so they cannot drift from what is actually bookable.
 *
 * This is the *short* booking form. The full three-step wizard with dynamic pricing
 * lives on the service pages.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$phone = (string) $meta->optOr('contact_phone');
$hours = (string) $meta->optOr('contact_hours');

/** Options for the service select, straight from the CPT. */
$serviceOptions = [];
foreach (
   get_posts([
      'post_type'        => 'dk_service',
      'posts_per_page'   => -1,
      'orderby'          => ['menu_order' => 'ASC'],
      'suppress_filters' => false,
   ]) as $service
) {
   $serviceOptions[] = get_the_title($service);
}

$locationOptions = [];
foreach (
   get_posts([
      'post_type'        => 'dk_location',
      'posts_per_page'   => -1,
      'orderby'          => ['menu_order' => 'ASC'],
      'suppress_filters' => false,
   ]) as $location
) {
   $locationOptions[] = get_the_title($location);
}
if (!$locationOptions) {
   // No locations entered yet — the comp's own value, so the select is never empty.
   $locationOptions = [__('Studio Drop-Off', 'detailking')];
}

$addonOptions = [__('None', 'detailking')];
foreach (
   get_posts([
      'post_type'        => 'dk_addon',
      'posts_per_page'   => -1,
      'orderby'          => ['menu_order' => 'ASC'],
      'suppress_filters' => false,
   ]) as $addon
) {
   $addonOptions[] = get_the_title($addon);
}

$vehicleTypes = [
   __('Small', 'detailking'),
   __('Medium', 'detailking'),
   __('Large', 'detailking'),
   __('Supercar', 'detailking'),
];

/** Render a select. Options are plain strings; the label is the value. */
$select = static function (string $name, string $label, array $options): void { ?>
   <div class="dk-field">
      <label class="dk-field__label body-sm" for="dk-<?= esc_attr($name); ?>"><?= esc_html($label); ?></label>
      <select class="dk-field__input" id="dk-<?= esc_attr($name); ?>" name="<?= esc_attr($name); ?>">
         <?php foreach ($options as $option) : ?>
            <option value="<?= esc_attr((string) $option); ?>"><?= esc_html((string) $option); ?></option>
         <?php endforeach; ?>
      </select>
   </div>
<?php };

/** Render a text-ish input. */
$input = static function (string $name, string $label, string $type = 'text', string $placeholder = '', bool $required = false): void { ?>
   <div class="dk-field">
      <label class="dk-field__label body-sm" for="dk-<?= esc_attr($name); ?>"><?= esc_html($label); ?></label>
      <input class="dk-field__input"
         id="dk-<?= esc_attr($name); ?>"
         name="<?= esc_attr($name); ?>"
         type="<?= esc_attr($type); ?>"
         <?= $placeholder !== '' ? 'placeholder="' . esc_attr($placeholder) . '"' : ''; ?>
         <?= $required ? 'required' : ''; ?>>
   </div>
<?php };
?>
<section class="home-booking section--dark" data-animate="fade">
   <div class="container-dk home-booking__inner">

      <div class="home-booking__copy">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => $meta->fieldOr('booking_eyebrow', $D),
            'title'   => $meta->fieldOr('booking_heading', $D),
            'gold'    => $meta->fieldOr('booking_heading_gold', $D),
            'size'    => 'display-sm',
            'text'    => $meta->fieldOr('booking_text', $D),
            'eyebrow_animate' => 'fade-up',
            'animate' => 'fade-up',
            'text_animate' => 'fade-up',
         ]);
         ?>

         <?php
         $label = (string) $meta->fieldOr('booking_label', $D);

         /* This label is a ring of text around the dial, not a line above it.
            The export reads `BOOK YOUR DETAIL REMIUM CARE` — two fragments in the
            wrong order with a letter missing — which figma-data/homepage-spec.md
            filed as a comp defect. It is not: that is what a Figma text-on-a-
            circle flattens to in a PNG export, and the reference recording shows
            the same words curved around the disc and turning slowly (measured
            ~4°/s, i.e. one revolution per 90s). Rendered as an SVG textPath so
            the words stay real text — selectable, translatable, and readable by
            a screen reader from the <title>. */
         ?>
         <div class="home-booking__seal">
            <?php if ($label !== '') : ?>
               <svg class="home-booking__sealtext" data-animate="zoom-in" viewBox="0 0 200 200" role="img"
                  aria-label="<?= esc_attr($label); ?>">
                  <defs>
                     <path id="dk-seal-path" fill="none"
                        d="M100,100 m-72,0 a72,72 0 1,1 144,0 a72,72 0 1,1 -144,0"></path>
                  </defs>
                  <text>
                     <textPath href="#dk-seal-path" startOffset="0">
                        <?= esc_html($label); ?>&nbsp;&middot;&nbsp;
                     </textPath>
                  </text>
               </svg>
            <?php endif; ?>

            <a class="home-booking__dial" href="#dk-booking-form" aria-label="<?php esc_attr_e('Go to the booking form', 'detailking'); ?>">
               <span aria-hidden="true">&rarr;</span>
            </a>
         </div>

         <ul class="home-booking__chips">
            <?php if ($phone !== '') : ?>
               <li>
                  <span class="home-booking__chipicon" aria-hidden="true">
                     <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'phone']); ?>
                  </span>
                  <a href="tel:<?= esc_attr((string) preg_replace('/[^\d+]/', '', $phone)); ?>" class="body-sm"><?= esc_html($phone); ?></a>
               </li>
            <?php endif; ?>
            <?php if ($hours !== '') : ?>
               <li>
                  <span class="home-booking__chipicon" aria-hidden="true">
                     <?php get_template_part('template-parts/components/social-icon', null, ['icon' => 'clock']); ?>
                  </span>
                  <span class="body-sm"><?= esc_html($hours); ?></span>
               </li>
            <?php endif; ?>
         </ul>
      </div>

      <div class="home-booking__card" id="dk-booking-form" data-animate="fade-right">
         <h2 class="home-booking__cardtitle heading-sm">
            <?= esc_html((string) $meta->fieldOr('booking_form_title', $D)); ?>
            <span class="text-gold-gradient"><?= esc_html((string) $meta->fieldOr('booking_form_title_gold', $D)); ?></span>
         </h2>

         <?php
         /* data-sp-form is what forms.js binds to; the handler is FormService's
            REST endpoint. Rendered through the registry so the nonce, honeypot and
            time-trap fields come from one place. */
         ?>
         <form class="dk-form" data-sp-form="home-booking" method="post" novalidate>
            <div class="dk-form__grid">
               <?php
               $select('service', __('Choose Service', 'detailking'), $serviceOptions ?: [__('Detailing', 'detailking')]);
               $select('package', __('Choose Package', 'detailking'), [__('Essential', 'detailking'), __('Signature', 'detailking'), __('Prestige', 'detailking')]);
               $select('vehicle_type', __('Vehicle Type', 'detailking'), $vehicleTypes);
               $select('addons', __('Optional Add-Ons', 'detailking'), $addonOptions);
               $input('name', __('Name', 'detailking'), 'text', __('Full name', 'detailking'), true);
               $input('phone', __('Phone Number', 'detailking'), 'tel', '+64 …', true);
               $input('email', __('Email', 'detailking'), 'email', 'you@email.com', true);
               $select('location', __('Location', 'detailking'), $locationOptions);
               $input('drop_date', __('Drop Date', 'detailking'), 'date');
               $input('drop_time', __('Drop Time', 'detailking'), 'time');
               ?>
            </div>

            <?php
            // Honeypot + timestamp. forms.js signs and submits these; a bot that
            // fills the honeypot or answers too fast is rejected server-side.
            ?>
            <p class="dk-form__trap" aria-hidden="true">
               <label>
                  <?php esc_html_e('Leave this field empty', 'detailking'); ?>
                  <input type="text" name="dk_hp" tabindex="-1" autocomplete="off">
               </label>
            </p>

            <button class="btn-gold btn-arrow dk-form__submit" type="submit">
               <?= esc_html((string) $meta->fieldOr('booking_submit_text', $D)); ?>
            </button>

            <p class="dk-form__status" role="status" aria-live="polite"></p>
         </form>
      </div>

   </div>
</section>
