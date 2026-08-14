<?php

/**
 * My Account — layout wrapper (BUILD-PLAN §7 Phase 1 step 11).
 *
 * Reached via the `[woocommerce_my_account]` shortcode inside page.php's own
 * loop, same contract as cart.php/form-checkout.php — no get_header()/
 * get_footer() or duplicate title/breadcrumb here, page.php's own
 * page-banner + .container-dk already rendered both. Woo's own navigation/
 * content hooks fire exactly where its default my-account.php fires them —
 * this file only adds the themed two-column layout wrapper around them.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;
?>
<div class="dk-account-layout">

   <div class="dk-account-nav">
      <?php do_action('woocommerce_account_navigation'); ?>
   </div>

   <div class="dk-account-content woocommerce-MyAccount-content">
      <?php do_action('woocommerce_account_content'); ?>
   </div>

</div>
