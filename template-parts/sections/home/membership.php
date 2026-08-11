<?php

/**
 * Membership plans. Design band y 6515.1–7743.1, node 59:2414.
 *
 * Cards come from the dk_membership CPT because the same three plans appear on the
 * Memberships page — one source, not two that drift.
 *
 * The featured card is dark, sits taller than its siblings and carries a badge that
 * overlaps its top edge. That is a per-plan flag, not a position, so reordering the
 * plans keeps the emphasis on the right one.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$plans = get_posts([
   'post_type'        => 'dk_membership',
   'posts_per_page'   => -1,
   'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
   'suppress_filters' => false,
]);

if (!$plans) {
   return;
}

$footnote  = (string) $meta->fieldOr('membership_footnote', $D);
$watermark = (string) $meta->fieldOr('membership_watermark', $D);
?>
<section class="home-membership section--light" data-animate="fade">
   <?php if ($watermark !== '') : ?>
      <span class="dk-watermark dk-watermark--light home-membership__watermark" aria-hidden="true"><?= esc_html($watermark); ?></span>
   <?php endif; ?>

   <div class="container-dk">

      <?php
      get_template_part('template-parts/components/section-heading', null, [
         'eyebrow' => $meta->fieldOr('membership_eyebrow', $D),
         'title'   => $meta->fieldOr('membership_heading', $D),
         'gold'    => $meta->fieldOr('membership_heading_gold', $D),
         'size'    => 'display-sm',
         'align'   => 'center',
         'rules'   => 'both',
         'text'    => $meta->fieldOr('membership_text', $D),
      ]);
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

      <?php if ($footnote !== '') : ?>
         <p class="home-membership__footnote body-sm"><?= esc_html($footnote); ?></p>
      <?php endif; ?>

   </div>
</section>
