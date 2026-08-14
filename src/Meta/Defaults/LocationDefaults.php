<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Fallback defaults for dk_location CPT instances.
 */
final class LocationDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         'location_badge'          => 'Studio',
         'location_address'        => '72 Byron Street, Sydenham, Christchurch 8011',
         'location_phone'          => '0800 700 007',
         'location_email'          => 'info@detailking.nz',
         'location_hours'          => 'Mon–Sat · 8:00am – 6:00pm',
         'location_map_embed'      => 'https://maps.google.com/maps?q=72+Byron+Street,+Sydenham,+Christchurch+8011,+New+Zealand&t=&z=15&ie=UTF8&iwloc=&output=embed',
         'location_directions_url' => 'https://www.google.com/maps/dir/?api=1&destination=72+Byron+Street,+Sydenham,+Christchurch+8011',
         'location_image'          => '',
      ];
   }
}
