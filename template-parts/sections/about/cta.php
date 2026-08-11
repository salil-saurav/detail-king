<?php

/**
 * About — CTA. Design band y 5420.4…6021 (dark card).
 *
 * Wraps the shared global/cta-banner part with this page's own copy.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'about';
?>
<section class="about-cta" data-animate="zoom">
   <div class="container-dk">
      <?php
      get_template_part('template-parts/global/cta-banner', null, [
         'title'          => $meta->fieldOr('cta_title', $D),
         'gold'           => $meta->fieldOr('cta_title_gold', $D),
         'text'           => $meta->fieldOr('cta_text', $D),
         'primary_text'   => $meta->fieldOr('cta_primary_text', $D),
         'primary_url'    => $meta->fieldOr('cta_primary_url', $D),
         'secondary_text' => $meta->fieldOr('cta_secondary_text', $D),
         'secondary_url'  => $meta->fieldOr('cta_secondary_url', $D),
      ]);
      ?>
   </div>
</section>
