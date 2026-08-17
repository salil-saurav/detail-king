<?php

/**
 * Build Your Package — Section 3: Your Details.
 *
 * Contact fields per the byop-data-map.md:
 * - Full Name* (full_name)
 * - Phone* (phone)
 * - Email* (email)
 * - Preferred Booking Date (drop_date)
 * - Additional Notes (notes)
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;
?>
<div class="byop-card" id="byop-step-details" data-animate="fade">
   <div class="byop-card__head">
      <span class="byop-card__num" aria-hidden="true">3</span>
      <div class="byop-card__titles">
         <h2 class="byop-card__title"><?= esc_html__('Your Details', 'detailking'); ?></h2>
         <p class="byop-card__subtitle"><?= esc_html__('Please enter your contact and booking preferences:', 'detailking'); ?></p>
      </div>
   </div>

   <div class="byop-details-form">
      <div class="dk-form__grid">
         <div class="dk-field">
            <label class="dk-field__label body-sm" for="byop-field-name"><?= esc_html__('Full Name *', 'detailking'); ?></label>
            <input class="dk-field__input" id="byop-field-name" name="full_name" type="text" autocomplete="name" placeholder="<?= esc_attr__('e.g. John Doe', 'detailking'); ?>" required>
         </div>

         <div class="dk-field">
            <label class="dk-field__label body-sm" for="byop-field-phone"><?= esc_html__('Phone Number *', 'detailking'); ?></label>
            <input class="dk-field__input" id="byop-field-phone" name="phone" type="tel" autocomplete="tel" placeholder="<?= esc_attr__('e.g. 021 123 4567', 'detailking'); ?>" required>
         </div>

         <div class="dk-field">
            <label class="dk-field__label body-sm" for="byop-field-email"><?= esc_html__('Email Address *', 'detailking'); ?></label>
            <input class="dk-field__input" id="byop-field-email" name="email" type="email" autocomplete="email" placeholder="<?= esc_attr__('e.g. john@example.com', 'detailking'); ?>" required>
         </div>

         <div class="dk-field">
            <label class="dk-field__label body-sm" for="byop-field-date"><?= esc_html__('Preferred Booking Date', 'detailking'); ?></label>
            <input class="dk-field__input" id="byop-field-date" name="drop_date" type="date">
         </div>

         <div class="dk-field byop-field--full">
            <label class="dk-field__label body-sm" for="byop-field-notes"><?= esc_html__('Additional Notes', 'detailking'); ?></label>
            <textarea class="dk-field__input" id="byop-field-notes" name="notes" rows="4" placeholder="<?= esc_attr__('Any specific requirements, questions or comments?', 'detailking'); ?>"></textarea>
         </div>
      </div>
   </div>
</div>
