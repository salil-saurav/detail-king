<?php

/**
 * Build Your Package — Section 2: Select A Service.
 *
 * 6-tile service picker over the 6 real dk_service posts.
 * Selecting a tile reveals that service's conditional requirement radio/checkbox block.
 * Implements the locked data mapping from byop-data-map.md.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();

// 6 canonical services in defined display order
$serviceKeys = [
   'grooming'       => [
      'label' => __('GROOMING', 'detailking'),
      'icon'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v4M4.93 4.93l2.83 2.83M2 12h4M4.93 19.07l2.83-2.83M12 18v4M19.07 19.07l-2.83-2.83M18 12h4M19.07 4.93l-2.83 2.83"/><circle cx="12" cy="12" r="4"/></svg>',
   ],
   'detailing'      => [
      'label' => __('DETAILING', 'detailking'),
      'icon'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3h6v3H9zM10 6v6l-5 9h14l-5-9V6"/><circle cx="10" cy="16" r="1"/><circle cx="14" cy="14" r="1"/></svg>',
   ],
   'ceramic-pro'    => [
      'label' => __('CERAMIC PRO PROTECTION', 'detailking'),
      'icon'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
   ],
   'ppf'            => [
      'label' => __('PPF', 'detailking'),
      'icon'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>',
   ],
   'window-tinting' => [
      'label' => __('WINDOW TINTING', 'detailking'),
      'icon'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>',
   ],
   'vinyl-wraps'    => [
      'label' => __('VINYL WRAPS', 'detailking'),
      'icon'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
   ],
];

// Query all dk_service posts
$servicePosts = get_posts([
   'post_type'        => 'dk_service',
   'posts_per_page'   => -1,
   'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
   'suppress_filters' => false,
]);

$servicesBySlug = [];
foreach ($servicePosts as $sp) {
   $servicesBySlug[$sp->post_name] = $sp;
}

// Query add-on-services packages (reused as add-ons for grooming & detailing)
$addOnServicePost = $servicesBySlug['add-on-services'] ?? null;
$genericAddons = [];
if ($addOnServicePost) {
   $genericAddons = get_posts([
      'post_type'        => 'dk_package',
      'posts_per_page'   => -1,
      'meta_key'         => 'package_service',
      'meta_value'       => $addOnServicePost->ID,
      'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
      'suppress_filters' => false,
   ]);
}

// Vinyl Wraps static options
$vinylOptions = [
   'Chrome Delete',
   'Blackout',
   'Racing Stripes',
   'Roof Wrap',
   'Partial Wrap',
   'Full Vehicle Wrap',
   'Custom Branding / Signage',
];

$defaultActiveServiceSlug = 'vinyl-wraps';
?>
<div class="byop-card" id="byop-step-service" data-animate="fade">
   <div class="byop-card__head">
      <span class="byop-card__num" aria-hidden="true">2</span>
      <div class="byop-card__titles">
         <h2 class="byop-card__title"><?= esc_html__('Select A Service', 'detailking'); ?></h2>
         <p class="byop-card__subtitle"><?= esc_html__('Choose the service you are interested in:', 'detailking'); ?></p>
      </div>
   </div>

   <!-- 6 Service Tiles -->
   <div class="byop-tiles byop-tiles--6" role="radiogroup" aria-label="<?= esc_attr__('Select a service', 'detailking'); ?>">
      <?php foreach ($serviceKeys as $slug => $data) :
         $servicePost = $servicesBySlug[$slug] ?? null;
         $serviceId   = $servicePost ? $servicePost->ID : 0;
         $title       = $servicePost ? get_the_title($servicePost) : $data['label'];
         $thumb       = $servicePost ? get_the_post_thumbnail_url($servicePost, 'large') : '';
         $isSelected  = ($slug === $defaultActiveServiceSlug);
         ?>
         <button
            type="button"
            class="byop-tile byop-tile--service<?= $isSelected ? ' is-selected' : ''; ?>"
            role="radio"
            aria-checked="<?= $isSelected ? 'true' : 'false'; ?>"
            data-service-tile
            data-service-slug="<?= esc_attr($slug); ?>"
            data-service-id="<?= esc_attr((string) $serviceId); ?>"
            data-service-title="<?= esc_attr($title); ?>"
            data-service-thumb="<?= esc_url($thumb); ?>"
         >
            <span class="byop-tile__check" aria-hidden="true">
               <svg viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3.5 8.5 6.5 11.5 12.5 4.5"/></svg>
            </span>
            <span class="byop-tile__icon"><?= $data['icon']; ?></span>
            <span class="byop-tile__label"><?= esc_html($data['label']); ?></span>
         </button>
      <?php endforeach; ?>
   </div>

   <!-- Conditional Service Blocks -->
   <div class="byop-service-blocks">
      <?php foreach ($serviceKeys as $slug => $data) :
         $servicePost = $servicesBySlug[$slug] ?? null;
         $serviceId   = $servicePost ? $servicePost->ID : 0;
         $isActive    = ($slug === $defaultActiveServiceSlug);

         // Fetch packages for this service
         $packages = $serviceId ? get_posts([
            'post_type'        => 'dk_package',
            'posts_per_page'   => -1,
            'meta_key'         => 'package_service',
            'meta_value'       => $serviceId,
            'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
            'suppress_filters' => false,
         ]) : [];
         ?>
         <div
            class="byop-service-block<?= $isActive ? ' is-active' : ''; ?>"
            id="byop-block-<?= esc_attr($slug); ?>"
            data-service-block="<?= esc_attr($slug); ?>"
            <?= !$isActive ? 'hidden' : ''; ?>
         >
            <div class="byop-service-block__badge">
               <span class="byop-service-block__badge-icon"><?= $data['icon']; ?></span>
               <span class="byop-service-block__badge-text"><?= esc_html($servicePost ? get_the_title($servicePost) : $data['label']); ?></span>
            </div>

            <?php if ($slug === 'vinyl-wraps') : ?>
               <!-- Variant B: Vinyl Wraps Layout (Custom check list + Photo upload + Notes) -->
               <div class="byop-service-block__grid byop-service-block__grid--split">
                  <div class="byop-service-block__col">
                     <h3 class="byop-service-block__heading"><?= esc_html__('Select Your Requirement', 'detailking'); ?></h3>
                     <p class="byop-service-block__subheading"><?= esc_html__('Choose one or more wrap options.', 'detailking'); ?></p>

                     <div class="byop-options byop-options--checkbox" role="group" aria-label="<?= esc_attr__('Vinyl wrap options', 'detailking'); ?>">
                        <?php foreach ($vinylOptions as $opt) : ?>
                           <label class="byop-option byop-option--checkbox">
                              <input
                                 type="checkbox"
                                 class="byop-option__input"
                                 name="vinyl_requirements[]"
                                 value="<?= esc_attr($opt); ?>"
                                 data-byop-requirement
                                 data-title="<?= esc_attr($opt); ?>"
                              >
                              <span class="byop-option__box" aria-hidden="true">
                                 <svg viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3.5 8.5 6.5 11.5 12.5 4.5"/></svg>
                              </span>
                              <span class="byop-option__content">
                                 <span class="byop-option__title"><?= esc_html($opt); ?></span>
                              </span>
                              <span class="byop-option__indicator" aria-hidden="true">&#10003;</span>
                           </label>
                        <?php endforeach; ?>
                     </div>
                  </div>

                  <div class="byop-service-block__col">
                     <h3 class="byop-service-block__heading"><?= esc_html__('Upload Vehicle Photos', 'detailking'); ?> <span class="byop-service-block__optional"><?= esc_html__('(Optional)', 'detailking'); ?></span></h3>

                     <div class="byop-upload">
                        <label class="byop-upload__dropzone" for="byop-vehicle-photos">
                           <input type="file" id="byop-vehicle-photos" name="vehicle_photos[]" multiple accept="image/*" class="byop-upload__input visually-hidden">
                           <span class="byop-upload__icon" aria-hidden="true">
                              <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
                           </span>
                           <span class="byop-upload__text"><?= esc_html__('Click to upload vehicle photos', 'detailking'); ?></span>
                           <span class="byop-upload__filelist" data-byop-filelist></span>
                        </label>
                     </div>

                     <div class="byop-notes-block">
                        <h4 class="byop-notes-block__title"><?= esc_html__('Additional Notes', 'detailking'); ?></h4>
                        <p class="byop-notes-block__subtitle"><?= esc_html__('Tell us more about your wrapping requirements.', 'detailking'); ?></p>
                        <textarea
                           class="byop-notes-block__textarea"
                           name="wrap_notes"
                           rows="3"
                           placeholder="<?= esc_attr__('Describe your wrap idea, colours, finish...', 'detailking'); ?>"
                        ></textarea>
                     </div>
                  </div>
               </div>

            <?php else : ?>
               <!-- Variant A: Standard Service Layout (Left: Packages Radio, Right: Add-ons Checkbox) -->
               <?php
               // Filter packages / add-ons per locked spec:
               $radioPackages   = [];
               $checkboxAddons  = [];

               if ($slug === 'grooming' || $slug === 'detailing') {
                  $radioPackages  = $packages;
                  $checkboxAddons = $genericAddons;
               } elseif ($slug === 'ceramic-pro') {
                  foreach ($packages as $pkg) {
                     if (in_array($pkg->post_name, ['ceramic-pro-wheels-caliper', 'ceramic-pro-car-interior-coating'], true)) {
                        $checkboxAddons[] = $pkg;
                     } else {
                        $radioPackages[] = $pkg;
                     }
                  }
               } elseif ($slug === 'ppf') {
                  // Basic, Full Front, Full Cover in radio; 5 new add-ons in checkbox
                  $ppfAddonSlugs = ['ppf-trunk-edge-kit', 'ppf-door-handle-protection', 'ppf-door-edge-protection', 'ppf-rocker-panel-protection', 'ppf-splash-kit'];
                  foreach ($packages as $pkg) {
                     if (in_array($pkg->post_name, $ppfAddonSlugs, true)) {
                        $checkboxAddons[] = $pkg;
                     } else {
                        $radioPackages[] = $pkg;
                     }
                  }
               } elseif ($slug === 'window-tinting') {
                  $radioPackages  = $packages;
                  $checkboxAddons = [];
               }
               ?>

               <div class="byop-service-block__grid<?= empty($checkboxAddons) ? ' byop-service-block__grid--single' : ' byop-service-block__grid--split'; ?>">
                  <!-- Left: Packages Radio List -->
                  <div class="byop-service-block__col">
                     <h3 class="byop-service-block__heading"><?= esc_html__('Select Your Requirement', 'detailking'); ?></h3>
                     <p class="byop-service-block__subheading"><?= esc_html__('Choose one option below.', 'detailking'); ?></p>

                     <div class="byop-options byop-options--radio" role="radiogroup" aria-label="<?= esc_attr__('Package options', 'detailking'); ?>">
                        <?php foreach ($radioPackages as $pIdx => $pkg) :
                           $price       = (float) $meta->field('package_price', $pkg->ID, 0);
                           $desc        = (string) $meta->field('package_description', $pkg->ID);
                           $isQuoteOnly = ($slug === 'ppf' && $pkg->post_name === 'ppf-full-cover');
                           $dispTitle   = $isQuoteOnly ? __('Full Vehicle Protection (Request Quote)', 'detailking') : get_the_title($pkg);
                           ?>
                           <label class="byop-option byop-option--radio">
                              <input
                                 type="radio"
                                 class="byop-option__input"
                                 name="package_selection_<?= esc_attr($slug); ?>"
                                 value="<?= esc_attr((string) $pkg->ID); ?>"
                                 data-byop-package
                                 data-package-id="<?= esc_attr((string) $pkg->ID); ?>"
                                 data-title="<?= esc_attr($dispTitle); ?>"
                                 data-base-price="<?= esc_attr((string) ($isQuoteOnly ? 0 : $price)); ?>"
                                 data-quote-only="<?= $isQuoteOnly ? 'true' : 'false'; ?>"
                              >
                              <span class="byop-option__radio-mark" aria-hidden="true">
                                 <span class="byop-option__radio-dot"></span>
                              </span>
                              <span class="byop-option__content">
                                 <span class="byop-option__title"><?= esc_html($dispTitle); ?></span>
                                 <?php if ($desc !== '') : ?>
                                    <span class="byop-option__desc"><?= esc_html($desc); ?></span>
                                 <?php endif; ?>
                              </span>
                              <span class="byop-option__price">
                                 <?php if ($isQuoteOnly) : ?>
                                    <span class="byop-option__quote-badge"><?= esc_html__('Request Quote', 'detailking'); ?></span>
                                 <?php elseif ($price > 0) : ?>
                                    <span class="byop-option__amount">$<?= esc_html((string) round($price)); ?></span>
                                 <?php endif; ?>
                              </span>
                           </label>
                        <?php endforeach; ?>
                     </div>
                  </div>

                  <!-- Right: Additional Services Checkbox List (if present) -->
                  <?php if (!empty($checkboxAddons)) : ?>
                     <div class="byop-service-block__col">
                        <h3 class="byop-service-block__heading"><?= esc_html__('Additional', 'detailking'); ?></h3>
                        <p class="byop-service-block__subheading"><?= esc_html__('Select optional add-on services:', 'detailking'); ?></p>

                        <div class="byop-options byop-options--checkbox" role="group" aria-label="<?= esc_attr__('Additional services', 'detailking'); ?>">
                           <?php foreach ($checkboxAddons as $addon) :
                              $addonPrice = (float) $meta->field('package_price', $addon->ID, 0);
                              $addonDesc  = (string) $meta->field('package_description', $addon->ID);
                              ?>
                              <label class="byop-option byop-option--checkbox">
                                 <input
                                    type="checkbox"
                                    class="byop-option__input"
                                    name="addons_selection_<?= esc_attr($slug); ?>[]"
                                    value="<?= esc_attr((string) $addon->ID); ?>"
                                    data-byop-addon
                                    data-addon-id="<?= esc_attr((string) $addon->ID); ?>"
                                    data-title="<?= esc_attr(get_the_title($addon)); ?>"
                                    data-base-price="<?= esc_attr((string) $addonPrice); ?>"
                                 >
                                 <span class="byop-option__box" aria-hidden="true">
                                    <svg viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3.5 8.5 6.5 11.5 12.5 4.5"/></svg>
                                 </span>
                                 <span class="byop-option__content">
                                    <span class="byop-option__title"><?= esc_html(get_the_title($addon)); ?></span>
                                    <?php if ($addonDesc !== '') : ?>
                                       <span class="byop-option__desc"><?= esc_html($addonDesc); ?></span>
                                    <?php endif; ?>
                                 </span>
                                 <?php if ($addonPrice > 0) : ?>
                                    <span class="byop-option__price">
                                       <span class="byop-option__amount">+$<?= esc_html((string) round($addonPrice)); ?></span>
                                    </span>
                                 <?php endif; ?>
                              </label>
                           <?php endforeach; ?>
                        </div>
                     </div>
                  <?php endif; ?>
               </div>
            <?php endif; ?>
         </div>
      <?php endforeach; ?>
   </div>
</div>
