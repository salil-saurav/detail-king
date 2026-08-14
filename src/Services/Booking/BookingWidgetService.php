<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Booking;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use DetailKing\Theme\Meta\MetaHelper;
use DetailKing\Theme\Services\CrossSell\CrossSellService;
use DetailKing\Theme\Services\Forms\FormService;
use WC_Order_Item_Product;
use WC_Product_Variable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined('ABSPATH') || exit;

/**
 * The single-service booking widget's real submit target (BUILD-PLAN §7
 * Phase 1 step 7). `sections/service/booking.php` posts here; this is not
 * routed through FormRegistry/FormService — that pipeline always produces a
 * generic Lead post, and this widget genuinely branches on
 * `dk_service.booking_mode`:
 *
 *   - `instant_booking` → the selected dk_package's linked Woo product
 *     (seed/products.php) is added to the real cart, the vehicle size as the
 *     variation, step-2's date/location/notes riding along as cart item data
 *     that becomes order item meta at checkout. No Lead, no dk_booking row —
 *     the order *is* the record.
 *   - `enquiry` (Vinyl Wraps) → no cart touch. Writes a dk_booking post
 *     (PostTypeMeta_Booking) — that CPT exists and is registered
 *     specifically for this path and had never been written to before this.
 *
 * Security is the same bar as FormService's forms (honeypot + IP throttle)
 * minus its signed time-trap: reaching this endpoint already requires
 * clicking a package card and filling a real form, which is a materially
 * higher bar for a naive bot than a bare contact form.
 */
class BookingWidgetService extends Singleton implements ServiceInterface
{
   public const REST_NAMESPACE = 'detailking/v1';
   public const ATTRIBUTE      = 'pa_vehicle-size';

   /** Honeypot field name — matches the convention already rendered by
    *  sections/home/booking.php (`dk_hp`), not FormService's own `sp_website`
    *  constant, which nothing in this codebase's markup actually sends. */
   private const HP_FIELD = 'dk_hp';

   public function register(): void
   {
      add_action('rest_api_init', [$this, 'registerRoutes']);
      add_filter('woocommerce_get_item_data', [$this, 'displayCartItemData'], 10, 2);
      add_action('woocommerce_checkout_create_order_line_item', [$this, 'persistOrderItemMeta'], 10, 4);
   }

   public function registerRoutes(): void
   {
      register_rest_route(self::REST_NAMESPACE, '/booking', [
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
            'dk_booking_invalid_nonce',
            __('Your session has expired. Please reload the page and try again.'),
            ['status' => 403]
         );
      }

      return true;
   }

   // -------------------------------------------------------------------------
   // Submission handling
   // -------------------------------------------------------------------------

   public function handle(WP_REST_Request $request): WP_REST_Response
   {
      // Honeypot: a filled hidden field means a bot. Pretend success.
      if (trim((string) $request->get_param(self::HP_FIELD)) !== '') {
         return $this->success('enquiry', []);
      }

      $throttle = $this->checkThrottle();
      if ($throttle instanceof WP_REST_Response) {
         return $throttle;
      }

      $packageId = (int) $request->get_param('package_id');
      $package   = get_post($packageId);

      if (!$package || $package->post_type !== 'dk_package') {
         return $this->error(__('Please choose a package first.'), 404);
      }

      $serviceId = (int) get_field('package_service', $packageId);
      $service   = $serviceId ? get_post($serviceId) : null;

      if (!$service) {
         return $this->error(__('This package has no linked service.'), 500);
      }

      $values = $this->collectContactFields($request);

      if (!empty($values['errors'])) {
         $response = $this->error(__('Please correct the highlighted fields.'), 422);
         $response->set_data(array_merge($response->get_data(), ['errors' => $values['errors']]));
         return $response;
      }

      $this->recordSubmission();

      $bookingMode = (string) get_field('booking_mode', $serviceId) ?: 'instant_booking';

      if ($bookingMode === 'enquiry') {
         return $this->handleEnquiry($package, $service, $values);
      }

      return $this->handleInstantBooking($package, $service, $values);
   }

   /** @return array{full_name:string,phone:string,email:string,vehicle_size:string,drop_date:string,drop_time:string,location:string,notes:string,errors:array<string,string>} */
   private function collectContactFields(WP_REST_Request $request): array
   {
      $fullName = sanitize_text_field((string) $request->get_param('full_name'));
      $phone    = sanitize_text_field((string) $request->get_param('phone'));
      $email    = sanitize_email((string) $request->get_param('email'));

      $errors = [];
      if ($fullName === '') {
         $errors['full_name'] = __('Full name is required.');
      }
      if ($phone === '') {
         $errors['phone'] = __('Phone number is required.');
      }
      if ($email === '' || !is_email($email)) {
         $errors['email'] = __('Please enter a valid email address.');
      }

      return [
         'full_name'    => $fullName,
         'phone'        => $phone,
         'email'        => $email,
         'vehicle_size' => sanitize_title((string) $request->get_param('vehicle_size')),
         'drop_date'    => sanitize_text_field((string) $request->get_param('drop_date')),
         'drop_time'    => sanitize_text_field((string) $request->get_param('drop_time')),
         'location'     => sanitize_text_field((string) $request->get_param('location')),
         'notes'        => sanitize_textarea_field((string) $request->get_param('notes')),
         'errors'       => $errors,
      ];
   }

   // -------------------------------------------------------------------------
   // instant_booking → real cart
   // -------------------------------------------------------------------------

   private function handleInstantBooking(\WP_Post $package, \WP_Post $service, array $values): WP_REST_Response
   {
      $productId = (int) get_field('package_product', $package->ID);

      if ($productId <= 0) {
         return $this->error(__('This package is not available to book online yet — please get in touch directly.'), 422);
      }

      $product = wc_get_product($productId);

      if (!$product instanceof WC_Product_Variable) {
         return $this->error(__('This package is not available to book online yet — please get in touch directly.'), 422);
      }

      $sizeSlug = $values['vehicle_size'];
      $sizeTerm = $sizeSlug !== '' ? get_term_by('slug', $sizeSlug, self::ATTRIBUTE) : false;

      if (!$sizeTerm) {
         return $this->error(__('Please choose a vehicle size.'), 422);
      }

      $variationId = 0;
      foreach ($product->get_children() as $childId) {
         if (get_post_meta($childId, 'attribute_' . self::ATTRIBUTE, true) === $sizeSlug) {
            $variationId = $childId;
            break;
         }
      }

      if ($variationId <= 0) {
         return $this->error(__('That vehicle size is not available for this package.'), 422);
      }

      if (!function_exists('wc_load_cart')) {
         return $this->error(__('The cart is unavailable right now. Please try again.'), 500);
      }

      wc_load_cart();

      // wc_load_cart()'s new WC_Cart hooks its own session load onto `wp_loaded`
      // (WC_Cart_Session::init()), which has already fired by the time a REST
      // route callback runs — so without this, the cart it builds is always
      // empty in memory regardless of what the session actually holds, and
      // add_to_cart() below would silently overwrite an existing cart instead
      // of adding to it. WooCommerce's own Store API CartController::load_cart()
      // works around this the same way: force the session read explicitly.
      WC()->cart->get_cart();

      $cartItemData = [
         'dk_booking' => [
            'service'  => get_the_title($service),
            'package'  => get_the_title($package),
            'vehicle'  => $sizeTerm->name,
            'name'     => $values['full_name'],
            'phone'    => $values['phone'],
            'email'    => $values['email'],
            'date'     => $values['drop_date'],
            'time'     => $values['drop_time'],
            'location' => $values['location'],
            'notes'    => $values['notes'],
         ],
      ];

      $added = WC()->cart->add_to_cart(
         $productId,
         1,
         $variationId,
         [self::ATTRIBUTE => $sizeSlug],
         $cartItemData
      );

      if (!$added) {
         return $this->error(__('Could not add this to your cart. Please try again.'), 500);
      }

      return $this->success('cart', [
         'redirect'      => wc_get_cart_url(),
         'cartCount'     => WC()->cart->get_cart_contents_count(),
         // The recommendation modal (BUILD-PLAN §7 step 8) opens in place of
         // the hard redirect below when this is non-empty — see
         // assets/js/booking-widget.js. Empty when no related product exists,
         // so the JS falls back to the plain redirect rather than opening a
         // modal with nothing to show.
         'crossSellHtml' => CrossSellService::getInstance()->recommendationsHtml($productId),
      ]);
   }

   /** Show the booking details on the Cart/Checkout line item — WC's own convention for custom cart item data. */
   public function displayCartItemData(array $itemData, array $cartItem): array
   {
      if (empty($cartItem['dk_booking'])) {
         return $itemData;
      }

      $labels = [
         'vehicle'  => __('Vehicle Size', 'detailking'),
         'date'     => __('Drop Date', 'detailking'),
         'time'     => __('Drop Time', 'detailking'),
         'location' => __('Location', 'detailking'),
         'notes'    => __('Notes', 'detailking'),
      ];

      foreach ($labels as $key => $label) {
         $value = $cartItem['dk_booking'][$key] ?? '';
         if ($value !== '') {
            $itemData[] = ['name' => $label, 'value' => wc_clean($value)];
         }
      }

      return $itemData;
   }

   /** Snapshot the booking details onto the order line item, so they outlive the cart/session. */
   public function persistOrderItemMeta(WC_Order_Item_Product $item, string $cartItemKey, array $values, \WC_Order $order): void
   {
      if (empty($values['dk_booking'])) {
         return;
      }

      foreach ($values['dk_booking'] as $key => $value) {
         if ($value === '') {
            continue;
         }
         $item->add_meta_data('_dk_booking_' . $key, $value, true);
      }
   }

   // -------------------------------------------------------------------------
   // enquiry → dk_booking record, no cart
   // -------------------------------------------------------------------------

   private function handleEnquiry(\WP_Post $package, \WP_Post $service, array $values): WP_REST_Response
   {
      // Vehicle size is optional context on an enquiry (unlike a priced cart
      // line, nothing here depends on it resolving), so a missing/unknown
      // slug just leaves the field blank rather than failing the submission.
      $vehicleLabel = '';
      if ($values['vehicle_size'] !== '') {
         $term = get_term_by('slug', $values['vehicle_size'], self::ATTRIBUTE);
         if ($term) {
            $vehicleLabel = $term->name;
         }
      }

      $title = sprintf(
         '%s — %s (%s)',
         get_the_title($service),
         get_the_title($package),
         $values['full_name']
      );

      $bookingId = wp_insert_post([
         'post_type'   => 'dk_booking',
         'post_status' => 'publish',
         'post_title'  => wp_strip_all_tags($title),
      ], true);

      if (is_wp_error($bookingId) || !$bookingId) {
         return $this->error(__('We could not save your enquiry. Please try again.'), 500);
      }

      $fields = [
         'booking_service'      => $service->ID,
         'booking_package'      => $package->ID,
         'booking_vehicle_size' => $vehicleLabel,
         'booking_name'         => $values['full_name'],
         'booking_phone'        => $values['phone'],
         'booking_email'        => $values['email'],
         'booking_date'         => $values['drop_date'],
         'booking_time'         => $values['drop_time'],
         'booking_location'     => $values['location'],
         'booking_notes'        => $values['notes'],
      ];

      foreach ($fields as $name => $value) {
         if (function_exists('update_field')) {
            update_field($name, $value, $bookingId);
         } else {
            update_post_meta($bookingId, $name, $value);
         }
      }

      $this->notifyEnquiry($service, $package, $values, (int) $bookingId);

      return $this->success('enquiry', [
         'message'  => __("Thanks — we've received your enquiry and will be in touch shortly.", 'detailking'),
         'redirect' => FormService::getInstance()->thankYouUrl('booking-enquiry'),
      ]);
   }

   private function notifyEnquiry(\WP_Post $service, \WP_Post $package, array $values, int $bookingId): void
   {
      $to = sanitize_email((string) MetaHelper::getInstance()->opt('forms_notify_email', get_option('admin_email')));
      if ($to === '') {
         return;
      }

      $subject = sprintf(
         /* translators: 1: service name, 2: site name */
         __('[%2$s] New enquiry — %1$s'),
         get_the_title($service),
         get_bloginfo('name')
      );

      $lines = [
         sprintf(__('Service: %s'), get_the_title($service)),
         sprintf(__('Package: %s'), get_the_title($package)),
         sprintf(__('Name: %s'), $values['full_name']),
         sprintf(__('Phone: %s'), $values['phone']),
         sprintf(__('Email: %s'), $values['email']),
         sprintf(__('Preferred date: %s'), $values['drop_date'] ?: '—'),
         sprintf(__('Preferred time: %s'), $values['drop_time'] ?: '—'),
         sprintf(__('Location: %s'), $values['location'] ?: '—'),
         sprintf(__('Notes: %s'), $values['notes'] ?: '—'),
         '',
         sprintf(__('View: %s'), get_edit_post_link($bookingId, 'raw')),
      ];

      wp_mail($to, $subject, implode("\n", $lines));
   }

   // -------------------------------------------------------------------------
   // Security: IP throttle (same limits FormService uses, own transient
   // namespace so the two pipelines don't share a counter).
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

      $last = (int) get_transient('dk_booking_last_' . md5($ip));
      if ($last > 0 && (time() - $last) < $minGap) {
         return $this->error(__('You are submitting too quickly. Please wait a moment and try again.'), 429);
      }

      $count = (int) get_transient('dk_booking_count_' . md5($ip));
      if ($count >= $maxHour) {
         return $this->error(__('Submission limit reached. Please try again later.'), 429);
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
      set_transient('dk_booking_last_' . md5($ip), time(), max($minGap, MINUTE_IN_SECONDS));

      $countKey = 'dk_booking_count_' . md5($ip);
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

   private function success(string $mode, array $extra = []): WP_REST_Response
   {
      return new WP_REST_Response(array_merge(['success' => true, 'mode' => $mode], $extra), 200);
   }

   private function error(string $message, int $status): WP_REST_Response
   {
      return new WP_REST_Response(['success' => false, 'message' => $message], $status);
   }

   public function frontendData(): array
   {
      return [
         'restUrl' => esc_url_raw(rest_url(self::REST_NAMESPACE . '/booking')),
         'nonce'   => wp_create_nonce('wp_rest'),
         'hpField' => self::HP_FIELD,
         'i18n'    => [
            'sending'     => __('Sending…', 'detailking'),
            'error'       => __('Something went wrong. Please try again.', 'detailking'),
            'noPackage'   => __('Please choose a package first.', 'detailking'),
            'enquirySent' => __("Thanks — we've received your enquiry and will be in touch shortly.", 'detailking'),
         ],
      ];
   }
}
