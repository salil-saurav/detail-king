<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Service fields — the Our Services grid card (General tab) plus the full
 * single-service page (Hero/Intro/Booking/CTA), one shared template for all 7
 * (see figma-data/service-single-spec.md).
 *
 * `service_short_name` exists because the comp uses two different names for the
 * same service: the page heading reads "Auto Detailing" while the homepage card,
 * the footer menu and the filter pills all read "Detailing". Deriving one from the
 * other by truncation would be guesswork ("Paint Protection Film (PPF)" becomes
 * "PPF", not "Paint"), so it is an explicit field that falls back to the title.
 *
 * No defaultsSlug(): unlike a page group, every field here is genuinely unique
 * per service (there is no single "the" default hero title), so copy is seeded
 * directly per post in seed/content.php rather than through a DefaultsProvider.
 */
class PostTypeMeta_Service extends AbstractPostTypeMeta
{
   protected function postType(): string
   {
      return 'dk_service';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_service';
   }

   protected function groupTitle(): string
   {
      return __('Service Details', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'svc';
   }

   protected function fields(): array
   {
      $img = $this->imageArgs();

      return [
         /* ═══════════ GENERAL (Our Services grid card) ═══════════ */
         $this->tab('tab_general', __('General', 'detailking')),
         $this->field('service_short_name', __('Short Name', 'detailking'), 'text', [
            'instructions' => __('Used on cards, menus and filter pills — e.g. "Detailing" for "Auto Detailing". Falls back to the full title.', 'detailking'),
         ]),
         $this->field('booking_mode', __('Booking Mode', 'detailking'), 'select', [
            'choices' => [
               'instant_booking' => __('Instant booking', 'detailking'),
               'enquiry'         => __('Enquiry only', 'detailking'),
            ],
            'default_value' => 'instant_booking',
            'instructions'  => __('Vinyl Wraps is enquiry-only in the comp; the rest book instantly. The comp does not visually differentiate the two on the single-service page — same booking widget either way.', 'detailking'),
         ]),
         $this->field('service_teaser', __('Card Teaser', 'detailking'), 'textarea', [
            'rows'         => 4,
            'instructions' => __('Shown under the heading on the Our Services grid card.', 'detailking'),
         ]),
         $this->repeater('service_features', __('Card Features', 'detailking'), [
            $this->field('feature_text', __('Feature', 'detailking')),
         ], ['button_label' => __('Add Feature', 'detailking')]),
         $this->field('service_grid_caption', __('Grid Card Photo Caption', 'detailking'), 'text', [
            'instructions' => __('Optional. Renders a numbered badge + caption over the card photo on the Our Services grid — the comp only uses this on one card (Add-On Services). Leave empty everywhere else.', 'detailking'),
         ]),

         /* ═══════════ HERO (single-service page-banner) ═══════════ */
         $this->tab('tab_hero', __('Hero', 'detailking')),
         $this->field('hero_eyebrow', __('Eyebrow Badge', 'detailking'), 'text', [
            'instructions' => __('e.g. "Window Tinting · Build & Book Online".', 'detailking'),
         ]),
         $this->field('hero_title', __('Title', 'detailking')),
         $this->field('hero_title_gold', __('Title — gold word', 'detailking'), 'text', [
            'instructions' => __('Joined inline after Title, e.g. "Window" + "Tinting" — same as Our Services, not a forced line break.', 'detailking'),
         ]),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),

         /* ═══════════ INTRO ("Why X?") ═══════════ */
         $this->tab('tab_intro', __('Intro', 'detailking')),
         $this->field('intro_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('intro_title', __('Title', 'detailking')),
         $this->field('intro_title_gold', __('Title — gold tail', 'detailking')),
         $this->field('intro_lead', __('Lead Paragraph', 'detailking'), 'textarea', [
            'rows'         => 2,
            'instructions' => __('The darker, larger line directly under the heading.', 'detailking'),
         ]),
         $this->field('intro_text', __('Body Paragraph', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('intro_image', __('Photo', 'detailking'), 'image', $img),
         $this->field('intro_watermark', __('Background Watermark', 'detailking'), 'text', [
            'instructions' => __('Large outlined word behind the copy column. Optional — several comp frames leave it blank.', 'detailking'),
         ]),

         /* ═══════════ BOOKING WIDGET ═══════════ */
         $this->tab('tab_booking', __('Booking', 'detailking')),
         $this->field('booking_intro_text', __('Widget Subtitle', 'detailking'), 'textarea', [
            'rows'         => 2,
            'instructions' => __('Under the shared "Choose & Book In Minutes" heading (Global Options → Booking Widget). This line is per-service, e.g. "Select a tint package, then add your car and drop-off details."', 'detailking'),
         ]),

         /* ═══════════ CTA ═══════════ */
         $this->tab('tab_cta', __('CTA', 'detailking')),
         $this->field('cta_title', __('Title', 'detailking')),
         $this->field('cta_title_gold', __('Title — gold line', 'detailking')),
         $this->field('cta_text', __('Text', 'detailking'), 'textarea', ['rows' => 2]),
         ...$this->linkFields('cta_primary', __('Primary CTA', 'detailking')),
         ...$this->linkFields('cta_secondary', __('Secondary CTA', 'detailking')),
      ];
   }
}
