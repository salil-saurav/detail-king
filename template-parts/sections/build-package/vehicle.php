<?php

/**
 * Build Your Package — Section 1: Select Your Vehicle.
 *
 * Reads vehicle_sizes repeater from GlobalFields / GlobalDefaults.
 * 4-tile vehicle picker with live multiplier data for price recalculation.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta         = MetaHelper::getInstance();
$vehicleSizes = $meta->rowsOr('vehicle_sizes', 'global');

$vehicleIcons = [
   // Hatchback / Small
   '<svg viewBox="0 0 64 28" width="56" height="25" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M7 21H3c-1.1 0-2-.9-2-2v-4c0-.6.2-1.1.7-1.5L8 7.5C9 6.5 10.5 6 12 6h18c2 0 3.5.8 4.5 2.2l5.5 7.8h17c1.7 0 3 1.3 3 3v2c0 1.1-.9 2-2 2h-4"/>
      <path d="M19 21h26"/>
      <circle cx="13" cy="21" r="5" stroke-width="1.8"/>
      <circle cx="51" cy="21" r="5" stroke-width="1.8"/>
      <path d="M14 7.5l-4.5 6.5H23V7.5H14zM26 7.5v6.5h11l-4.5-6.5H26z"/>
   </svg>',
   // Sedan / Medium
   '<svg viewBox="0 0 64 28" width="56" height="25" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M7 21H3c-1.1 0-2-.9-2-2v-4c0-.6.3-1.2.8-1.6L9 9c1.2-1.3 2.9-2 4.7-2h20.6c2.1 0 4 .9 5.3 2.5l6.4 7.5h12c1.7 0 3 1.3 3 3v2c0 1.1-.9 2-2 2h-4"/>
      <path d="M19 21h26"/>
      <circle cx="13" cy="21" r="5" stroke-width="1.8"/>
      <circle cx="51" cy="21" r="5" stroke-width="1.8"/>
      <path d="M15 8.5l-4.5 6h13.5v-6H15zM27 8.5v6h17l-5-6H27z"/>
   </svg>',
   // SUV / Large
   '<svg viewBox="0 0 64 28" width="56" height="25" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M7 21H3c-1.1 0-2-.9-2-2v-6c0-.8.4-1.5 1-2l5-5.5C8.2 4.6 9.6 4 11 4h27c1.6 0 3.1.8 4 2.2l6 8.8h10c1.7 0 3 1.3 3 3v3c0 1.1-.9 2-2 2h-4"/>
      <path d="M19 21h26"/>
      <circle cx="13" cy="21" r="5" stroke-width="1.8"/>
      <circle cx="51" cy="21" r="5" stroke-width="1.8"/>
      <path d="M12 5.5l-3.5 7h15.5v-7H12zM27 5.5v7h18l-4.5-7H27z"/>
   </svg>',
   // Supercar / XL
   '<svg viewBox="0 0 64 28" width="56" height="25" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M7 21H3c-1.1 0-2-.9-2-2v-3c0-.6.3-1.1.7-1.5L14 8c1.6-1.3 3.6-2 5.6-2h15.8c2.4 0 4.6 1.1 6 3l6.6 6.5h8c1.7 0 3 1.3 3 3v2c0 1.1-.9 2-2 2h-4"/>
      <path d="M19 21h26"/>
      <circle cx="13" cy="21" r="5" stroke-width="1.8"/>
      <circle cx="51" cy="21" r="5" stroke-width="1.8"/>
      <path d="M19 7.5l-4 7h13v-7H19zM31 7.5v7h17l-6.5-7H31z"/>
   </svg>',
];

$displayLabels = [
   __('Small Vehicle', 'detailking'),
   __('Medium Vehicle', 'detailking'),
   __('Large Vehicle', 'detailking'),
   __('XL / Super Car', 'detailking'),
];
?>
<div class="byop-card" id="byop-step-vehicle" data-animate="fade">
   <div class="byop-card__head">
      <span class="byop-card__num" aria-hidden="true">1</span>
      <div class="byop-card__titles">
         <h2 class="byop-card__title"><?= esc_html__('Select Your Vehicle', 'detailking'); ?></h2>
         <p class="byop-card__subtitle"><?= esc_html__('What type of vehicle do you have?', 'detailking'); ?></p>
      </div>
   </div>

   <div class="byop-tiles byop-tiles--4" role="radiogroup" aria-label="<?= esc_attr__('Select vehicle size', 'detailking'); ?>">
      <?php foreach ($vehicleSizes as $index => $size) :
         $rawLabel   = (string) ($size['size_label'] ?? '');
         if ($rawLabel === '') continue;

         $slug       = sanitize_title($rawLabel);
         $multiplier = (float) ($size['size_multiplier'] ?? 1);
         $dispLabel  = $displayLabels[$index] ?? $rawLabel;
         $iconSvg    = $vehicleIcons[$index] ?? $vehicleIcons[0];
         $isSelected = ($index === 0);
         ?>
         <button
            type="button"
            class="byop-tile byop-tile--vehicle<?= $isSelected ? ' is-selected' : ''; ?>"
            role="radio"
            aria-checked="<?= $isSelected ? 'true' : 'false'; ?>"
            data-vehicle-tile
            data-vehicle-slug="<?= esc_attr($slug); ?>"
            data-vehicle-label="<?= esc_attr($dispLabel); ?>"
            data-vehicle-raw-label="<?= esc_attr($rawLabel); ?>"
            data-multiplier="<?= esc_attr((string) $multiplier); ?>"
         >
            <span class="byop-tile__check" aria-hidden="true">
               <svg viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3.5 8.5 6.5 11.5 12.5 4.5"/></svg>
            </span>
            <span class="byop-tile__icon"><?= $iconSvg; ?></span>
            <span class="byop-tile__label"><?= esc_html(strtoupper($dispLabel)); ?></span>
         </button>
      <?php endforeach; ?>
   </div>
</div>
