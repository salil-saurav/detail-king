<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Our Services page default content, transcribed from the comp (node 159:2 —
 * see projects/detail-king/figma-data/our-services-spec.md).
 *
 * Hero and CTA only — the filter bar's pills are derived live from the
 * `service_category` terms (so a new service/category needs no defaults
 * update), and the card grid itself is generated from the `dk_service` CPT.
 * Per-service teaser/feature copy lives on the posts (PostTypeMeta_Service),
 * seeded directly in seed/content.php, not here — this class is strictly the
 * page-level copy that does not repeat on another page frame.
 */
final class ServicesDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ═══════════ HERO / PAGE BANNER ═══════════ */
         'hero_title'      => 'Our',
         'hero_title_gold' => 'Services',
         'hero_text'       => "From a signature hand wash to flagship Ceramic Pro protection — every service is built around analysing your vehicle first, then treating it as if it were our own. Christchurch & Dunedin.",
         'hero_bg_image'   => '',

         /* ═══════════ CTA ═══════════ */
         'cta_title'          => 'Not Sure Which',
         'cta_title_gold'     => 'Service You Need?',
         'cta_text'           => "Tell us about your vehicle and we'll recommend the right package — book online in minutes or talk to our team.",
         'cta_primary_text'   => 'Book a Service',
         'cta_primary_url'    => '/contact/',
         'cta_secondary_text' => 'Explore Memberships',
         'cta_secondary_url'  => '/memberships/',
      ];
   }
}
