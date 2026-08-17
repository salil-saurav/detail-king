<?php

/**
 * Memberships CTA section.
 *
 * Wraps the shared global/cta-banner part with this page's own copy.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'memberships';

?>
<section class="mship-cta">
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
