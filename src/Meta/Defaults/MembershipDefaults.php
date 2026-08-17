<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Membership page default content, transcribed from the comp (Membership Plans.png).
 */
final class MembershipDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ═══════════ HERO ═══════════ */
         'hero_eyebrow'    => 'MEMBERSHIP · MORE THAN JUST CAR CARE',
         'hero_title'      => 'MEMBERSHIP',
         'hero_title_gold' => 'PLANS',
         'hero_text'       => 'Join our membership program and enjoy exclusive rewards every time you visit. Choose the plan that suits your needs and start saving on your vehicle care.',
         'hero_bg_image'   => '',

         /* ═══════════ PLANS ═══════════ */
         'plans_eyebrow'    => 'CHOOSE YOUR PLAN',
         'plans_title'      => 'SIMPLE, REWARDING',
         'plans_title_gold' => 'MEMBERSHIP',
         'plans_text'       => 'Three straightforward plans, all with member-only pricing and rewards. Cancel anytime.',

         /* ═══════════ LOYALTY REWARDS ═══════════ */
         'loyalty_eyebrow'    => 'LOYALTY REWARDS',
         'loyalty_title'      => 'MORE THAN JUST',
         'loyalty_title_gold' => 'CAR CARE',
         'loyalty_text'       => 'Join our membership program and enjoy exclusive rewards every time you visit.',
         'loyalty_items'      => [
            [
               'loyalty_icon'  => 'clock',
               'loyalty_title' => 'DISCOUNTED WASHES',
               'loyalty_text'  => 'Save on selected services and maintenance packages throughout the year.',
            ],
            [
               'loyalty_icon'  => 'diamond',
               'loyalty_title' => 'EARN LOYALTY POINTS',
               'loyalty_text'  => 'Collect points with every purchase and redeem them for future services and products.',
            ],
            [
               'loyalty_icon'  => 'crown',
               'loyalty_title' => 'REFERRAL REWARDS',
               'loyalty_text'  => 'Invite friends and family and receive rewards when they become customers.',
            ],
         ],

         /* ═══════════ VALUE / CHECKLIST ═══════════ */
         'value_eyebrow'    => 'WHY BECOME A MEMBER?',
         'value_title'      => 'REWARDS THAT',
         'value_title_gold' => 'ADD UP',
         'value_text'       => "Membership pays for itself — every wash, every visit, every referral. Here's what you unlock the moment you join.",
         'value_watermark'  => 'VALUE',
         'value_checklist'  => [
            ['item_text' => 'Save Money on Regular Services'],
            ['item_text' => 'Exclusive Member Offers'],
            ['item_text' => 'Earn Rewards on Every Visit'],
            ['item_text' => 'Priority Access to Promotions'],
            ['item_text' => 'Special Discounts on Products & Services'],
            ['item_text' => 'More Than Just Car Care'],
         ],

         /* ═══════════ CTA ═══════════ */
         'cta_title'          => 'START ENJOYING EXCLUSIVE',
         'cta_title_gold'     => 'MEMBER BENEFITS TODAY',
         'cta_text'           => 'Choose the membership plan that suits your needs and start saving on your vehicle care.',
         'cta_primary_text'   => 'Join Now →',
         'cta_primary_url'    => '#plans',
         'cta_secondary_text' => 'Talk To Our Team',
         'cta_secondary_url'  => '/contact/',
      ];
   }
}
