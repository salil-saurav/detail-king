<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Gallery page default content, transcribed from the Figma comp (node 179:6033).
 *
 * Provides defaults for the hero page-banner, gallery empty-state message,
 * and the closing CTA banner.
 */
final class GalleryDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ═══════════ HERO / PAGE BANNER ═══════════ */
         'hero_eyebrow'    => 'OUR WORK · CHRISTCHURCH & DUNEDIN',
         'hero_title'      => 'The',
         'hero_title_gold' => 'Gallery',
         'hero_text'       => "A look at the results we're proud of — detailing, ceramic coatings,\ngrooming, tinting, interior rejuvenation and custom wraps.",
         'hero_bg_image'   => '',

         /* ═══════════ GALLERY / FILTERS ═══════════ */
         'filter_all_label' => 'All Work',
         'empty_text'       => 'No gallery items found in this category.',

         /* ═══════════ CTA ═══════════ */
         'cta_title'          => 'Like What',
         'cta_title_gold'     => 'You See?',
         'cta_text'           => "Let's make your car the next one in the gallery. Book a service\nor talk to our team today.",
         'cta_primary_text'   => 'Book Now',
         'cta_primary_url'    => '/contact/',
         'cta_secondary_text' => 'Explore Services',
         'cta_secondary_url'  => '/services/',
      ];
   }
}
