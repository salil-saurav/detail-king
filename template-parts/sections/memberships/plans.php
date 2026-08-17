<?php

/**
 * Memberships Plans grid section ("Simple, Rewarding Membership").
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'memberships';

?>
<section class="home-membership section--light" id="plans" data-animate="fade">
   <div class="container-dk">

      <?php
      get_template_part('template-parts/components/section-heading', null, [
         'eyebrow' => $meta->fieldOr('plans_eyebrow', $D),
         'title'   => $meta->fieldOr('plans_title', $D),
         'gold'    => $meta->fieldOr('plans_title_gold', $D),
         'size'    => 'display-sm',
         'align'   => 'center',
         'rules'   => 'both',
         'text'    => $meta->fieldOr('plans_text', $D),
      ]);
      ?>

      <?php get_template_part('template-parts/sections/shared/membership-cards'); ?>

   </div>
</section>
