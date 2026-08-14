<?php

/**
 * My Account Dashboard (BUILD-PLAN §7 Phase 1 step 11).
 *
 * TASK-BRIEF.md's required My Account fields: membership plan + renewal
 * date; previous/current orders; services purchased; account details;
 * saved addresses/payment methods; manage/cancel membership; password
 * reset. This screen covers the membership panel + quick links into the
 * rest (orders/addresses/account details all get their own endpoint
 * override — see woocommerce/myaccount/{orders,my-address,form-edit-
 * account}.php); "services purchased" is deliberately the same Orders view,
 * not a second report, since every real line item already is a purchased
 * service/package/product/membership (BUILD-PLAN §2's chosen commerce model).
 *
 * $current_user is the same global Woo's own default dashboard.php uses.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Services\Account\MembershipAccountService;

if (!defined('ABSPATH')) exit;

$membership = MembershipAccountService::getInstance()->currentMembership(get_current_user_id());
?>
<div class="dk-account-dashboard">

   <p class="dk-account-dashboard__greeting">
      <?php
      printf(
         /* translators: %s: user display name */
         esc_html__('Welcome back, %s.', 'detailking'),
         '<strong>' . esc_html($current_user->display_name) . '</strong>'
      );
      ?>
   </p>

   <section class="dk-account-card">
      <h2 class="dk-account-card__title"><?php esc_html_e('Membership', 'detailking'); ?></h2>

      <?php if ($membership) : ?>
         <?php
         get_template_part('template-parts/components/membership-card', null, [
            'plan'              => $membership['plan'],
            'account'           => true,
            'since'             => $membership['since'],
            'estimated_renewal' => $membership['estimated_renewal'],
         ]);
         ?>
      <?php else : ?>
         <p class="body-base"><?php esc_html_e("You don't have an active membership yet.", 'detailking'); ?></p>
         <a class="btn-gold btn-arrow" href="<?= esc_url(home_url('/memberships/')); ?>">
            <?php esc_html_e('View Membership Plans', 'detailking'); ?>
         </a>
      <?php endif; ?>
   </section>

   <div class="dk-account-dashboard__links">
      <a class="dk-account-quick-link" href="<?= esc_url(wc_get_endpoint_url('orders')); ?>">
         <span><?php esc_html_e('Orders & Services Purchased', 'detailking'); ?></span>
         <span aria-hidden="true">&rarr;</span>
      </a>
      <a class="dk-account-quick-link" href="<?= esc_url(wc_get_endpoint_url('edit-address')); ?>">
         <span><?php esc_html_e('Saved Addresses', 'detailking'); ?></span>
         <span aria-hidden="true">&rarr;</span>
      </a>
      <a class="dk-account-quick-link" href="<?= esc_url(wc_get_endpoint_url('edit-account')); ?>">
         <span><?php esc_html_e('Account Details & Password', 'detailking'); ?></span>
         <span aria-hidden="true">&rarr;</span>
      </a>
   </div>

   <p class="dk-account-dashboard__logout">
      <a href="<?= esc_url(wc_logout_url()); ?>"><?php esc_html_e('Log out', 'detailking'); ?></a>
   </p>

   <?php
   /**
    * My Account dashboard — kept for any plugin/hook relying on it.
    *
    * @since 2.6.0
    */
   do_action('woocommerce_account_dashboard');
   ?>

</div>
