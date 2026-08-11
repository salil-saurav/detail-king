<?php

/**
 * Seam image. Design band y 2953–3643.2, node 59:2240.
 *
 * A single rounded photo (~1088x562, centred) straddling the boundary between the
 * cream section above and the dark section below. The comp implements the split as
 * one gradient stop at 52% on the section's own fill, which is what the CSS does.
 *
 * The whole band is skipped when there is no image, rather than leaving an empty
 * 690px gap with a visible colour break.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();

$image = $meta->imageUrl(
   $meta->field('seam_image'),
   get_template_directory_uri() . '/assets/images/home/seam-polish.jpg'
);

if ($image === '') {
   return;
}
?>
<section class="home-seam" data-animate="zoom">
   <div class="home-seam__inner">
      <figure class="home-seam__figure">
         <img src="<?= esc_url($image); ?>" alt="" loading="lazy" decoding="async">
      </figure>
   </div>
</section>
