<?php

/**
 * Gallery CTA section — reuses the global cta-banner component.
 * Design band y 2568…3170 (Figma node 179:6142).
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'gallery';
?>
<?php /* No data-animate here: the shared cta-banner card carries its own zoom
        reveal, and stacking a section fade on top of it reveals the same thing
        twice with two different curves. */ ?>
<section class="gallery-cta-section">
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
