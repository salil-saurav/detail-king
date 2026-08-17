<?php

/**
 * Build Your Package — Need A Hand? CTA Band.
 *
 * One white card, split two-col: copy left (padded), photo right (edge-to-edge,
 * clipped to the card's own radius — no separate framed-photo border here).
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'buildpackage';

$imageVal = $meta->field('help_image');
$image    = $imageVal ? wp_get_attachment_image_url((int) $imageVal, 'large') : '';

/* Fallback when help_image is empty — which it is by default, so this is the
   path every visitor actually gets. It pointed at
   `assets/images/about/who-we-are-car.jpg`, a file that has never existed in
   this theme: the band shipped with a 404'd <img> and its alt text showing.
   Now this band's own photo, cut from the comp export (node region y
   5152…6212 at 2x) like every other placeholder image here, and replaced by
   the client's real photography through the ACF field when it lands. */
if (!$image) {
   $image = get_template_directory_uri() . '/assets/images/build-package/help-studio.jpg';
}
?>
<?php /* section--beige, not --light: the builder band above this one is now
        #F6F4EF (see build-package.css), and the comp separates the two by
        stepping this band down to #EDEAE2 rather than running one flat cream
        from the hero to the footer. */ ?>
<section class="byop-help section--beige" data-animate="fade">
   <div class="container-dk">
      <div class="byop-help__card card-light">
         <div class="byop-help__copy">
            <?php
            get_template_part('template-parts/components/section-heading', null, [
               'eyebrow' => $meta->fieldOr('help_eyebrow', $D),
               'title'   => $meta->fieldOr('help_title', $D),
               'gold'    => $meta->fieldOr('help_title_gold', $D),
               'size'    => 'heading-xxs',
            ]);
            ?>
            <div class="byop-help__text body-md mt-4">
               <?= wpautop(esc_html((string) $meta->fieldOr('help_text', $D))); ?>
            </div>

            <?php
            $primaryText = (string) $meta->fieldOr('help_primary_text', $D);
            $primaryUrl  = (string) $meta->fieldOr('help_primary_url', $D);
            if ($primaryText !== '' && $primaryUrl !== '') :
            ?>
               <div class="byop-help__actions mt-4">
                  <a class="btn-dark btn-arrow" href="<?= esc_url($primaryUrl); ?>"><?= esc_html($primaryText); ?></a>
               </div>
            <?php endif; ?>
         </div>

         <div class="byop-help__media">
            <img src="<?= esc_url($image); ?>" alt="<?= esc_attr__('Detail King Studio', 'detailking'); ?>" loading="lazy" decoding="async">
         </div>
      </div>
   </div>
</section>
