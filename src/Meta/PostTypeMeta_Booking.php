<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * dk_booking display fields — the enquiry record BookingWidgetService writes
 * for `booking_mode: enquiry` services (Vinyl Wraps). Every field here is
 * readonly: the post type's own `create_posts => do_not_allow` capability
 * already means nobody types one by hand (DetailKingPostTypes.php), so this
 * group exists purely so Shreya/the team can read a submitted enquiry in
 * wp-admin, not to make one editable.
 *
 * instant_booking services never create one of these — they become a real
 * Woo order instead (order item meta, not this CPT).
 */
class PostTypeMeta_Booking extends AbstractPostTypeMeta
{
   protected function postType(): string
   {
      return 'dk_booking';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_booking';
   }

   protected function groupTitle(): string
   {
      return __('Enquiry Details', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'bkg';
   }

   protected function fields(): array
   {
      $ro = ['readonly' => 1];

      return [
         $this->field('booking_service', __('Service', 'detailking'), 'post_object', $ro + [
            'post_type'     => ['dk_service'],
            'return_format' => 'id',
            'ui'            => 1,
         ]),
         $this->field('booking_package', __('Package', 'detailking'), 'post_object', $ro + [
            'post_type'     => ['dk_package'],
            'return_format' => 'id',
            'ui'            => 1,
         ]),
         $this->field('booking_vehicle_size', __('Vehicle Size', 'detailking'), 'text', $ro),
         $this->field('booking_name', __('Full Name', 'detailking'), 'text', $ro),
         $this->field('booking_phone', __('Phone', 'detailking'), 'text', $ro),
         $this->field('booking_email', __('Email', 'detailking'), 'email', $ro),
         $this->field('booking_date', __('Preferred Drop Date', 'detailking'), 'text', $ro),
         $this->field('booking_time', __('Preferred Drop Time', 'detailking'), 'text', $ro),
         $this->field('booking_location', __('Location', 'detailking'), 'text', $ro),
         $this->field('booking_notes', __('Notes', 'detailking'), 'textarea', $ro + ['rows' => 3]),
      ];
   }
}
