<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Forms;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use DetailKing\Theme\Meta\MetaHelper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined('ABSPATH') || exit;

/**
 * Handles every custom-form submission over the REST API and replaces
 * Contact Form 7 entirely.
 *
 * One POST endpoint is registered per form in FormRegistry, under the
 * `detailking/v1` namespace (e.g. /wp-json/detailking/v1/forms/quote). Each
 * submission is hardened with several independent layers:
 *
 *   1. REST nonce (X-WP-Nonce) — CSRF protection, verified in permission_callback.
 *   2. Honeypot field — silently drops obvious bots.
 *   3. Signed time-trap — rejects submissions that are impossibly fast or stale.
 *   4. Per-IP throttling — a minimum interval between submissions and an hourly
 *      cap, both backed by transients (configurable).
 *   5. Strict per-field sanitisation + validation from the registry.
 *
 * Valid submissions are stored as a Lead (see LeadPostType), optionally emailed
 * to the site, and the JSON response carries the thank-you page URL the
 * frontend redirects to.
 */
class FormService extends Singleton implements ServiceInterface
{
   public const REST_NAMESPACE = 'detailking/v1';

   /** Honeypot + time-trap request keys. */
   private const HP_FIELD  = 'sp_website';
   private const TS_FIELD  = 'sp_ts';
   private const TOK_FIELD = 'sp_token';

   public function register(): void
   {
      add_action('rest_api_init', [$this, 'registerRoutes']);
      add_action('after_switch_theme', [$this, 'ensureThankYouPage']);
   }

   // -------------------------------------------------------------------------
   // Routing
   // -------------------------------------------------------------------------

   public function registerRoutes(): void
   {
      foreach (array_keys(FormRegistry::getInstance()->all()) as $formId) {
         register_rest_route(self::REST_NAMESPACE, '/forms/' . $formId, [
            'methods'             => WP_REST_Server::CREATABLE, // POST
            'callback'            => fn(WP_REST_Request $request) => $this->handle($formId, $request),
            'permission_callback' => [$this, 'verifyNonce'],
         ]);
      }
   }

   /**
    * CSRF gate: a valid `wp_rest` nonce must accompany every submission. The
    * nonce is bound to the visitor's (possibly logged-out) session cookie.
    */
   public function verifyNonce(WP_REST_Request $request): bool|WP_Error
   {
      $nonce = $request->get_header('x_wp_nonce') ?: (string) $request->get_param('_wpnonce');

      if (!wp_verify_nonce($nonce, 'wp_rest')) {
         return new WP_Error(
            'sp_invalid_nonce',
            __('Your session has expired. Please reload the page and try again.'),
            ['status' => 403]
         );
      }

      return true;
   }

   // -------------------------------------------------------------------------
   // Submission handling
   // -------------------------------------------------------------------------

   private function handle(string $formId, WP_REST_Request $request): WP_REST_Response
   {
      $form = FormRegistry::getInstance()->get($formId);
      if ($form === null) {
         return $this->error(__('Unknown form.'), 404);
      }

      // --- Honeypot: a filled hidden field means a bot. Pretend success. ---
      if (trim((string) $request->get_param(self::HP_FIELD)) !== '') {
         return $this->success($this->thankYouUrl());
      }

      // --- Time-trap: signed timestamp guards against fast bots / stale forms. ---
      $trap = $this->checkTimeTrap($request);
      if ($trap instanceof WP_REST_Response) {
         return $trap;
      }

      // --- Per-IP throttling. ---
      $throttle = $this->checkThrottle();
      if ($throttle instanceof WP_REST_Response) {
         return $throttle;
      }

      // --- Sanitise + validate fields against the registry. ---
      [$values, $errors] = $this->collectFields($form, $request);
      if (!empty($errors)) {
         $response = $this->error(__('Please correct the highlighted fields.'), 422);
         $response->set_data(array_merge($response->get_data(), ['errors' => $errors]));
         return $response;
      }

      // --- Persist, notify, record throttle state. ---
      $leadId = $this->storeLead($formId, $form, $values);
      if (!$leadId) {
         return $this->error(__('We could not save your message. Please try again.'), 500);
      }

      $this->notify($form, $values, $leadId);
      $this->recordSubmission();

      /**
       * Fires after a lead has been stored and the notification sent.
       *
       * @param int                  $leadId
       * @param string               $formId
       * @param array<string,string> $values
       */
      do_action('detailking/theme/form_submitted', $leadId, $formId, $values);

      return $this->success($this->thankYouUrl($formId));
   }

   /**
    * @param array<string,mixed> $form
    * @return array{0:array<string,string>,1:array<string,string>} [values, errors]
    */
   private function collectFields(array $form, WP_REST_Request $request): array
   {
      $values = [];
      $errors = [];

      foreach ($form['fields'] as $key => $field) {
         $raw = $request->get_param($key);
         $raw = is_string($raw) ? $raw : '';

         $value = $this->sanitizeField($field['type'], $raw);

         if ($field['required'] && $value === '') {
            $errors[$key] = sprintf(__('%s is required.'), $field['label']);
            continue;
         }

         if ($value !== '' && $field['type'] === 'email' && !is_email($value)) {
            $errors[$key] = __('Please enter a valid email address.');
            continue;
         }

         $values[$key] = $value;
      }

      return [$values, $errors];
   }

   private function sanitizeField(string $type, string $raw): string
   {
      return match ($type) {
         'email'    => sanitize_email($raw),
         'textarea' => sanitize_textarea_field($raw),
         'url'      => esc_url_raw($raw),
         default    => sanitize_text_field($raw),
      };
   }

   // -------------------------------------------------------------------------
   // Security layers
   // -------------------------------------------------------------------------

   /** A submission is genuine only if the signed timestamp is valid and aged sanely. */
   private function checkTimeTrap(WP_REST_Request $request): ?WP_REST_Response
   {
      $ts    = (int) $request->get_param(self::TS_FIELD);
      $token = (string) $request->get_param(self::TOK_FIELD);

      if ($ts <= 0 || !hash_equals($this->signTimestamp($ts), $token)) {
         return $this->error(__('Your session has expired. Please reload the page and try again.'), 403);
      }

      $elapsed = time() - $ts;
      $minFill = (int) $this->setting('forms_min_fill_seconds', 3);

      // Submitted impossibly fast → bot. Drop silently with a fake success.
      if ($elapsed < $minFill) {
         return $this->success($this->thankYouUrl());
      }

      // Page sat open for over an hour → token stale, ask for a reload.
      if ($elapsed > HOUR_IN_SECONDS) {
         return $this->error(__('This form has expired. Please reload the page and try again.'), 403);
      }

      return null;
   }

   /** Minimum-interval + hourly-cap throttle, keyed by client IP. */
   private function checkThrottle(): ?WP_REST_Response
   {
      $ip      = $this->clientIp();
      $minGap  = (int) $this->setting('forms_throttle_seconds', 30);
      $maxHour = (int) $this->setting('forms_throttle_max', 8);

      if ($ip === '') {
         return null;
      }

      $last = (int) get_transient('sp_form_last_' . md5($ip));
      if ($last > 0 && (time() - $last) < $minGap) {
         return $this->error(
            __('You are submitting too quickly. Please wait a moment and try again.'),
            429
         );
      }

      $count = (int) get_transient('sp_form_count_' . md5($ip));
      if ($count >= $maxHour) {
         return $this->error(
            __('Submission limit reached. Please try again later.'),
            429
         );
      }

      return null;
   }

   private function recordSubmission(): void
   {
      $ip = $this->clientIp();
      if ($ip === '') {
         return;
      }

      $minGap = (int) $this->setting('forms_throttle_seconds', 30);
      set_transient('sp_form_last_' . md5($ip), time(), max($minGap, MINUTE_IN_SECONDS));

      $countKey = 'sp_form_count_' . md5($ip);
      $count    = (int) get_transient($countKey);
      set_transient($countKey, $count + 1, HOUR_IN_SECONDS);
   }

   private function signTimestamp(int $ts): string
   {
      return hash_hmac('sha256', $ts . '|detailking_form', wp_salt('nonce'));
   }

   private function clientIp(): string
   {
      $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
      /** Allow proxies/CDNs to supply the real client IP where trusted. */
      $ip = (string) apply_filters('detailking/theme/form_client_ip', $ip);
      return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
   }

   // -------------------------------------------------------------------------
   // Storage + notification
   // -------------------------------------------------------------------------

   /**
    * @param array<string,mixed>  $form
    * @param array<string,string> $values
    */
   private function storeLead(string $formId, array $form, array $values): int
   {
      $name  = $values['name'] ?? ($values['email'] ?? __('Unknown'));
      $title = sprintf('%s — %s', $form['label'], $name);

      $leadId = wp_insert_post([
         'post_type'   => LeadPostType::POST_TYPE,
         'post_status' => 'publish',
         'post_title'  => wp_strip_all_tags($title),
      ], true);

      if (is_wp_error($leadId) || !$leadId) {
         return 0;
      }

      $prefix = LeadPostType::META_PREFIX;
      foreach ($values as $key => $value) {
         update_post_meta($leadId, $prefix . 'field_' . $key, $value);
      }

      update_post_meta($leadId, $prefix . 'form', $formId);
      update_post_meta($leadId, $prefix . 'ip', $this->clientIp());
      update_post_meta($leadId, $prefix . 'ua', sanitize_text_field((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')));
      update_post_meta($leadId, $prefix . 'referer', esc_url_raw((string) ($_SERVER['HTTP_REFERER'] ?? '')));

      return (int) $leadId;
   }

   /**
    * @param array<string,mixed>  $form
    * @param array<string,string> $values
    */
   private function notify(array $form, array $values, int $leadId): void
   {
      $to = (string) $this->setting('forms_notify_email', get_option('admin_email'));
      $to = sanitize_email($to);
      if ($to === '' || !apply_filters('detailking/theme/form_send_notification', true, $form)) {
         return;
      }

      $subject = sprintf(
         /* translators: 1: form label, 2: site name */
         __('[%2$s] New %1$s submission'),
         $form['label'],
         get_bloginfo('name')
      );

      $lines = [];
      foreach ($form['fields'] as $key => $field) {
         $lines[] = sprintf('%s: %s', $field['label'], $values[$key] ?? '—');
      }
      $lines[] = '';
      $lines[] = sprintf(__('View lead: %s'), get_edit_post_link($leadId, 'raw'));

      wp_mail(
         apply_filters('detailking/theme/form_notify_email', $to, $form),
         apply_filters('detailking/theme/form_notify_subject', $subject, $form),
         implode("\n", $lines)
      );
   }

   // -------------------------------------------------------------------------
   // Thank-you page
   // -------------------------------------------------------------------------

   /** Resolve the post-submission redirect URL. Optionally per-form. */
   public function thankYouUrl(string $formId = ''): string
   {
      $configured = (string) $this->setting('forms_thank_you_page', '');

      if ($configured === '') {
         $page = get_page_by_path('thank-you');
         $configured = $page ? (string) get_permalink($page) : home_url('/');
      }

      /**
       * Filter the thank-you redirect URL (per form id when provided).
       *
       * @param string $url
       * @param string $formId
       */
      return (string) apply_filters('detailking/theme/form_thank_you_url', $configured, $formId);
   }

   /** Create a default "Thank You" page on theme activation if none exists. */
   public function ensureThankYouPage(): void
   {
      if (get_page_by_path('thank-you')) {
         return;
      }

      wp_insert_post([
         'post_type'    => 'page',
         'post_status'  => 'publish',
         'post_title'   => __('Thank You'),
         'post_name'    => 'thank-you',
         'post_content' => __('Thank you for getting in touch. We have received your message and will get back to you shortly.'),
      ]);
   }

   // -------------------------------------------------------------------------
   // Frontend bridge + helpers
   // -------------------------------------------------------------------------

   /**
    * Data localised into forms.js. Carries the REST root, a session nonce and a
    * freshly-signed time-trap token for this page render.
    *
    * @return array<string,mixed>
    */
   public function frontendData(): array
   {
      $ts = time();

      return [
         'restUrl' => esc_url_raw(rest_url(self::REST_NAMESPACE . '/forms/')),
         'nonce'   => wp_create_nonce('wp_rest'),
         'ts'      => $ts,
         'token'   => $this->signTimestamp($ts),
         'hpField' => self::HP_FIELD,
         'tsField' => self::TS_FIELD,
         'tokField'=> self::TOK_FIELD,
         'i18n'    => [
            'sending' => __('Sending…'),
            'error'   => __('Something went wrong. Please try again.'),
         ],
      ];
   }

   private function setting(string $name, mixed $default): mixed
   {
      $value = MetaHelper::getInstance()->opt($name, '');
      return ($value === '' || $value === null) ? $default : $value;
   }

   private function success(string $redirect): WP_REST_Response
   {
      return new WP_REST_Response([
         'success'  => true,
         'redirect' => $redirect,
      ], 200);
   }

   private function error(string $message, int $status): WP_REST_Response
   {
      return new WP_REST_Response([
         'success' => false,
         'message' => $message,
      ], $status);
   }
}
