<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Build Your Package page default copy, transcribed directly from the
 * Figma comp (node 242:5047, "Build Your Package.png").
 *
 * Hero and Help CTA copy only — vehicle sizes come from GlobalFields,
 * services from the dk_service CPT, packages and add-ons from dk_package.
 */
final class BuildPackageDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ═══════════ HERO / BANNER ═══════════ */
         'hero_eyebrow'       => 'CUSTOM BUILD · TAILORED TO YOUR CAR',
         'hero_title'         => 'BUILD YOUR',
         'hero_title_gold'    => 'PACKAGE',
         'hero_text'          => "Build a package tailored to your vehicle and requirements \u{2014} pick your vehicle, choose a service, personalise it, and we'll take care of the rest.",
         'hero_bg_image'      => '',

         /* ═══════════ NEED A HAND? (HELP CTA) ═══════════ */
         'help_eyebrow'       => 'NEED A HAND?',
         'help_title'         => 'NOT SURE WHICH PACKAGE IS',
         'help_title_gold'    => 'RIGHT FOR YOUR VEHICLE?',
         'help_text'          => "If our package options don't quite match what you're looking for, get in touch with our team directly.\n\nWhether you need a customised combination of services, have specific requirements, or want advice on the best solution for your vehicle, we're here to help. We'll work with you to create a package tailored to your vehicle, goals, and budget.",
         'help_primary_text'  => 'Book A Consultation',
         'help_primary_url'   => '/contact/',
         'help_image'         => '',
      ];
   }
}
