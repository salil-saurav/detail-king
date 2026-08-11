<?php

/**
 * Star rating, 1-5.
 *
 *   get_template_part('template-parts/components/stars', null, ['rating' => 5]);
 *
 * Drawn as SVG rather than the "★" character: the comp uses Segoe UI Symbol for it,
 * which is a Windows system font the site does not load, so the glyph would render
 * differently on every platform and at an unpredictable width.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$rating = isset($args['rating']) ? (int) $args['rating'] : 5;
$rating = max(0, min(5, $rating));
?>
<span class="dk-stars" role="img"
   aria-label="<?= esc_attr(sprintf(
      /* translators: %d: star rating out of five */
      _n('%d star out of 5', '%d stars out of 5', $rating, 'detailking'),
      $rating
   )); ?>">
   <?php for ($i = 0; $i < $rating; $i++) : ?>
      <svg class="dk-stars__star" viewBox="0 0 24 24" width="16" height="16" fill="currentColor" focusable="false" aria-hidden="true">
         <path d="M12 2.2l2.9 6.3 6.9.8-5.1 4.7 1.4 6.8-6.1-3.5-6.1 3.5 1.4-6.8L2.2 9.3l6.9-.8L12 2.2z"/>
      </svg>
   <?php endfor; ?>
</span>
