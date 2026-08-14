<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Account;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use WC_DateTime;
use WC_Order;
use WP_Post;

defined('ABSPATH') || exit;

/**
 * Derives a customer's current membership from their own real Woo orders
 * rather than a bespoke user-meta subscription store (BUILD-PLAN §7 Phase 1
 * step 9 continuation). No `update_user_meta`/`wc_get_orders` precedent
 * existed anywhere in this theme before this — this is new ground, kept
 * deliberately simple.
 *
 * A plain query helper, not a hook-registering service: register() is a
 * no-op, called directly from woocommerce/myaccount/dashboard.php.
 *
 * The renewal date this returns is an ESTIMATE (order date + one interval
 * derived from the plan's own free-text `plan_period` label) — real synced
 * billing is Stripe Billing + webhooks' job (BUILD-PLAN §7 step 9, blocked
 * on Stripe gateway access). Every template using this must label the date
 * as an estimate, not assert it as authoritative.
 */
class MembershipAccountService extends Singleton implements ServiceInterface
{
   public function register(): void
   {
      // Intentionally empty — this service is a query helper called
      // directly from the My Account dashboard template, not a hook.
   }

   /**
    * @return array{plan: WP_Post, order: WC_Order, since: string, estimated_renewal: string}|null
    */
   public function currentMembership(int $userId): ?array
   {
      if ($userId <= 0 || !function_exists('wc_get_orders')) {
         return null;
      }

      $productToPlan = $this->productToPlanMap();

      if (!$productToPlan) {
         return null;
      }

      $orders = wc_get_orders([
         'customer_id' => $userId,
         'status'      => ['wc-completed', 'wc-processing'],
         'limit'       => -1,
         'orderby'     => 'date',
         'order'       => 'DESC',
      ]);

      foreach ($orders as $order) {
         if (!$order instanceof WC_Order) {
            continue;
         }

         foreach ($order->get_items() as $item) {
            $productId = (int) $item->get_product_id();

            if (!isset($productToPlan[$productId])) {
               continue;
            }

            $plan  = $productToPlan[$productId];
            $since = $order->get_date_created();

            return [
               'plan'              => $plan,
               'order'             => $order,
               'since'             => $since ? $since->date_i18n(get_option('date_format')) : '',
               // TODO: replace with a real Stripe Customer Portal-synced date
               // once Stripe Billing + webhooks lands (BUILD-PLAN §7 step 9).
               'estimated_renewal' => $this->estimateRenewal($since, (string) get_field('plan_period', $plan->ID)),
            ];
         }
      }

      return null;
   }

   /** @return array<int, WP_Post> Woo product id => dk_membership post. */
   private function productToPlanMap(): array
   {
      $plans = get_posts([
         'post_type'        => 'dk_membership',
         'posts_per_page'   => -1,
         'post_status'      => 'any',
         'suppress_filters' => false,
      ]);

      $map = [];

      foreach ($plans as $plan) {
         $productId = (int) get_field('plan_product', $plan->ID);

         if ($productId > 0) {
            $map[$productId] = $plan;
         }
      }

      return $map;
   }

   /**
    * Best-effort estimate from the plan's own free-text `plan_period` label
    * (e.g. "/ month", "/ year") — not synced billing data.
    */
   private function estimateRenewal(?WC_DateTime $since, string $periodLabel): string
   {
      if (!$since) {
         return '';
      }

      $interval = str_contains(strtolower($periodLabel), 'year') ? '+1 year' : '+1 month';

      $renewal = clone $since;
      $renewal->modify($interval);

      return $renewal->date_i18n(get_option('date_format'));
   }
}
