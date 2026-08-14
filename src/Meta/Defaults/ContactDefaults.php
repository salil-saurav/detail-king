<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Contact page default content, transcribed verbatim from the Figma comp
 * (node 182:6858 — see figma-data/contact.txt).
 */
final class ContactDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ═══════════ HERO / BANNER ═══════════ */
         'hero_eyebrow'           => 'GET CONNECTED',
         'hero_title'             => 'Contact',
         'hero_title_gold'        => 'Us',
         'hero_text'              => "Talk, mail or message us to discuss how we can help you with\nour incredible services.",
         'hero_bg_image'          => '',

         /* ═══════════ CONTACT FORM ═══════════ */
         'form_title'             => 'Get In',
         'form_title_gold'        => 'Touch',
         'form_text'              => 'Fill in the form and our team will get back to you shortly.',
         'form_submit_text'       => 'Submit',

         /* ═══════════ DIRECT INFO ═══════════ */
         'direct_title'           => 'Reach Us Directly',
         'direct_phone_label'     => 'PHONE',
         'direct_phone'           => '0800 700 007',
         'direct_email_label'     => 'EMAIL',
         'direct_email'           => 'info@detailking.nz',
         'direct_hours_label'     => 'HOURS',
         'direct_hours'           => 'Mon–Sat · 8:00am – 6:00pm',
         'direct_locations_title' => 'Our Locations',

         /* ═══════════ STUDIOS / LOCATIONS SECTION ═══════════ */
         'studios_eyebrow'        => 'FIND YOUR LOCATION',
         'studios_title'          => 'Visit One Of Our',
         'studios_title_gold'     => "Two\nStudios",
         'studios_text'           => "Christchurch headquarters and our Dunedin branch — drop in or book\nahead.",
      ];
   }
}
