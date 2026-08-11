<?php

/**
 * Section heading: eyebrow + two-tone display heading.
 *
 * Eleven of the twelve homepage sections use this exact pattern — an eyebrow with
 * a rule, then a Bebas heading whose tail runs in the gold gradient. Building it
 * once here is what stops six near-identical `.xxx-heading` blocks accumulating
 * in the page CSS.
 *
 *   get_template_part('template-parts/components/section-heading', null, [
 *      'eyebrow'   => 'Our Expertise',
 *      'title'     => 'CRAFTED SERVICES FOR',
 *      'gold'      => 'EVERY FINISH',
 *      'size'      => 'display-xxl',   // type-scale class, default heading-xl
 *      'align'     => 'center',        // 'start' (default) | 'center'
 *      'rules'     => 'both',          // 'start' (default) | 'both' | 'none'
 *      'text'      => 'Optional lead paragraph.',
 *      'tag'       => 'h2',            // default h2
 *      'class'     => 'extra classes',
 *      'break'     => true,            // gold portion on its own line — a real
 *                                      // clause break in the comp, not a reflow
 *                                      // artefact (default false: space-joined)
 *   ]);
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$eyebrow = isset($args['eyebrow']) ? (string) $args['eyebrow'] : '';
$title   = isset($args['title'])   ? (string) $args['title']   : '';
$gold    = isset($args['gold'])    ? (string) $args['gold']    : '';
$text    = isset($args['text'])    ? (string) $args['text']    : '';
$size    = isset($args['size'])    ? (string) $args['size']    : 'heading-xl';
$align   = isset($args['align'])   ? (string) $args['align']   : 'start';
$rules   = isset($args['rules'])   ? (string) $args['rules']   : 'start';
$tag     = isset($args['tag'])     ? (string) $args['tag']     : 'h2';
$extra   = isset($args['class'])   ? (string) $args['class']   : '';
$break   = !empty($args['break']);

// Only h1-h6 and a couple of neutral wrappers; never interpolate a raw tag name.
$tag = in_array($tag, ['h1', 'h2', 'h3', 'h4', 'p', 'div'], true) ? $tag : 'h2';

$eyebrowClass = 'eyebrow';
if ($rules === 'both') {
   $eyebrowClass .= ' eyebrow--line';
} elseif ($rules === 'start') {
   $eyebrowClass .= ' eyebrow--rule-start';
}

$blockClass = 'section-heading';
if ($align === 'center') {
   $blockClass .= ' section-heading--center';
}
if ($extra !== '') {
   $blockClass .= ' ' . $extra;
}
?>
<div class="<?= esc_attr($blockClass); ?>">
   <?php if ($eyebrow !== '') : ?>
      <span class="<?= esc_attr($eyebrowClass); ?>"><?= esc_html($eyebrow); ?></span>
   <?php endif; ?>

   <?php if ($title !== '' || $gold !== '') : ?>
      <<?= $tag; ?> class="section-heading__title <?= esc_attr($size); ?>">
         <?php
         // A space between the two halves, not a <br>: the comp's line breaks are
         // a consequence of its own max-width, and freezing them here would stop
         // the heading reflowing when an editor changes a word.
         if ($title !== '') {
            echo esc_html($title);
         }
         if ($gold !== '') {
            echo $break ? '<br>' : ($title !== '' ? ' ' : '');
            echo '<span class="text-gold-gradient">' . esc_html($gold) . '</span>';
         }
         ?>
      </<?= $tag; ?>>
   <?php endif; ?>

   <?php if ($text !== '') : ?>
      <p class="section-heading__text body-base"><?= wp_kses_post($text); ?></p>
   <?php endif; ?>
</div>
