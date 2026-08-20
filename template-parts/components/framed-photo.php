<?php

/**
 * Framed photo card: rounded outer frame + shadow, an inset gold hairline
 * border, and an optional glass badge over the bottom-left corner.
 *
 * Recurs 3x on the About page alone (Who We Are, Our Story, Our Approach) with
 * only the outer border colour changing — dark sections use a gold-tinted
 * frame, light sections a neutral one. Built once here rather than three times.
 *
 *   get_template_part('template-parts/components/framed-photo', null, [
 *      'image'      => $url,
 *      'alt'        => '',
 *      'border'     => 'gold',   // 'neutral' (default) | 'gold'
 *      'badge_year' => 'Since 2016',   // optional glass badge, omit to hide
 *      'badge_text' => 'Trusted automotive care',
 *      'animate'    => 'zoom-in',      // optional data-animate value, omit for none
 *   ]);
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$image  = isset($args['image']) ? (string) $args['image'] : '';
$alt    = isset($args['alt'])   ? (string) $args['alt']   : '';
$border = isset($args['border']) && $args['border'] === 'gold' ? 'gold' : 'neutral';

$badgeYear = isset($args['badge_year']) ? (string) $args['badge_year'] : '';
$badgeText = isset($args['badge_text']) ? (string) $args['badge_text'] : '';
$animate   = isset($args['animate']) ? (string) $args['animate'] : '';

if ($image === '') {
   return;
}
?>
<div class="dk-framed-photo dk-framed-photo--<?= esc_attr($border); ?>" <?= $animate !== '' ? ' data-animate="' . esc_attr($animate) . '"' : ''; ?>>
   <div class="dk-framed-photo__media">
      <img src="<?= esc_url($image); ?>" alt="<?= esc_attr($alt); ?>" loading="lazy" decoding="async">
   </div>

   <?php if ($badgeYear !== '' || $badgeText !== '') : ?>
      <div class="dk-framed-photo__badge card-glass">
         <?php if ($badgeYear !== '') : ?>
            <span class="dk-framed-photo__badge-year"><?= esc_html($badgeYear); ?></span>
         <?php endif; ?>
         <?php if ($badgeText !== '') : ?>
            <span class="dk-framed-photo__badge-text body-base"><?= esc_html($badgeText); ?></span>
         <?php endif; ?>
      </div>
   <?php endif; ?>
</div>
