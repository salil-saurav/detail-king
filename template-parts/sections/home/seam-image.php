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
<?php
/* The zoom reveal is on the FIGURE, not the section. motion.js holds a
   data-animate="zoom" element at scale(1.08) until it reveals, and this is the
   only full-bleed element carrying that variant — so the section's own box grew
   4% past the viewport and the document scrolled sideways by 58px at 1440 (and
   77px at 1920) until the reveal fired. Scaling the 1088px-capped figure instead
   keeps the growth inside the section, which now clips it, and it is the closer
   reading of §9 anyway: the *image* settles from 1.08, not the whole band. */
?>
<section class="home-seam">
   <div class="home-seam__inner">
      <figure class="home-seam__figure" data-animate="zoom" data-parallax-scope>
         <img src="<?= esc_url($image); ?>" alt="" loading="lazy" decoding="async" data-parallax="6">
      </figure>
   </div>
</section>
