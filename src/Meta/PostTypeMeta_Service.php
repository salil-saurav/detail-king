<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Service fields.
 *
 * Only the shared/cross-page fields are here for now — the full service-page
 * group (hero, intro, booking mode, CTA) lands with the service template.
 *
 * `service_short_name` exists because the comp uses two different names for the
 * same service: the page heading reads "Auto Detailing" while the homepage card,
 * the footer menu and the filter pills all read "Detailing". Deriving one from the
 * other by truncation would be guesswork ("Paint Protection Film (PPF)" becomes
 * "PPF", not "Paint"), so it is an explicit field that falls back to the title.
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
      return [
         $this->field('service_short_name', __('Short Name', 'detailking'), 'text', [
            'instructions' => __('Used on cards, menus and filter pills — e.g. "Detailing" for "Auto Detailing". Falls back to the full title.', 'detailking'),
         ]),
         $this->field('booking_mode', __('Booking Mode', 'detailking'), 'select', [
            'choices' => [
               'instant_booking' => __('Instant booking', 'detailking'),
               'enquiry'         => __('Enquiry only', 'detailking'),
            ],
            'default_value' => 'instant_booking',
            'instructions'  => __('Vinyl Wraps is enquiry-only in the comp; the rest book instantly.', 'detailking'),
         ]),
      ];
   }
}
