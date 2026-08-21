<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Account;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use WP_Error;
use WP_User;

defined('ABSPATH') || exit;

/**
 * Authentication service for Login, Signup, and Password Reset.
 *
 * Implements:
 * - Native WordPress user authentication (wp_signon) on custom Login template
 * - Native WordPress user registration (wp_insert_user) with auto-login on custom Signup template
 * - Logged-in user redirection to My Account dashboard (/my-account/)
 * - Password reset integration with WordPress native wp-login.php?action=lostpassword flow
 * - Dark-themed on-brand styling for wp-login.php (logo, colours, Bebas/Poppins type)
 * - Unauthenticated wp-login.php / wp-admin requests bounced to the custom
 *   /login page (see redirectToCustomLogin()); logout, the lost-password
 *   flow and a logged-in user's own session are left on wp-login.php
 *
 * @package DetailKing Theme
 */
class AuthService extends Singleton implements ServiceInterface
{
   public static string $authError = '';
   public static string $authSuccess = '';

   /**
    * wp-login.php `action` values that still need the native screen — the
    * lost-password round trip and logout both run through wp-login.php by
    * design (see the class doc above), so they're excluded from the
    * redirect-to-/login rule below rather than redirected mid-flow.
    */
   private const LOGIN_PASSTHROUGH_ACTIONS = [
      'logout',
      'lostpassword',
      'retrievepassword',
      'resetpass',
      'rp',
      'postpass',
      'confirmaction',
      'confirm_admin_email',
   ];

   public function register(): void
   {
      add_action('wp_enqueue_scripts', [$this, 'enqueueAuthAssets'], 20);
      add_action('login_enqueue_scripts', [$this, 'enqueueLoginAssets'], 20);
      add_filter('login_headerurl', [$this, 'loginHeaderUrl']);
      add_filter('login_headertext', [$this, 'loginHeaderText']);
      add_action('template_redirect', [$this, 'handleAuthSubmissions']);
      add_action('template_redirect', [$this, 'redirectLoggedInUsers']);
      add_action('login_init', [$this, 'redirectToCustomLogin']);
   }

   /**
    * Send an unauthenticated wp-login.php request to the theme's own /login
    * page. wp-admin needs no separate hook: WordPress's own admin.php calls
    * auth_redirect() (-> wp-login.php) before 'admin_init' ever fires for a
    * logged-out visitor, so this one hook on 'login_init' catches both a
    * direct wp-login.php hit and that bounce from wp-admin. Logged-in users
    * are untouched either way — they never reach auth_redirect(), and a
    * logged-in user who browses to wp-login.php directly keeps WordPress's
    * own "already logged in" behaviour.
    */
   public function redirectToCustomLogin(): void
   {
      if (is_user_logged_in()) {
         return;
      }

      // The "session expired while editing" interim-login iframe, and the
      // lost-password flow's own "check your email" notice — both render
      // *on* wp-login.php by design; redirecting either mid-flow strands
      // the user with no way back to what they were doing.
      if (isset($_REQUEST['interim-login']) || isset($_GET['checkemail'])) {
         return;
      }

      $action = sanitize_key(wp_unslash($_REQUEST['action'] ?? 'login'));

      if (in_array($action, self::LOGIN_PASSTHROUGH_ACTIONS, true)) {
         return;
      }

      wp_safe_redirect(home_url('/login/'));
      exit;
   }

   /**
    * Enqueue Login/Signup page styles and scripts on auth page templates.
    */
   public function enqueueAuthAssets(): void
   {
      if ($this->isAuthPage()) {
         $themeUri = get_template_directory_uri();
         $themeDir = get_template_directory();

         $authCssPath = '/assets/css/pages/auth.css';
         $ver = file_exists($themeDir . $authCssPath) ? (string) filemtime($themeDir . $authCssPath) : '1.0.0';

         wp_enqueue_style(
            'dk-auth',
            $themeUri . $authCssPath,
            ['sp-global', 'dk-fonts'],
            $ver
         );

         $authJsPath = '/assets/js/auth.js';
         $jsVer = file_exists($themeDir . $authJsPath) ? (string) filemtime($themeDir . $authJsPath) : '1.0.0';

         wp_enqueue_script(
            'dk-auth',
            $themeUri . $authJsPath,
            [],
            $jsVer,
            ['strategy' => 'defer']
         );
      }
   }

   /**
    * Enqueue on-brand dark stylesheet and fonts on native wp-login.php.
    */
   public function enqueueLoginAssets(): void
   {
      $themeUri = get_template_directory_uri();
      $themeDir = get_template_directory();

      // Enqueue Bebas Neue & Poppins self-hosted fonts
      $fontsCss = '/assets/css/fonts.css';
      $fontsVer = file_exists($themeDir . $fontsCss) ? (string) filemtime($themeDir . $fontsCss) : '1.0.0';
      wp_enqueue_style('dk-fonts', $themeUri . $fontsCss, [], $fontsVer);

      // Enqueue dedicated wp-login / auth stylesheet
      $authCssPath = '/assets/css/pages/auth.css';
      $ver = file_exists($themeDir . $authCssPath) ? (string) filemtime($themeDir . $authCssPath) : '1.0.0';

      wp_enqueue_style(
         'dk-wp-login',
         $themeUri . $authCssPath,
         ['dk-fonts'],
         $ver
      );
   }

   /**
    * Redirect the login logo link to site homepage.
    */
   public function loginHeaderUrl(): string
   {
      return home_url('/');
   }

   /**
    * Set the login logo link title to site name.
    */
   public function loginHeaderText(): string
   {
      return get_bloginfo('name');
   }

   /**
    * Check if current view is the Login or Signup template.
    */
   public function isAuthPage(): bool
   {
      return is_page_template('pages/template-login.php')
         || is_page_template('pages/template-signup.php')
         || is_page('login')
         || is_page('signup');
   }

   /**
    * Redirect logged-in users away from login/signup pages to My Account.
    */
   public function redirectLoggedInUsers(): void
   {
      if (!is_user_logged_in()) {
         return;
      }

      if (isset($_GET['action']) && $_GET['action'] === 'logout') {
         return;
      }

      if ($this->isAuthPage()) {
         $target = $this->getDashboardUrl();
         wp_safe_redirect($target);
         exit;
      }
   }

   /**
    * Get dashboard URL (My Account).
    */
   public function getDashboardUrl(): string
   {
      if (function_exists('wc_get_page_permalink')) {
         $wooAccount = wc_get_page_permalink('myaccount');
         if ($wooAccount) {
            return $wooAccount;
         }
      }

      $accountPage = get_page_by_path('my-account');
      if ($accountPage) {
         return get_permalink($accountPage);
      }

      return home_url('/my-account/');
   }

   /**
    * Handle Login and Signup form POST submissions.
    */
   public function handleAuthSubmissions(): void
   {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['dk_auth_action'])) {
         return;
      }

      // Honeypot check for spam bots
      if (!empty($_POST['dk_hp_auth'])) {
         wp_die(__('Spam detected.', 'detailking'));
      }

      $action = sanitize_key($_POST['dk_auth_action']);

      if ($action === 'login') {
         $this->processLogin();
      } elseif ($action === 'signup') {
         $this->processSignup();
      }
   }

   /**
    * Process custom Login submission.
    */
   private function processLogin(): void
   {
      if (!wp_verify_nonce($_POST['_dk_login_nonce'] ?? '', 'dk_login_action')) {
         self::$authError = __('Security check failed. Please refresh and try again.', 'detailking');
         return;
      }

      $log = sanitize_text_field(wp_unslash($_POST['log'] ?? ''));
      $pwd = $_POST['pwd'] ?? '';
      $remember = !empty($_POST['rememberme']);

      if (empty($log) || empty($pwd)) {
         self::$authError = __('Please enter both your email/username and password.', 'detailking');
         return;
      }

      // If an email address was entered, find user by email to ensure login works cleanly
      $userLogin = $log;
      if (is_email($log)) {
         $userByEmail = get_user_by('email', $log);
         if ($userByEmail instanceof WP_User) {
            $userLogin = $userByEmail->user_login;
         }
      }

      $credentials = [
         'user_login'    => $userLogin,
         'user_password' => $pwd,
         'remember'      => $remember,
      ];

      $user = wp_signon($credentials, is_ssl());

      if (is_wp_error($user)) {
         self::$authError = __('Invalid login credentials. Please check your details and try again.', 'detailking');
         return;
      }

      // Redirect on success
      $redirectTo = sanitize_url(wp_unslash($_POST['redirect_to'] ?? ''));
      if (empty($redirectTo) || !wp_validate_redirect($redirectTo)) {
         $redirectTo = $this->getDashboardUrl();
      }

      wp_safe_redirect($redirectTo);
      exit;
   }

   /**
    * Process custom Signup / registration submission.
    */
   private function processSignup(): void
   {
      if (!wp_verify_nonce($_POST['_dk_signup_nonce'] ?? '', 'dk_signup_action')) {
         self::$authError = __('Security check failed. Please refresh and try again.', 'detailking');
         return;
      }

      $fullName   = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
      $userEmail  = sanitize_email(wp_unslash($_POST['user_email'] ?? ''));
      $userPass   = $_POST['user_pass'] ?? '';
      $userPassCf = $_POST['user_pass_confirm'] ?? '';

      // Validation
      if (empty($fullName)) {
         self::$authError = __('Please enter your full name.', 'detailking');
         return;
      }

      if (empty($userEmail) || !is_email($userEmail)) {
         self::$authError = __('Please enter a valid email address.', 'detailking');
         return;
      }

      if (email_exists($userEmail)) {
         self::$authError = __('An account with this email address already exists. Please sign in instead.', 'detailking');
         return;
      }

      if (empty($userPass) || strlen($userPass) < 8) {
         self::$authError = __('Password must be at least 8 characters long.', 'detailking');
         return;
      }

      if ($userPass !== $userPassCf) {
         self::$authError = __('Passwords do not match. Please verify and try again.', 'detailking');
         return;
      }

      // Generate a clean, unique user_login from email prefix or name
      $baseLogin = sanitize_user(strstr($userEmail, '@', true) ?: $userEmail, true);
      if (empty($baseLogin)) {
         $baseLogin = sanitize_user(str_replace(' ', '', strtolower($fullName)), true);
      }
      if (empty($baseLogin)) {
         $baseLogin = 'member';
      }

      // WordPress rejects usernames over 60 characters. A long email's local part
      // (or a long full name) can exceed that on its own, and the dedup loop below
      // appends a numeric suffix on top — truncate first so wp_insert_user() never
      // fails with a raw "Username may not be longer than 60 characters" error.
      $baseLogin = substr($baseLogin, 0, 50);

      $username = $baseLogin;
      $suffix = 1;
      while (username_exists($username)) {
         $username = $baseLogin . $suffix;
         $suffix++;
      }

      // Name breakdown
      $nameParts = explode(' ', $fullName, 2);
      $firstName = $nameParts[0] ?? '';
      $lastName  = $nameParts[1] ?? '';

      // Default role
      $role = get_option('default_role') ?: 'subscriber';
      if (function_exists('wc_get_customer_default_role')) {
         $role = wc_get_customer_default_role();
      }
      $role = apply_filters('detailking/theme/signup_user_role', $role);

      $userId = wp_insert_user([
         'user_login'   => $username,
         'user_email'   => $userEmail,
         'user_pass'    => $userPass,
         'first_name'   => $firstName,
         'last_name'    => $lastName,
         'display_name' => $fullName,
         'role'         => $role,
      ]);

      if (is_wp_error($userId)) {
         self::$authError = $userId->get_error_message() ?: __('Could not create account. Please try again.', 'detailking');
         return;
      }

      // Auto-login the newly registered user
      wp_set_current_user($userId);
      wp_set_auth_cookie($userId, true, is_ssl());

      $createdUser = get_user_by('id', $userId);
      if ($createdUser instanceof WP_User) {
         do_action('wp_login', $createdUser->user_login, $createdUser);
      }

      // Redirect to My Account dashboard
      wp_safe_redirect($this->getDashboardUrl());
      exit;
   }
}
