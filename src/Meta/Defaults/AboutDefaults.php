<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * About page default content, transcribed from the comp (node 128:2 — see
 * projects/detail-king/figma-data/about-spec.md).
 *
 * Everything here is page-level: none of this copy repeats on another page
 * frame. The stats repeater in particular has its own numbers (4,134+ vehicles
 * vs the homepage's own craft-stats set) — same test as HomepageDefaults, same
 * conclusion each time it's checked.
 *
 * Equipment card icons are pre-cropped PNGs lifted straight from the export
 * (assets/images/about/icons/icon-{slug}.png, via `equip_icon`) rather than
 * the house SVG glyph set — the comp's icons are specific system-font
 * (Segoe UI Symbol) glyphs with no equivalent shape in components/glyph.php.
 * `equip_glyph` stays as a fallback for any card an editor adds beyond these
 * four, which won't have a matching cropped asset.
 */
final class AboutDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ═══════════ HERO / PAGE BANNER ═══════════ */
         'hero_title'      => 'We Make The World A',
         'hero_title_gold' => 'Shinier Place',
         'hero_text'       => 'One car at a time — premium auto detailing in Christchurch & Dunedin, crafted by specialists since 2016.',
         'hero_bg_image'   => '',

         /* ═══════════ WHO WE ARE ═══════════ */
         'who_eyebrow'    => 'Who We Are',
         'who_title'      => 'We make the world a shinier place —',
         'who_title_gold' => 'one car at a time.',
         'who_text_1'     => "Your car is arguably one of the most cherished possessions you have. No wonder you would wager anything but not your automobile. And yes, you would always want to ensure your car receives the best detailing services — and that's where Detail King Automotive steps in.",
         'who_text_2'     => 'Auto detailing by our standards is the practice of carrying out extremely thorough polishing and cleaning of an automobile, both interior and exterior, to produce a high-quality result. Our service revolves around aesthetics and extends to protection & sealant, minor paint repair, surface restoration and the careful cleaning of the parts most often ignored.',
         'who_image'      => '',
         'who_badge_year' => 'Since 2016',
         'who_badge_text' => 'Trusted automotive care',

         /* ═══════════ OUR STORY ═══════════ */
         'story_eyebrow'    => 'About Detail King Auto Concept',
         'story_title'      => 'A Sophisticated,',
         'story_title_gold' => 'Professional Approach',
         'story_text_1'     => "Established in 2016, Detail King brings a sophisticated and professional approach to every auto detailing project we touch. Our outfit is owned and staffed by experts who are readily available, working on each customer's car every day. We care deeply about the aesthetic appeal of your car and want you to receive top-of-the-line treatment.",
         'story_text_2'     => "Our premium services are structured around a full hand wash — our cleaners are delighted at every opportunity to get their hands dirty. We've added specialty services to be a one-stop shop for all your detailing needs at the most competitive rate in the market: high-speed polishing, waxing and paint protection, window tinting and engine shampooing.",
         'story_text_3'     => 'We pride ourselves on the versatility of our staff, and we put our money where our mouth is. Comprehensive solutions, affordable services, world-class products — that is our promise.',
         'story_image'      => '',
         'story_watermark'  => 'EST. 2016',

         /* ═══════════ EQUIPMENT ═══════════ */
         'equip_eyebrow'   => 'Equipped For Excellence',
         'equip_title'     => 'Show-Quality,',
         'equip_title_gold' => 'Every Time',
         'equip_text'      => 'From rotary buffing machines to steam shampooing, we reach every area of your car for a pristine, show-quality finish.',
         'equip_watermark' => 'THE CRAFT',
         'about_equipment' => [
            ['equip_icon' => 'pro-equipment',        'equip_glyph' => 'gear',    'equip_title' => 'Pro Equipment',       'equip_text' => 'Rotary buffing machines, high-end pads, stain extraction and steam shampooing.'],
            ['equip_icon' => 'high-speed-polishing', 'equip_glyph' => 'sparkle', 'equip_title' => 'High-Speed Polishing', 'equip_text' => 'Waxing and paint protection that restores deep, lasting gloss.'],
            ['equip_icon' => 'detail-brushes',       'equip_glyph' => 'hexagon', 'equip_title' => 'Detail Brushes',       'equip_text' => "Every texture and size to reach every contour of your car's fabric."],
            ['equip_icon' => 'world-class-products', 'equip_glyph' => 'diamond', 'equip_title' => 'World-Class Products', 'equip_text' => 'Affordable service without compromising on premium chemistry.'],
         ],

         /* ═══════════ OUR APPROACH ═══════════ */
         'approach_eyebrow'    => 'Our Approach',
         'approach_title'      => 'We Check Before',
         'approach_title_gold' => 'We Clean',
         'approach_text_1'     => "We don't just jump into cleaning your car without a background check. We begin by analysing your car's interior and exterior paintwork and condition to determine the best products and the right grooming method to achieve the best results.",
         'approach_text_2'     => "The review takes only a few minutes, but the detailing itself can take up to three hours — we employ a painstaking approach when handling our clients' most cherished investment.",
         'approach_image'      => '',
         'about_steps' => [
            ['step_number' => '01', 'step_title' => 'Inspect', 'step_text' => 'We analyse paintwork and condition inside and out.'],
            ['step_number' => '02', 'step_title' => 'Plan',    'step_text' => 'We select the right products and grooming method for your car.'],
            ['step_number' => '03', 'step_title' => 'Detail',  'step_text' => 'A painstaking, thorough service — often up to three hours.'],
            ['step_number' => '04', 'step_title' => 'Protect', 'step_text' => 'Sealant and protection so the finish lasts well beyond the studio.'],
         ],

         /* ═══════════ STATS ═══════════ */
         'about_stats' => [
            ['stat_value' => '2016',   'stat_label' => 'Established Since'],
            ['stat_value' => '4,134+', 'stat_label' => 'Vehicles Perfected'],
            ['stat_value' => '2HR',    'stat_label' => 'Hour Painstaking Detail'],
            ['stat_value' => '4.0★',   'stat_label' => 'Star Google Rating'],
         ],

         /* ═══════════ CTA ═══════════ */
         'cta_title'           => 'Ready To Give Your Car',
         'cta_title_gold'      => 'The Royal Treatment?',
         'cta_text'            => 'Comprehensive auto detailing, affordable service, world-class products. Book your service in minutes.',
         'cta_primary_text'    => 'Book a Service',
         'cta_primary_url'     => '/contact/',
         'cta_secondary_text'  => 'Explore Services',
         'cta_secondary_url'   => '/services/',
      ];
   }
}
