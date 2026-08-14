<?php

/**
 * Template Name: Sign Up Page
 *
 * Dedicated registration page for native WordPress user account creation.
 * Two-column desktop layout (Welcome/Brand Perks left, Signup Card right).
 *
 * Figma frame: Signup #130:1013
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Services\Account\AuthService;

if (!defined('ABSPATH')) exit;

$authError = AuthService::$authError;
$authSuccess = AuthService::$authSuccess;

get_header();
?>
<main id="primary" class="site-main auth-page auth-page--signup">
   <section class="auth-section">
      <div class="auth-section__bg" aria-hidden="true"></div>

      <div class="container-dk auth-container">
         <div class="auth-grid">
            <!-- Left Column: Welcome & Brand Perks -->
            <div class="auth-grid__left">
               <?php get_template_part('template-parts/sections/auth/auth-brand', null, ['mode' => 'signup']); ?>
            </div>

            <!-- Right Column: Signup Card -->
            <div class="auth-grid__right">
               <div class="auth-card" data-animate="fade-left">
                  <div class="auth-card__divider-top" aria-hidden="true"></div>

                  <div class="auth-card__header">
                     <h2 class="auth-card__title">MY <span class="text-gold-gradient">ACCOUNT</span></h2>
                     <p class="auth-card__subtitle">Join the King’s Garage in under a minute.</p>
                  </div>

                  <!-- Segmented Tab Switcher -->
                  <div class="auth-switcher" role="tablist" aria-label="Account Action">
                     <a href="<?= esc_url(home_url('/login/')); ?>" class="auth-switcher__tab" role="tab" aria-selected="false">
                        Sign In
                     </a>
                     <span class="auth-switcher__tab auth-switcher__tab--active" role="tab" aria-selected="true">
                        Create Account
                     </span>
                  </div>

                  <!-- Alerts -->
                  <?php if (!empty($authError)) : ?>
                     <div class="auth-alert auth-alert--error" role="alert">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                           <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span><?= esc_html($authError); ?></span>
                     </div>
                  <?php endif; ?>

                  <?php if (!empty($authSuccess)) : ?>
                     <div class="auth-alert auth-alert--success" role="status">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                           <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <span><?= esc_html($authSuccess); ?></span>
                     </div>
                  <?php endif; ?>

                  <!-- Signup Form -->
                  <form method="post" action="<?= esc_url(home_url('/signup/')); ?>" class="auth-form" id="dk-signup-form">
                     <?php wp_nonce_field('dk_signup_action', '_dk_signup_nonce'); ?>
                     <input type="hidden" name="dk_auth_action" value="signup">
                     <input type="text" name="dk_hp_auth" class="auth-hp" tabindex="-1" autocomplete="off" aria-hidden="true">

                     <!-- Full Name -->
                     <div class="auth-field">
                        <label for="dk-signup-name" class="auth-label">Full Name</label>
                        <div class="auth-input-wrap">
                           <span class="auth-input-icon" aria-hidden="true">
                              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                 <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                 <circle cx="12" cy="7" r="4"/>
                              </svg>
                           </span>
                           <input
                              type="text"
                              name="full_name"
                              id="dk-signup-name"
                              class="auth-input"
                              placeholder="Jordan Reeves"
                              value="<?= esc_attr($_POST['full_name'] ?? ''); ?>"
                              required
                              autocomplete="name"
                           >
                        </div>
                     </div>

                     <!-- Email Address -->
                     <div class="auth-field">
                        <label for="dk-signup-email" class="auth-label">Email Address</label>
                        <div class="auth-input-wrap">
                           <span class="auth-input-icon" aria-hidden="true">
                              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                 <rect x="2" y="4" width="20" height="16" rx="2"/>
                                 <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                              </svg>
                           </span>
                           <input
                              type="email"
                              name="user_email"
                              id="dk-signup-email"
                              class="auth-input"
                              placeholder="you@example.com"
                              value="<?= esc_attr($_POST['user_email'] ?? ''); ?>"
                              required
                              autocomplete="email"
                           >
                        </div>
                     </div>

                     <!-- Password -->
                     <div class="auth-field">
                        <label for="dk-signup-pass" class="auth-label">Password</label>
                        <div class="auth-input-wrap">
                           <span class="auth-input-icon" aria-hidden="true">
                              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                 <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                 <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                              </svg>
                           </span>
                           <input
                              type="password"
                              name="user_pass"
                              id="dk-signup-pass"
                              class="auth-input auth-input--has-toggle"
                              placeholder="••••••••"
                              required
                              autocomplete="new-password"
                              minlength="8"
                           >
                           <button
                              type="button"
                              class="auth-toggle-pass"
                              aria-label="Toggle password visibility"
                              data-auth-toggle="#dk-signup-pass"
                           >
                              <svg class="auth-eye-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                 <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                 <circle cx="12" cy="12" r="3"/>
                              </svg>
                              <svg class="auth-eye-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;">
                                 <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                 <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                 <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                 <line x1="2" x2="22" y1="2" y2="22"/>
                              </svg>
                           </button>
                        </div>
                     </div>

                     <!-- Confirm Password -->
                     <div class="auth-field">
                        <label for="dk-signup-pass-cf" class="auth-label">Confirm Password</label>
                        <div class="auth-input-wrap">
                           <span class="auth-input-icon" aria-hidden="true">
                              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                 <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                 <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                              </svg>
                           </span>
                           <input
                              type="password"
                              name="user_pass_confirm"
                              id="dk-signup-pass-cf"
                              class="auth-input auth-input--has-toggle"
                              placeholder="••••••••"
                              required
                              autocomplete="new-password"
                              minlength="8"
                           >
                           <button
                              type="button"
                              class="auth-toggle-pass"
                              aria-label="Toggle password visibility"
                              data-auth-toggle="#dk-signup-pass-cf"
                           >
                              <svg class="auth-eye-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                 <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                 <circle cx="12" cy="12" r="3"/>
                              </svg>
                              <svg class="auth-eye-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;">
                                 <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                 <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                 <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                 <line x1="2" x2="22" y1="2" y2="22"/>
                              </svg>
                           </button>
                        </div>
                     </div>

                     <!-- Options Row: Remember Me -->
                     <div class="auth-options-row">
                        <label class="auth-checkbox-label">
                           <input type="checkbox" name="rememberme" id="dk-signup-remember" value="forever" class="auth-checkbox" checked>
                           <span class="auth-checkbox-custom" aria-hidden="true"></span>
                           <span class="auth-checkbox-text">Remember me</span>
                        </label>
                     </div>

                     <!-- Submit Button -->
                     <button type="submit" class="btn-gold auth-submit-btn">
                        <span>Create Account</span>
                        <span class="btn-arrow" aria-hidden="true">→</span>
                     </button>
                  </form>

                  <!-- Card Switcher Footer -->
                  <div class="auth-card-footer">
                     <p class="auth-card-footer__text">
                        Already a member? <a href="<?= esc_url(home_url('/login/')); ?>" class="auth-link-highlight">Sign in</a>
                     </p>
                     <div class="auth-trust-seal">
                        <svg width="15" height="17" viewBox="0 0 16 18" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                           <path d="M8 1L2 3.66667V8.11111C2 12.0178 4.56 15.6533 8 16.5556C11.44 15.6533 14 12.0178 14 8.11111V3.66667L8 1Z"/>
                        </svg>
                        <span>Secured with 256-bit encryption</span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
</main>
<?php
get_footer();
