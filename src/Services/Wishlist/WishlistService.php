<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Wishlist;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined('ABSPATH') || exit;

/**
 * Account-linked wishlist — promotes product-card.php's `[data-dk-wishlist]`
 * heart (previously visual-only/localStorage, see global.css/global.js
 * history) to a real per-customer list: a WooCommerce My Account endpoint
 * ("Wishlisted") backed by user meta, plus the REST route the heart button
 * calls to persist a toggle.
 *
 * Guests keep the old localStorage-only behaviour (global.js falls back to
 * it whenever `DetailKingWishlist.isLoggedIn` is false) — there is nowhere
 * to persist a wishlist without an account, and forcing a login just to
 * click a heart would be a regression, not a feature.
 *
 * Same REST-under-`detailking/v1` + nonce convention as CrossSellService.
 */
class WishlistService extends Singleton implements ServiceInterface
{
   public const REST_NAMESPACE = 'detailking/v1';
   public const ENDPOINT       = 'wishlist';
   public const META_KEY       = '_dk_wishlist';

   public function register(): void
   {
      add_action('init', [$this, 'addEndpoint']);
      add_filter('woocommerce_account_menu_items', [$this, 'addMenuItem']);
      add_action('woocommerce_account_' . self::ENDPOINT . '_endpoint', [$this, 'renderEndpoint']);
      add_action('rest_api_init', [$this, 'registerRoutes']);
   }

   /**
    * `add_rewrite_endpoint()` alone is enough for Woo's own
    * `woocommerce_account_content()` to notice `wishlist` in `$wp->query_vars`
    * and fire `woocommerce_account_wishlist_endpoint` — no entry in
    * `woocommerce_get_query_vars` needed, that list is only for the
    * admin-configurable default endpoints. Flushed once (not on every
    * request) so `/my-account/wishlist/` resolves under pretty permalinks.
    */
   public function addEndpoint(): void
   {
      add_rewrite_endpoint(self::ENDPOINT, EP_ROOT | EP_PAGES);

      if (get_option('dk_wishlist_endpoint_flushed') !== '1') {
         flush_rewrite_rules();
         update_option('dk_wishlist_endpoint_flushed', '1');
      }
   }

   /** @param array<string, string> $items */
   public function addMenuItem(array $items): array
   {
      $keys = array_keys($items);
      $after = array_search('orders', $keys, true);
      $at = $after === false ? 1 : $after + 1;

      return array_slice($items, 0, $at, true)
         + [self::ENDPOINT => __('Wishlisted', 'detailking')]
         + array_slice($items, $at, null, true);
   }

   public function renderEndpoint(): void
   {
      $products = $this->wishlistProducts(get_current_user_id());

      wc_get_template('myaccount/wishlist.php', ['products' => $products]);
   }

   // -------------------------------------------------------------------------
   // Storage
   // -------------------------------------------------------------------------

   /** @return int[] Product ids, oldest-saved first. */
   public function getWishlist(int $userId): array
   {
      if ($userId <= 0) {
         return [];
      }

      $ids = get_user_meta($userId, self::META_KEY, true);

      return is_array($ids) ? array_values(array_unique(array_map('intval', $ids))) : [];
   }

   /** @return \WC_Product[] Still-existing products from the saved ids, newest-saved first. */
   private function wishlistProducts(int $userId): array
   {
      $products = [];

      foreach (array_reverse($this->getWishlist($userId)) as $productId) {
         $product = wc_get_product($productId);

         if ($product) {
            $products[] = $product;
         }
      }

      return $products;
   }

   // -------------------------------------------------------------------------
   // REST endpoint
   // -------------------------------------------------------------------------

   public function registerRoutes(): void
   {
      register_rest_route(self::REST_NAMESPACE, '/wishlist/toggle', [
         'methods'             => WP_REST_Server::CREATABLE,
         'callback'            => [$this, 'handleToggle'],
         'permission_callback' => [$this, 'verifyNonce'],
      ]);
   }

   public function verifyNonce(WP_REST_Request $request): bool|WP_Error
   {
      $nonce = $request->get_header('x_wp_nonce') ?: (string) $request->get_param('_wpnonce');

      if (!wp_verify_nonce($nonce, 'wp_rest')) {
         return new WP_Error(
            'dk_wishlist_invalid_nonce',
            __('Your session has expired. Please reload the page and try again.', 'detailking'),
            ['status' => 403]
         );
      }

      return true;
   }

   public function handleToggle(WP_REST_Request $request): WP_REST_Response
   {
      if (!is_user_logged_in()) {
         return $this->error(__('Please log in to save items to your wishlist.', 'detailking'), 401);
      }

      $productId = (int) $request->get_param('product_id');
      $product   = $productId > 0 ? wc_get_product($productId) : false;

      if (!$product) {
         return $this->error(__('This product could not be found.', 'detailking'), 404);
      }

      $userId = get_current_user_id();
      $ids    = $this->getWishlist($userId);

      if (in_array($productId, $ids, true)) {
         $ids    = array_values(array_diff($ids, [$productId]));
         $action = 'removed';
      } else {
         $ids[]  = $productId;
         $action = 'added';
      }

      update_user_meta($userId, self::META_KEY, $ids);

      return $this->success($action, ['ids' => array_map('strval', $ids)]);
   }

   private function success(string $action, array $extra = []): WP_REST_Response
   {
      return new WP_REST_Response(array_merge(['success' => true, 'action' => $action], $extra), 200);
   }

   private function error(string $message, int $status): WP_REST_Response
   {
      return new WP_REST_Response(['success' => false, 'message' => $message], $status);
   }

   // -------------------------------------------------------------------------
   // Frontend data
   // -------------------------------------------------------------------------

   public function frontendData(): array
   {
      $userId = get_current_user_id();

      return [
         'restUrl'    => esc_url_raw(rest_url(self::REST_NAMESPACE . '/wishlist/toggle')),
         'nonce'      => wp_create_nonce('wp_rest'),
         'isLoggedIn' => is_user_logged_in(),
         'ids'        => array_map('strval', $this->getWishlist($userId)),
      ];
   }
}
