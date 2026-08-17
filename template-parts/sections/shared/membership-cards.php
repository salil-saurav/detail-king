<?php

/**
 * Shared membership card grid component.
 *
 * Renders the 3-card grid off the dk_membership CPT loop.
 * Shared between Homepage (sections/home/membership.php) and Memberships page (template-parts/sections/memberships/plans.php).
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$plans = get_posts([
   'post_type'        => 'dk_membership',
   'posts_per_page'   => -1,
   'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
   'suppress_filters' => false,
]);

if (!$plans) {
   return;
}
?>
<div class="home-membership__grid">
   <?php foreach ($plans as $plan) : ?>
      <?php
      get_template_part('template-parts/components/membership-card', null, [
         'plan' => $plan,
      ]);
      ?>
   <?php endforeach; ?>
</div>
