<?php

/**
 * Gold marquee ticker. Design node 59:3063, h=75.6, overlapping the hero base.
 *
 * The text is a global option — the same strip appears on About and on every
 * service page, so it is one value, not one per page.
 *
 * The track is duplicated so the CSS translate can loop seamlessly; the copy is
 * aria-hidden so a screen reader reads the phrase once, not twice.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$ticker = (string) MetaHelper::getInstance()->optOr('ticker_text');

if ($ticker === '') {
   return;
}

// Split on the comp's diamond separator so each item can carry its own glyph
// rather than relying on the editor to type "◆" between every phrase.
$items = array_values(array_filter(array_map('trim', explode('◆', $ticker)), 'strlen'));

if (!$items) {
   return;
}
?>
<div class="dk-marquee" role="marquee" aria-label="<?= esc_attr($ticker); ?>">
   <?php for ($copy = 0; $copy < 2; $copy++) : ?>
      <ul class="dk-marquee__track"<?= $copy === 1 ? ' aria-hidden="true"' : ''; ?>>
         <?php
         // Repeat the phrase set enough times to overflow a wide viewport, so the
         // loop never shows a gap on a large screen.
         for ($rep = 0; $rep < 3; $rep++) :
            foreach ($items as $item) : ?>
               <li class="dk-marquee__item label-sm"><?= esc_html($item); ?></li>
            <?php endforeach;
         endfor; ?>
      </ul>
   <?php endfor; ?>
</div>
