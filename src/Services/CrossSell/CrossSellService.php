<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\CrossSell;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined('ABSPATH') || exit;

/**
 * Recommendation popup + cart cross-sell (BUILD-PLAN §7 Phase 1 step 8).
 *
 * Two entry points into the same cart-add + 10%-off machinery:
 *  - `POST /wp-json/detailking/v1/cart/add` — the single-product page's own
 *    add-to-cart form (intercepted by assets/js/cross-sell.js) and the
 *    recommendation modal's own "add this instead/also" buttons.
 *  - BookingWidgetService's existing `/booking` endpoint calls
 *    recommendationsHtml() directly (no second REST round-trip) once its own
 *    add-to-cart succeeds, and renders the same modal.
 *
 * The discount is a custom cart-item-data flag (`dk_cross_sell`), not a
 * WC_Coupon — nothing in this theme creates coupons, and a per-item flag is
 * simpler to reverse (remove the item, the discount goes with it) than a
 * cart-wide coupon that has to be explicitly removed.
 */
class CrossSellService extends Singleton implements ServiceInterface
{
   public const REST_NAMESPACE = 'detailking/v1';
   public const DISCOUNT_PCT   = 10;

   /** How many products to recommend per rail/modal. */
   private const RESULT_LIMIT = 3;

   public function register(): void
   {
      add_action('rest_api_init', [$this, 'registerRoutes']);
      add_action('woocommerce_before_calculate_totals', [$this, 'applyCrossSellDiscount']);
      add_filter('woocommerce_get_item_data', [$this, 'displayCrossSellBadge'], 10, 2);
      add_action('detailking_cart_cross_sell', [$this, 'renderCartRail']);

      // wp_loaded fires after every plugin's own 'init' callback has run,
      // including WC's wc-template-hooks.php include that adds the native
      // cross-sell display to woocommerce_cart_collaterals. Removing it
      // directly from register() would race that include and silently no-op
      // (the same class of hook-priority bug already paid for once with
      // DebloaterService::removeJquery() — see CLAUDE.md).
      add_action('wp_loaded', static function (): void {
         remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10);
      });

      // Rendered once, empty, as a direct child of <body> — same reasoning
      // and same unconditional-render precedent as SearchService::
      // renderOverlay(). [data-dk-add-to-cart] isn't confined to Woo/single-
      // product contexts (membership-card.php's CTA renders on the
      // homepage), so gating this to "Woo contexts" left that button
      // opening nothing. An empty overlay markup on a page with nothing
      // that can open it costs nothing.
      add_action('wp_footer', [$this, 'renderModal']);
   }

   public function renderModal(): void
   {
      get_template_part('template-parts/components/recommendation-modal');
   }

   // -------------------------------------------------------------------------
   // REST endpoint
   // -------------------------------------------------------------------------

   public function registerRoutes(): void
   {
      register_rest_route(self::REST_NAMESPACE, '/cart/add', [
         'methods'             => WP_REST_Server::CREATABLE,
         'callback'            => [$this, 'handleAdd'],
         'permission_callback' => [$this, 'verifyNonce'],
      ]);
   }

   public function verifyNonce(WP_REST_Request $request): bool|WP_Error
   {
      $nonce = $request->get_header('x_wp_nonce') ?: (string) $request->get_param('_wpnonce');

      if (!wp_verify_nonce($nonce, 'wp_rest')) {
         return new WP_Error(
            'dk_cross_sell_invalid_nonce',
            __('Your session has expired. Please reload the page and try again.', 'detailking'),
            ['status' => 403]
         );
      }

      return true;
   }

   public function handleAdd(WP_REST_Request $request): WP_REST_Response
   {
      $productId   = (int) $request->get_param('product_id');
      $quantity    = max(1, (int) ($request->get_param('quantity') ?: 1));
      $variationId = (int) $request->get_param('variation_id');
      $variation   = (array) ($request->get_param('variation') ?: []);
      $isCrossSell = (bool) $request->get_param('cross_sell');

      $product = $productId > 0 ? wc_get_product($productId) : false;

      if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
         return $this->error(__('This product is not available right now.', 'detailking'), 404);
      }

      if (!function_exists('wc_load_cart')) {
         return $this->error(__('The cart is unavailable right now. Please try again.', 'detailking'), 500);
      }

      wc_load_cart();

      // Same fix as BookingWidgetService::handleInstantBooking() — WC_Cart
      // loads empty inside a REST callback unless the session read is forced
      // explicitly (WC_Cart_Session::init() hooks to wp_loaded, which has
      // already fired by now).
      WC()->cart->get_cart();

      $cartItemData = $isCrossSell ? ['dk_cross_sell' => true] : [];

      $added = WC()->cart->add_to_cart($productId, $quantity, $variationId ?: 0, $variation, $cartItemData);

      if (!$added) {
         return $this->error(__('Could not add this to your cart. Please try again.', 'detailking'), 500);
      }

      return $this->success('added', [
         'cartCount'     => WC()->cart->get_cart_contents_count(),
         'cartSubtotal'  => wp_strip_all_tags(WC()->cart->get_cart_subtotal()),
         // Never recommend on top of a recommendation — no nested modal.
         'crossSellHtml' => $isCrossSell ? '' : $this->recommendationsHtml($productId),
      ]);
   }

   // -------------------------------------------------------------------------
   // Discount — always computed from get_regular_price(), never get_price().
   // Woo fires woocommerce_before_calculate_totals multiple times per request
   // (cart load, update_cart, coupon apply, checkout draft recalculation) —
   // reading get_price() on a second pass would re-discount an already-
   // discounted price (0.9 -> 0.81 -> ...). get_regular_price() never mutates.
   // -------------------------------------------------------------------------

   public function applyCrossSellDiscount(\WC_Cart $cart): void
   {
      if (is_admin() && !defined('DOING_AJAX')) {
         return;
      }

      foreach ($cart->get_cart() as $cartItem) {
         if (empty($cartItem['dk_cross_sell'])) {
            continue;
         }

         /** @var \WC_Product $product */
         $product = $cartItem['data'];
         $base    = (float) $product->get_regular_price();

         if ($base <= 0) {
            continue; // no sane base to discount from — leave price untouched
         }

         $product->set_price((string) round($base * (1 - self::DISCOUNT_PCT / 100), 2));
      }
   }

   public function displayCrossSellBadge(array $itemData, array $cartItem): array
   {
      if (empty($cartItem['dk_cross_sell'])) {
         return $itemData;
      }

      $itemData[] = [
         'name'  => __('Discount', 'detailking'),
         'value' => sprintf(
            /* translators: %d: discount percentage */
            __('%d%% off — recommended add-on', 'detailking'),
            self::DISCOUNT_PCT
         ),
      ];

      return $itemData;
   }

   // -------------------------------------------------------------------------
   // Recommendations
   // -------------------------------------------------------------------------

   /**
    * Render 2-3 related products (real Woo relation via wc_get_related_products
    * — shared category/tag, not a hand-picked list) as cross-sell product cards.
    */
   public function recommendationsHtml(int $seedProductId, array $excludeIds = []): string
   {
      $excludeIds[] = $seedProductId;
      $relatedIds   = wc_get_related_products($seedProductId, self::RESULT_LIMIT, $excludeIds);

      if (!$relatedIds) {
         return '';
      }

      ob_start();
      foreach ($relatedIds as $relatedId) {
         $post = get_post($relatedId);
         if ($post) {
            get_template_part('template-parts/components/product-card', null, [
               'product'      => $post,
               'dark'         => true,
               'cross_sell'   => true,
               'discount_pct' => self::DISCOUNT_PCT,
            ]);
         }
      }
      return (string) ob_get_clean();
   }

   /**
    * The Cart page's inline (non-modal) rail — mounted on the inert
    * `detailking_cart_cross_sell` action already present in cart.php.
    */
   public function renderCartRail(): void
   {
      if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
         return;
      }

      $cartProductIds = array_values(array_unique(array_map(
         static fn(array $item): int => (int) $item['product_id'],
         WC()->cart->get_cart()
      )));

      if (!$cartProductIds) {
         return;
      }

      $recommended = [];
      foreach ($cartProductIds as $pid) {
         foreach (wc_get_related_products($pid, self::RESULT_LIMIT, $cartProductIds) as $rid) {
            if (!in_array($rid, $recommended, true)) {
               $recommended[] = $rid;
            }
         }
         if (count($recommended) >= self::RESULT_LIMIT) {
            break;
         }
      }
      $recommended = array_slice($recommended, 0, self::RESULT_LIMIT);

      if (!$recommended) {
         return; // nothing to recommend — omit the section entirely, no empty heading
      }

      get_template_part('template-parts/sections/shop/cross-sell-rail', null, [
         'product_ids' => $recommended,
      ]);
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
         'restUrl' => esc_url_raw(rest_url(self::REST_NAMESPACE . '/cart/add')),
         'nonce'   => wp_create_nonce('wp_rest'),
         'i18n'    => [
            'adding'  => __('Adding…', 'detailking'),
            'added'   => __('Added ✓', 'detailking'),
            'error'   => __('Something went wrong. Please try again.', 'detailking'),
         ],
      ];
   }
}
