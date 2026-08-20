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
         <div class="auth-perk-item" data-animate>
            <div class="auth-perk-item__badge" aria-hidden="true">
               <?php if ($perk['icon'] === 'bookings') : ?>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                     <path d="M6 2.5 9.5 6 6 9.5 2.5 6 6 2.5z"/>
                     <path d="M18 2.5 21.5 6 18 9.5 14.5 6 18 2.5z"/>
                     <path d="M6 14.5 9.5 18 6 21.5 2.5 18 6 14.5z"/>
                     <path d="M18 14.5 21.5 18 18 21.5 14.5 18 18 14.5z"/>
                  </svg>
               <?php elseif ($perk['icon'] === 'perks') : ?>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                     <path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"/>
                     <path d="M4 20h16v2H4v-2z"/>
                  </svg>
               <?php else : ?>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                     <path d="M12 2.5 21.5 12 12 21.5 2.5 12 12 2.5z"/>
                     <path d="M12 8 16 12 12 16 8 12 12 8z"/>
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
         <div class="auth-stat-item" data-animate>
            <span class="auth-stat-item__value text-gold-gradient"><?= esc_html($stat['value']); ?></span>
            <span class="auth-stat-item__label"><?= esc_html($stat['label']); ?></span>
         </div>
      <?php endforeach; ?>
   </div>
</div>
