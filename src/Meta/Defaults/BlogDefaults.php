<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Blog index (the posts page) default copy and settings.
 * Sourced directly from Figma frame 180:6257 ("The Blog").
 */
final class BlogDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ═══════════ HERO / PAGE BANNER ═══════════ */
         'hero_eyebrow'      => 'THE DETAIL KING JOURNAL',
         'hero_title'        => 'The',
         'hero_title_gold'   => 'Blog',
         'hero_text'         => 'Tips, guides and insights from the team — paint care, protection, detailing know-how and everything in between.',
         'hero_bg_image'     => '',

         /* ═══════════ CTA BANNER ═══════════ */
         'cta_title'          => 'Ready To Treat',
         'cta_gold'           => 'Your Car?',
         'cta_text'           => 'Reading is great — results are better. Book a service or talk to our team today.',
         'cta_primary_text'   => 'Book Now',
         'cta_primary_url'    => '/contact/',
         'cta_secondary_text' => 'Explore Services',
         'cta_secondary_url'  => '/services/',
      ];
   }
}
