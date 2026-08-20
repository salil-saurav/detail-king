<?php

/**
 * Build Your Package — Package Summary Sidebar.
 *
 * Sticky sidebar displaying:
 * - Selected service's thumbnail image
 * - Selection summary: Vehicle, Service, Package
 * - Additional Services list (dynamic)
 * - Estimated Cost
 * - Submit button
 * - 3 Trust bullets: 100% Secure Booking / Expert Consultation / Premium Quality Assured
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

// Get default selected service (vinyl-wraps) to display initially
$defaultServiceSlug = 'vinyl-wraps';
$services = get_posts([
   'post_type'   => 'dk_service',
   'name'        => $defaultServiceSlug,
   'numberposts' => 1,
]);

$initialThumb = '';
$initialTitle = __('Vinyl Wraps', 'detailking');

if ($services) {
   $initialThumb = get_the_post_thumbnail_url($services[0], 'large');
   $initialTitle = get_the_title($services[0]);
}

// Fallback image if service has no thumbnail
if (!$initialThumb) {
   $initialThumb = get_template_directory_uri() . '/assets/images/placeholder.jpg';
}

?>
<div class="byop-summary-card">
   <!-- Live Service Image -->
   <div class="byop-summary-card__media" data-animate="zoom-in">
      <img src="<?= esc_url($initialThumb); ?>" alt="<?= esc_attr($initialTitle); ?>" class="byop-summary-card__img" data-byop-summary-img>
   </div>

   <div class="byop-summary-card__body">
      <h3 class="byop-summary-card__title"><?= esc_html__('Package Summary', 'detailking'); ?></h3>

      <!-- Selection List -->
      <ul class="byop-summary-card__selections">
         <li class="byop-summary-card__selection" data-animate>
            <span class="byop-summary-card__selection-label"><?= esc_html__('Vehicle Size:', 'detailking'); ?></span>
            <strong class="byop-summary-card__selection-val" data-byop-summary-vehicle>Small Vehicle</strong>
         </li>
         <li class="byop-summary-card__selection" data-animate>
            <span class="byop-summary-card__selection-label"><?= esc_html__('Service:', 'detailking'); ?></span>
            <strong class="byop-summary-card__selection-val" data-byop-summary-service><?= esc_html($initialTitle); ?></strong>
         </li>
         <li class="byop-summary-card__selection" data-animate>
            <span class="byop-summary-card__selection-label"><?= esc_html__('Selected Package:', 'detailking'); ?></span>
            <strong class="byop-summary-card__selection-val" data-byop-summary-package>Custom Build</strong>
         </li>
      </ul>

      <!-- Additional Services list -->
      <div class="byop-summary-card__addons-wrapper" data-byop-summary-addons-wrapper style="display: none;">
         <h4 class="byop-summary-card__section-title"><?= esc_html__('Additional Services', 'detailking'); ?></h4>
         <ul class="byop-summary-card__addons-list" data-byop-summary-addons-list>
            <!-- Dynamic elements will be inserted here -->
         </ul>
      </div>

      <!-- Pricing and Submit -->
      <div class="byop-summary-card__price-section">
         <div class="byop-summary-card__price-row">
            <span class="byop-summary-card__price-label"><?= esc_html__('Estimated Cost', 'detailking'); ?></span>
            <div class="byop-summary-card__price-value-container">
               <span class="byop-summary-card__price-val text-gold-gradient" data-byop-summary-price>$0</span>
            </div>
         </div>

         <!-- Status display for form errors/success -->
         <p class="dk-form__status" role="status" aria-live="polite"></p>

         <button type="submit" class="btn-gold btn-arrow byop-summary-card__submit w-100" data-byop-submit-btn>
            <?= esc_html__('Get My Custom Quote', 'detailking'); ?>
         </button>
         
         <p class="byop-summary-card__disclaimer">
            <?= esc_html__('All estimates exclude custom requirements. We will contact you to confirm details.', 'detailking'); ?>
         </p>
      </div>

      <!-- Trust Bullets -->
      <ul class="byop-summary-card__trust">
         <li class="byop-summary-card__trust-item" data-animate>
            <span class="byop-summary-card__trust-icon" aria-hidden="true">
               <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <span class="byop-summary-card__trust-text"><?= esc_html__('100% Secure Booking', 'detailking'); ?></span>
         </li>
         <li class="byop-summary-card__trust-item" data-animate>
            <span class="byop-summary-card__trust-icon" aria-hidden="true">
               <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </span>
            <span class="byop-summary-card__trust-text"><?= esc_html__('Expert Consultation', 'detailking'); ?></span>
         </li>
         <li class="byop-summary-card__trust-item" data-animate>
            <span class="byop-summary-card__trust-icon" aria-hidden="true">
               <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 22 8.5 22 15.5 12 22 2 15.5 2 8.5 12 2"/><polyline points="2 8.5 12 15 22 8.5"/><polyline points="12 22 12 15"/></svg>
            </span>
            <span class="byop-summary-card__trust-text"><?= esc_html__('Premium Quality Assured', 'detailking'); ?></span>
         </li>
      </ul>
   </div>
</div>
