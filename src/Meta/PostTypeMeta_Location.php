<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Studio location fields for dk_location CPT (Contact page studio cards, maps, and booking dropdowns).
 */
class PostTypeMeta_Location extends AbstractPostTypeMeta
{
   protected function postType(): string
   {
      return 'dk_location';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_location';
   }

   protected function groupTitle(): string
   {
      return __('Location Details', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'location';
   }

   protected function defaultsSlug(): ?string
   {
      return 'location';
   }

   protected function fields(): array
   {
      $img = $this->imageArgs();

      return [
         $this->field('location_badge', __('Badge / Tag', 'detailking'), 'text', [
            'instructions' => __('Shown on the card pill badge — e.g. "Headquarter", "Branch".', 'detailking'),
         ]),
         $this->field('location_address', __('Address', 'detailking'), 'textarea', [
            'rows'         => 3,
            'instructions' => __('Physical studio address.', 'detailking'),
         ]),
         $this->field('location_phone', __('Phone', 'detailking'), 'text'),
         $this->field('location_email', __('Email', 'detailking'), 'email'),
         $this->field('location_hours', __('Opening Hours', 'detailking'), 'text', [
            'instructions' => __('e.g. "Mon–Sat · 8:00am – 6:00pm".', 'detailking'),
         ]),
         $this->field('location_map_embed', __('Google Maps Embed URL', 'detailking'), 'textarea', [
            'rows'         => 3,
            'instructions' => __('Plain iframe embed URL for the Google Map (e.g. https://maps.google.com/maps?...).', 'detailking'),
         ]),
         $this->field('location_directions_url', __('Directions URL', 'detailking'), 'text', [
            'instructions' => __('External link to Google Maps directions.', 'detailking'),
         ]),
         $this->field('location_image', __('Studio Photo', 'detailking'), 'image', $img),
      ];
   }
}
