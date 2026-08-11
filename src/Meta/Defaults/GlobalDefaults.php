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
 * NOTE — the comp contradicts itself about location. The footer on every frame
 * reads "12 Showroom Lane, Auckland, NZ", while the Gallery hero eyebrow reads
 * "OUR WORK · CHRISTCHURCH & DUNEDIN" and the Contact page shows two studios in
 * Christchurch and Dunedin. Seeded with the footer's literal copy; this is a
 * client decision, not something to quietly reconcile.
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
         'contact_address' => '12 Showroom Lane, Auckland, NZ',
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
      ];
   }
}
