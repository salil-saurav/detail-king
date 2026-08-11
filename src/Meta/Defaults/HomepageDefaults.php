<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Homepage default content, transcribed verbatim from the comp
 * (pngs/frontpage.png — see projects/detail-king/figma-data/homepage-spec.md).
 *
 * Consumed in three places from this one declaration: ACF `default_value`, the
 * template fallbacks via MetaHelper::fieldOr(), and the repeater seeder for the
 * array values (ACF has no default_value for repeaters).
 *
 * Two deliberate departures from the comp's literal text, both recorded in the
 * spec as comp defects rather than silently "fixed":
 *
 *   - the craft-stat label reads "% Returning Clients" in the comp while its own
 *     value is already "98%". Seeded without the stray percent sign.
 *   - the booking section's gold label reads "BOOK YOUR DETAIL REMIUM CARE" —
 *     missing the P of PREMIUM, and reading like two fragments spliced together.
 *     Seeded as "BOOK YOUR DETAIL · PREMIUM CARE".
 */
final class HomepageDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ═══════════ HERO ═══════════ */
         'hero_badge' => "New Zealand's Premium Detailing Studio",
         // Split so the gold gradient can be applied to the tail without a <span>
         // in the field value — an editor should never have to type markup.
         'hero_heading'      => 'PREMIUM AUTOMOTIVE CARE FOR CARS THAT',
         'hero_heading_gold' => 'DESERVE MORE',
         'hero_text' => 'Detailing, <strong>Ceramic Coatings</strong>, Paint Protection Film, Tinting &amp; <strong>Membership Care Plans</strong> — all under one roof.',
         'hero_cta_primary_text'   => 'Book a Service',
         'hero_cta_primary_url'    => '/contact/',
         'hero_cta_secondary_text' => 'Explore Memberships',
         'hero_cta_secondary_url'  => '/memberships/',
         'hero_video_text' => 'Watch The Craft',
         'hero_video_url'  => '',
         'hero_stats' => [
            ['stat_value' => '2016',   'stat_label' => 'Established Since'],
            ['stat_value' => '4,800+', 'stat_label' => 'Vehicles Perfected'],
            ['stat_value' => '5.0★',   'stat_label' => 'Star Google Rating'],
         ],

         /* ═══════════ SERVICES ═══════════ */
         'services_eyebrow'      => 'Our Expertise',
         'services_heading'      => 'CRAFTED SERVICES FOR',
         'services_heading_gold' => 'EVERY FINISH',
         'services_cta_text'     => 'View All Services',
         'services_cta_url'      => '/our-services/',
         'services_card_link_text' => 'Explore Packages',

         // The comp's 7th card is a promo, not a service — it links to the
         // package builder rather than to a dk_service post.
         'services_promo_title'     => 'CUSTOM BUILD',
         'services_promo_link_text' => 'Build Your Package',
         'services_promo_url'       => '/build-your-package/',

         'services_features' => [
            ['feature_title' => 'CERAMIC SHIELD',   'feature_text' => '9H surface hardness'],
            ['feature_title' => 'SELF-HEALING PPF', 'feature_text' => 'Heals swirls with heat'],
            ['feature_title' => 'HYDROPHOBIC GLASS', 'feature_text' => 'Rain simply rolls away'],
         ],

         /* ═══════════ VIDEO ═══════════ */
         'video_eyebrow'      => 'Inside the Studio',
         'video_heading'      => 'WATCH OBSESSION',
         'video_heading_gold' => 'IN MOTION',
         'video_badge'        => 'REC · STUDIO FILM',
         'video_meta'         => '04K · 60FPS',
         'video_caption'      => 'PORSCHE 911 — CERAMIC PRO GOLD',
         'video_url'          => '',
         'video_watermark'    => 'THE CRAFT',
         'craft_stats' => [
            ['stat_value' => '10+',    'stat_label' => 'Years of Craft'],
            ['stat_value' => '4,800+', 'stat_label' => 'Vehicles Perfected'],
            ['stat_value' => '98%',    'stat_label' => 'Returning Clients'],
            ['stat_value' => '320+',   'stat_label' => 'Supercars Protected'],
         ],

         /* ═══════════ WHY US ═══════════ */
         'why_eyebrow'      => 'The Standard',
         'why_heading'      => 'WHY',
         'why_heading_gold' => 'DETAIL KING',
         'why_watermark'    => 'WHY US',
         'why_image_badge'  => 'SINCE 2016',
         'why_features_left' => [
            ['feature_icon_glyph' => 'crown',   'feature_title' => 'ESTABLISHED SINCE 2016', 'feature_text' => 'A decade of trusted craft.'],
            ['feature_icon_glyph' => 'sparkle', 'feature_title' => 'CERTIFIED EXPERTS',      'feature_text' => 'Trained, accredited specialists.'],
            ['feature_icon_glyph' => 'diamond', 'feature_title' => 'PREMIUM PRODUCTS',       'feature_text' => 'Only flagship-grade chemistry.'],
         ],
         'why_features_right' => [
            ['feature_icon_glyph' => 'hexagon', 'feature_title' => 'LUXURY CAR SPECIALISTS', 'feature_text' => 'Exotics handled daily.'],
            ['feature_icon_glyph' => 'gear',    'feature_title' => 'ADVANCED EQUIPMENT',     'feature_text' => 'Studio-grade tools & lighting.'],
            ['feature_icon_glyph' => 'spark',   'feature_title' => 'TAILORED CARE',          'feature_text' => 'Every package, built for you.'],
         ],

         /* ═══════════ BEFORE / AFTER ═══════════ */
         'ba_eyebrow'      => 'Proof, Not Promises',
         'ba_heading'      => 'BEFORE &',
         'ba_heading_gold' => 'AFTER',
         'ba_text'         => 'Drag the divider and watch years of wear disappear. Real transformations on real luxury vehicles — no filters, just finish.',
         'ba_label_before' => 'Before',
         'ba_label_after'  => 'After',
         'ba_presets' => [
            ['preset_label' => 'Paint Correction'],
            ['preset_label' => 'Swirl Removal'],
            ['preset_label' => 'Gloss Restoration'],
         ],

         /* ═══════════ MEMBERSHIP ═══════════ */
         'membership_eyebrow'      => 'The Detail King Club',
         'membership_heading'      => 'MEMBERSHIP',
         'membership_heading_gold' => 'PLANS',
         'membership_text'         => 'Recurring care for owners who never settle — billed monthly, cancel anytime.',
         'membership_watermark'    => 'THE CLUB',
         'membership_footnote'     => 'All plans renew automatically · Pause or cancel anytime from your account',

         /* ═══════════ SHOP ═══════════ */
         'shop_eyebrow'      => 'Take It Home',
         'shop_heading'      => 'SHOP FEATURED',
         'shop_heading_gold' => 'PRODUCTS',
         'shop_cta_text'     => 'Shop All Products',
         'shop_watermark'    => 'SHOP',

         /* ═══════════ TESTIMONIALS ═══════════ */
         'reviews_eyebrow'      => 'Verified on Google',
         'reviews_heading'      => 'OWNERS WHO',
         'reviews_heading_gold' => 'TRUST US',
         'reviews_card_title'   => 'Google Reviews',
         'reviews_card_note'    => 'Based on 380+ reviews',

         /* ═══════════ INSTAGRAM ═══════════ */
         'instagram_eyebrow'      => '@detailking.nz',
         'instagram_heading'      => 'FOLLOW THE',
         'instagram_heading_gold' => 'FINISH',
         'instagram_cta_text'     => 'Follow on Instagram',
         'instagram_cta_url'      => 'https://instagram.com/detailking.nz',

         /* ═══════════ BOOKING ═══════════ */
         'booking_eyebrow'      => 'Reserve Your Slot',
         'booking_heading'      => 'READY TO BOOK YOUR',
         'booking_heading_gold' => 'NEXT DETAIL?',
         'booking_text'         => "Choose your service, vehicle and time — pay securely online and we'll take care of the rest. Most bookings confirmed within the hour.",
         'booking_label'        => 'BOOK YOUR DETAIL · PREMIUM CARE',
         'booking_form_title'      => 'BOOK IN',
         'booking_form_title_gold' => 'MINUTES',
         'booking_submit_text'     => 'Book Appointment',

         /* ═══════════ FAQ ═══════════ */
         'faq_eyebrow'       => 'Good to Know',
         'faq_heading'       => 'QUESTIONS,',
         'faq_heading_gold'  => 'ANSWERED',
         'faq_watermark'     => 'FAQ',
         'faq_panel_lead'    => 'Still curious?',
         'faq_panel_text'    => 'Our team replies within the hour — call, message, or drop by the studio lounge.',
      ];
   }
}
