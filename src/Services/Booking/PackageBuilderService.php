<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Booking;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use DetailKing\Theme\Meta\MetaHelper;
use DetailKing\Theme\Services\Forms\FormService;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined('ABSPATH') || exit;

/**
 * PackageBuilderService handles the "Build Your Own Package" feature
 * (REST endpoint detailking/v1/package-builder).
 *
 * Sibling to BookingWidgetService — mirrors its honeypot + IP throttle +
 * nonce verification pattern. Per byop-data-map.md, every submission follows
 * the quote/enquiry path: creates a dk_booking post with both relational and
 * additive display fields, sends an email notification, and redirects to thank-you.
 */
class PackageBuilderService extends Singleton implements ServiceInterface
{
   public const REST_NAMESPACE = 'detailking/v1';

   /** Honeypot field name — matches dk_hp */
   private const HP_FIELD = 'dk_hp';

   public function register(): void
   {
      add_action('rest_api_init', [$this, 'registerRoutes']);
      add_action('wp_enqueue_scripts', [$this, 'enqueueAssets'], 20);
   }

   public function registerRoutes(): void
   {
      register_rest_route(self::REST_NAMESPACE, '/package-builder', [
         'methods'             => WP_REST_Server::CREATABLE,
         'callback'            => [$this, 'handle'],
         'permission_callback' => [$this, 'verifyNonce'],
      ]);
   }

   public function verifyNonce(WP_REST_Request $request): bool|WP_Error
   {
      $nonce = $request->get_header('x_wp_nonce') ?: (string) $request->get_param('_wpnonce');

      if (!wp_verify_nonce($nonce, 'wp_rest')) {
         return new WP_Error(
            'dk_byop_invalid_nonce',
            __('Your session has expired. Please reload the page and try again.', 'detailking'),
            ['status' => 403]
         );
      }

      return true;
   }

   /**
    * Enqueue package builder assets on the Build Your Package template.
    */
   public function enqueueAssets(): void
   {
      if (is_page_template('pages/template-build-package.php') || is_page('build-your-package')) {
         $themeUri = get_template_directory_uri();
         $themeDir = get_template_directory();

         $cssPath = '/assets/css/pages/build-package.css';
         $cssVer  = file_exists($themeDir . $cssPath) ? (string) filemtime($themeDir . $cssPath) : '1.0.0';

         wp_enqueue_style(
            'dk-build-package',
            $themeUri . $cssPath,
            ['sp-global', 'dk-fonts'],
            $cssVer
         );

         $jsPath = '/assets/js/pages/build-package.js';
         $jsVer  = file_exists($themeDir . $jsPath) ? (string) filemtime($themeDir . $jsPath) : '1.0.0';

         wp_enqueue_script(
            'dk-build-package',
            $themeUri . $jsPath,
            ['sp-main'],
            $jsVer,
            ['strategy' => 'defer']
         );

         wp_localize_script('dk-build-package', 'DetailKingPackageBuilder', $this->frontendData());
      }
   }

   // -------------------------------------------------------------------------
   // Submission handling
   // -------------------------------------------------------------------------

   public function handle(WP_REST_Request $request): WP_REST_Response
   {
      // Honeypot: a filled hidden field means a bot. Pretend success.
      if (trim((string) $request->get_param(self::HP_FIELD)) !== '') {
         return $this->success([
            'message'  => __("Thanks — we've received your enquiry and will be in touch shortly.", 'detailking'),
            'redirect' => FormService::getInstance()->thankYouUrl('package-builder'),
         ]);
      }

      $throttle = $this->checkThrottle();
      if ($throttle instanceof WP_REST_Response) {
         return $throttle;
      }

      $values = $this->collectFields($request);

      if (!empty($values['errors'])) {
         $response = $this->error(__('Please correct the highlighted fields.', 'detailking'), 422);
         $response->set_data(array_merge($response->get_data(), ['errors' => $values['errors']]));
         return $response;
      }

      $this->recordSubmission();

      return $this->createBooking($values);
   }

   /**
    * Collect and validate submission payload.
    *
    * @return array<string, mixed>
    */
   private function collectFields(WP_REST_Request $request): array
   {
      $fullName = sanitize_text_field((string) $request->get_param('full_name'));
      $phone    = sanitize_text_field((string) $request->get_param('phone'));
      $email    = sanitize_email((string) $request->get_param('email'));

      $errors = [];
      if ($fullName === '') {
         $errors['full_name'] = __('Full name is required.', 'detailking');
      }
      if ($phone === '') {
         $errors['phone'] = __('Phone number is required.', 'detailking');
      }
      if ($email === '' || !is_email($email)) {
         $errors['email'] = __('Please enter a valid email address.', 'detailking');
      }

      // Vehicle
      $vehicleSize = sanitize_text_field((string) $request->get_param('vehicle_size'));
      if ($vehicleSize === '') {
         $errors['vehicle_size'] = __('Please select a vehicle size.', 'detailking');
      }

      // Service
      $serviceSlug = sanitize_title((string) $request->get_param('service_slug'));
      $serviceId   = (int) $request->get_param('service_id');
      $servicePost = null;

      if ($serviceId > 0) {
         $servicePost = get_post($serviceId);
      } elseif ($serviceSlug !== '') {
         $posts = get_posts([
            'post_type'   => 'dk_service',
            'name'        => $serviceSlug,
            'numberposts' => 1,
         ]);
         if ($posts) {
            $servicePost = $posts[0];
            $serviceId   = $servicePost->ID;
         }
      }

      if (!$servicePost || $servicePost->post_type !== 'dk_service') {
         $errors['service'] = __('Please select a service.', 'detailking');
      }

      // Package / requirement
      $packageId    = (int) $request->get_param('package_id');
      $packagePost  = $packageId > 0 ? get_post($packageId) : null;
      $requirements = $request->get_param('requirements');
      $reqString    = '';

      if (is_array($requirements)) {
         $cleanReqs = array_filter(array_map('sanitize_text_field', $requirements));
         $reqString = implode(', ', $cleanReqs);
      } elseif (is_string($requirements)) {
         $reqString = sanitize_text_field($requirements);
      }

      // Add-ons
      $addons      = $request->get_param('addons');
      $addonTitles = [];
      if (is_array($addons)) {
         foreach ($addons as $addonItem) {
            if (is_numeric($addonItem)) {
               $p = get_post((int) $addonItem);
               if ($p) {
                  $addonTitles[] = get_the_title($p);
               }
            } elseif (is_string($addonItem)) {
               $t = trim(sanitize_text_field($addonItem));
               if ($t !== '') {
                  $addonTitles[] = $t;
               }
            }
         }
      }

      $notes     = sanitize_textarea_field((string) $request->get_param('notes'));
      $wrapNotes = sanitize_textarea_field((string) $request->get_param('wrap_notes'));
      if ($wrapNotes !== '') {
         $notes = ($notes !== '') ? ($notes . "\n\nWrap details: " . $wrapNotes) : ('Wrap details: ' . $wrapNotes);
      }

      $estimate = sanitize_text_field((string) $request->get_param('estimated_cost'));
      if ($estimate === '') {
         $estimate = 'Request Quote';
      }

      return [
         'full_name'    => $fullName,
         'phone'        => $phone,
         'email'        => $email,
         'vehicle_size' => $vehicleSize,
         'service_post' => $servicePost,
         'service_id'   => $serviceId,
         'package_post' => $packagePost,
         'package_id'   => $packageId,
         'requirements' => $reqString,
         'addons'       => $addonTitles,
         'drop_date'    => sanitize_text_field((string) $request->get_param('drop_date')),
         'notes'        => $notes,
         'estimate'     => $estimate,
         'errors'       => $errors,
      ];
   }

   /**
    * Create a dk_booking enquiry post and notify admin.
    *
    * @param array<string, mixed> $values
    */
   private function createBooking(array $values): WP_REST_Response
   {
      /** @var WP_Post|null $service */
      $service = $values['service_post'];
      /** @var WP_Post|null $package */
      $package = $values['package_post'];

      $serviceTitle = $service ? get_the_title($service) : __('Custom Build', 'detailking');
      $packageTitle = $package ? get_the_title($package) : ($values['requirements'] ?: __('Custom Build', 'detailking'));

      $title = sprintf(
         'Package Builder: %s — %s (%s)',
         $serviceTitle,
         $packageTitle,
         $values['full_name']
      );

      $bookingId = wp_insert_post([
         'post_type'   => 'dk_booking',
         'post_status' => 'publish',
         'post_title'  => wp_strip_all_tags($title),
      ], true);

      if (is_wp_error($bookingId) || !$bookingId) {
         return $this->error(__('We could not save your enquiry. Please try again.', 'detailking'), 500);
      }

      $addonsSummary = !empty($values['addons']) ? implode(', ', $values['addons']) : __('None', 'detailking');

      $fields = [
         'booking_service'      => $service ? $service->ID : 0,
         'booking_package'      => $package ? $package->ID : 0,
         'booking_vehicle_size' => $values['vehicle_size'],
         'booking_name'         => $values['full_name'],
         'booking_phone'        => $values['phone'],
         'booking_email'        => $values['email'],
         'booking_date'         => $values['drop_date'],
         'booking_notes'        => $values['notes'],
         // Additive BYOP specific fields
         'booking_byop_service'  => $serviceTitle,
         'booking_byop_package'  => $packageTitle,
         'booking_byop_addons'   => $addonsSummary,
         'booking_byop_estimate' => $values['estimate'],
      ];

      foreach ($fields as $name => $value) {
         if (function_exists('update_field')) {
            update_field($name, $value, $bookingId);
         } else {
            update_post_meta($bookingId, $name, $value);
         }
      }

      $this->notifyEnquiry($serviceTitle, $packageTitle, $addonsSummary, $values, (int) $bookingId);

      return $this->success([
         'message'  => __("Thanks — we've received your package enquiry and will be in touch shortly.", 'detailking'),
         'redirect' => FormService::getInstance()->thankYouUrl('package-builder'),
      ]);
   }

   /**
    * Send email notification to admin.
    *
    * @param array<string, mixed> $values
    */
   private function notifyEnquiry(string $serviceTitle, string $packageTitle, string $addonsSummary, array $values, int $bookingId): void
   {
      $to = sanitize_email((string) MetaHelper::getInstance()->opt('forms_notify_email', get_option('admin_email')));
      if ($to === '') {
         return;
      }

      $subject = sprintf(
         /* translators: 1: service name, 2: site name */
         __('[%2$s] Custom Package Request — %1$s', 'detailking'),
         $serviceTitle,
         get_bloginfo('name')
      );

      $lines = [
         sprintf(__('Vehicle Size: %s', 'detailking'), $values['vehicle_size']),
         sprintf(__('Service: %s', 'detailking'), $serviceTitle),
         sprintf(__('Package / Requirement: %s', 'detailking'), $packageTitle),
         sprintf(__('Additional Services: %s', 'detailking'), $addonsSummary),
         sprintf(__('Estimated Cost: %s', 'detailking'), $values['estimate']),
         '',
         sprintf(__('Name: %s', 'detailking'), $values['full_name']),
         sprintf(__('Phone: %s', 'detailking'), $values['phone']),
         sprintf(__('Email: %s', 'detailking'), $values['email']),
         sprintf(__('Preferred Date: %s', 'detailking'), $values['drop_date'] ?: '—'),
         sprintf(__('Notes: %s', 'detailking'), $values['notes'] ?: '—'),
         '',
         sprintf(__('View booking in wp-admin: %s', 'detailking'), get_edit_post_link($bookingId, 'raw')),
      ];

      wp_mail($to, $subject, implode("\n", $lines));
   }

   // -------------------------------------------------------------------------
   // Security: IP throttle
   // -------------------------------------------------------------------------

   private function checkThrottle(): ?WP_REST_Response
   {
      $ip = $this->clientIp();
      if ($ip === '') {
         return null;
      }

      $meta    = MetaHelper::getInstance();
      $minGap  = (int) $meta->opt('forms_throttle_seconds', 30);
      $maxHour = (int) $meta->opt('forms_throttle_max', 8);

      $last = (int) get_transient('dk_byop_last_' . md5($ip));
      if ($last > 0 && (time() - $last) < $minGap) {
         return $this->error(__('You are submitting too quickly. Please wait a moment and try again.', 'detailking'), 429);
      }

      $count = (int) get_transient('dk_byop_count_' . md5($ip));
      if ($count >= $maxHour) {
         return $this->error(__('Submission limit reached. Please try again later.', 'detailking'), 429);
      }

      return null;
   }

   private function recordSubmission(): void
   {
      $ip = $this->clientIp();
      if ($ip === '') {
         return;
      }

      $minGap = (int) MetaHelper::getInstance()->opt('forms_throttle_seconds', 30);
      set_transient('dk_byop_last_' . md5($ip), time(), max($minGap, MINUTE_IN_SECONDS));

      $countKey = 'dk_byop_count_' . md5($ip);
      set_transient($countKey, (int) get_transient($countKey) + 1, HOUR_IN_SECONDS);
   }

   private function clientIp(): string
   {
      $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
      $ip = (string) apply_filters('detailking/theme/form_client_ip', $ip);
      return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
   }

   // -------------------------------------------------------------------------
   // Response + frontend config
   // -------------------------------------------------------------------------

   private function success(array $extra = []): WP_REST_Response
   {
      return new WP_REST_Response(array_merge(['success' => true], $extra), 200);
   }

   private function error(string $message, int $status): WP_REST_Response
   {
      return new WP_REST_Response(['success' => false, 'message' => $message], $status);
   }

   public function frontendData(): array
   {
      return [
         'restUrl' => esc_url_raw(rest_url(self::REST_NAMESPACE . '/package-builder')),
         'nonce'   => wp_create_nonce('wp_rest'),
         'hpField' => self::HP_FIELD,
         'i18n'    => [
            'sending'     => __('Sending…', 'detailking'),
            'error'       => __('Something went wrong. Please try again.', 'detailking'),
            'noVehicle'   => __('Please select a vehicle size.', 'detailking'),
            'noService'   => __('Please select a service.', 'detailking'),
            'enquirySent' => __("Thanks — we've received your enquiry and will be in touch shortly.", 'detailking'),
         ],
      ];
   }
}
