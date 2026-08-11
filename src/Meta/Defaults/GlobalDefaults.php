<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Site-wide default content, seeded verbatim from the comp.
 *
 * Everything here appears on more than one page frame, which is precisely the
 * test for "global option" rather than "page field": duplicating the footer
 * address or the review summary onto each page guarantees they drift apart.
 *
 * NOTE — the comp contradicted itself about location: the footer on every frame
 * read "12 Showroom Lane, Auckland, NZ" while the Gallery hero eyebrow and the
 * Contact page ("VISIT ONE OF OUR TWO STUDIOS") read Christchurch & Dunedin.
 * Client resolved 2026-08-11: Christchurch (72 Byron Street, Sydenham,
 * Christchurch 8011) is the headquarters, Dunedin (31 Otaki Street, South
 * Dunedin, Dunedin 9012) is the branch. `contact_address` now carries the
 * Christchurch HQ address; the Contact page's two-studio cards (built in a
 * later pass) will source both from `dk_location`.
 */
final class GlobalDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ── Header ─────────────────────────────────────────────── */
         'header_brand_name'  => 'Detail King',
         'header_account_text' => 'Login / My Account',
         'header_cta_text'    => 'Book Now',
         'header_cta_url'     => '/contact/',

         /* ── Footer ─────────────────────────────────────────────── */
         'footer_about_text'  => 'Premium automotive care for cars that deserve more. Detailing, protection and membership plans — all under one roof since 2016.',
         'footer_watermark'   => 'DETAIL KING',
         'footer_copyright'   => '© {year} Detail King. All rights reserved.',
         'footer_legal_text'  => 'Privacy · Terms · Crafted for the obsessed',

         'footer_social_links' => [
            ['social_label' => 'Facebook',  'social_icon' => 'facebook',  'social_url' => '#'],
            ['social_label' => 'YouTube',   'social_icon' => 'youtube',   'social_url' => '#'],
            ['social_label' => 'Instagram', 'social_icon' => 'instagram', 'social_url' => '#'],
            ['social_label' => 'LinkedIn',  'social_icon' => 'linkedin',  'social_url' => '#'],
         ],

         /* ── Contact block — footer + Contact page + booking form ── */
         'contact_address' => '72 Byron Street, Sydenham, Christchurch 8011',
         'contact_phone'   => '+64 9 000 0000',
         'contact_email'   => 'hello@detailking.nz',
         'contact_hours'   => 'Mon–Sat · 8:00am – 6:00pm',

         /* ── Marquee ticker — homepage, About and every service page ── */
         'ticker_text' => 'SINCE 2016 ◆ CERTIFIED PROFESSIONALS ◆ PREMIUM PRODUCTS ◆ LUXURY VEHICLE SPECIALISTS',

         /* ── Review summary — homepage and service pages ─────────── */
         'reviews_average' => '4.9',
         'reviews_count'   => '380+',

         /* ── Social proof / Instagram ────────────────────────────── */
         'instagram_handle' => '@DETAILING.NZ',

         /* ── Booking widget — identical heading on every service page ── */
         'booking_widget_eyebrow'    => 'Build Your Booking',
         'booking_widget_title'      => 'Choose &',
         'booking_widget_title_gold' => 'Book In Minutes',
         'vehicle_sizes' => [
            ['size_label' => 'Small (hatch / coupe)',    'size_multiplier' => 1.0],
            ['size_label' => 'Medium (sedan / wagon)',   'size_multiplier' => 1.15],
            ['size_label' => 'Large (SUV / ute / van)',  'size_multiplier' => 1.35],
            ['size_label' => 'Supercar / Exotic',        'size_multiplier' => 1.6],
         ],
      ];
   }
}
