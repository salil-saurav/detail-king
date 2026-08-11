<?php

/**
 * Single service — CTA. Design band y 3443…4027 (dark card).
 *
 * Wraps the shared global/cta-banner part with this service's own copy.
 * Primary CTA defaults to the on-page booking widget anchor.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
?>
<section class="service-cta" data-animate="zoom">
   <div class="container-dk">
      <?php
      get_template_part('template-parts/global/cta-banner', null, [
         'title'          => (string) $meta->field('cta_title'),
         'gold'           => (string) $meta->field('cta_title_gold'),
         'text'           => (string) $meta->field('cta_text'),
         'primary_text'   => (string) $meta->field('cta_primary_text', false, __('Build My Booking', 'detailking')),
         'primary_url'    => (string) $meta->field('cta_primary_url', false, '#dk-booking'),
         'secondary_text' => (string) $meta->field('cta_secondary_text', false, __('All Services', 'detailking')),
         'secondary_url'  => (string) $meta->field('cta_secondary_url', false, home_url('/our-services/')),
      ]);
      ?>
   </div>
</section>
