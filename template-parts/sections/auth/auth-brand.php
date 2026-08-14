<?php

/**
 * Authentication left brand & perks column.
 *
 * Renders the brand authority, member perks value propositions, and
 * live track record stats. Shared between Login and Signup pages.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$mode = $args['mode'] ?? 'login';
$isLogin = ($mode === 'login');

$heading1 = $isLogin
   ? 'WELCOME BACK TO <br>THE <span class="text-gold-gradient">KING’S GARAGE</span>'
   : 'WELCOME TO <br>THE <span class="text-gold-gradient">KING’S GARAGE</span>';

$description = $isLogin
   ? 'Sign in to manage bookings, track your <strong class="text-gold">membership plan</strong>, view service history and unlock <strong class="text-gold">members-only</strong> pricing on detailing & protection.'
   : 'Join to manage bookings, track your <strong class="text-gold">membership plan</strong>, view service history and unlock <strong class="text-gold">members-only</strong> pricing on detailing & protection.';

$perks = [
   [
      'icon'     => 'bookings',
      'title'    => 'Manage Your Bookings',
      'subtitle' => 'Reschedule, review and rebook in a tap.',
   ],
   [
      'icon'     => 'perks',
      'title'    => 'Membership Perks',
      'subtitle' => 'Priority slots and exclusive member rates.',
   ],
   [
      'icon'     => 'history',
      'title'    => 'Full Service History',
      'subtitle' => 'Every detail, coating and PPF on record.',
   ],
];

$stats = [
   [
      'value' => '2016',
      'label' => 'Trusted Since',
   ],
   [
      'value' => '4,800+',
      'label' => 'Vehicles Perfected',
   ],
   [
      'value' => '5.0★',
      'label' => 'Google Rating',
   ],
];
?>
<div class="auth-brand" data-animate="fade-right">
   <!-- Eyebrow Badge -->
   <div class="auth-eyebrow">
      <span class="auth-eyebrow__dot" aria-hidden="true"></span>
      <span class="auth-eyebrow__text">MEMBERS’ LOUNGE</span>
   </div>

   <!-- Main Heading -->
   <h1 class="auth-brand__heading">
      <?= $heading1; ?>
   </h1>

   <!-- Subtitle / Intro -->
   <p class="auth-brand__desc">
      <?= $description; ?>
   </p>

   <!-- Perks List -->
   <div class="auth-perks">
      <?php foreach ($perks as $perk) : ?>
         <div class="auth-perk-item">
            <div class="auth-perk-item__badge" aria-hidden="true">
               <?php if ($perk['icon'] === 'bookings') : ?>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                     <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                     <line x1="16" y1="2" x2="16" y2="6"/>
                     <line x1="8" y1="2" x2="8" y2="6"/>
                     <line x1="3" y1="10" x2="21" y2="10"/>
                     <path d="m9 16 2 2 4-4"/>
                  </svg>
               <?php elseif ($perk['icon'] === 'perks') : ?>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                     <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"/>
                     <path d="M4 20h16v2H4v-2z"/>
                  </svg>
               <?php else : ?>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                     <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                     <path d="m9 12 2 2 4-4"/>
                  </svg>
               <?php endif; ?>
            </div>
            <div class="auth-perk-item__content">
               <h4 class="auth-perk-item__title"><?= esc_html($perk['title']); ?></h4>
               <p class="auth-perk-item__subtitle"><?= esc_html($perk['subtitle']); ?></p>
            </div>
         </div>
      <?php endforeach; ?>
   </div>

   <!-- Stats Row -->
   <div class="auth-stats">
      <?php foreach ($stats as $stat) : ?>
         <div class="auth-stat-item">
            <span class="auth-stat-item__value text-gold-gradient"><?= esc_html($stat['value']); ?></span>
            <span class="auth-stat-item__label"><?= esc_html($stat['label']); ?></span>
         </div>
      <?php endforeach; ?>
   </div>
</div>
