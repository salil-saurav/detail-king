<?php

/**
 * Small gold decorative glyph, by name.
 *
 *   get_template_part('template-parts/components/glyph', null, ['glyph' => 'crown']);
 *
 * These are the marks inside the why-us icon badges and the services feature
 * strip. They are drawn as SVG rather than taken from the comp's font, because in
 * the comp they come from Segoe UI Symbol — a Windows system font that is not
 * available to the site and would fall back to something different on every OS.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$glyph = isset($args['glyph']) ? (string) $args['glyph'] : '';

/** @var array<string,string> inner markup of a 24x24 viewBox */
$paths = [
   'crown'   => '<path d="M3 8.5l4 3.2L12 4l5 7.7 4-3.2-1.6 9.5H4.6L3 8.5zM6.3 19h11.4l-.4 2H6.7l-.4-2z"/>',
   'sparkle' => '<path d="M12 2.6l1.9 5.6 5.6 1.9-5.6 1.9L12 17.6l-1.9-5.6L4.5 10l5.6-1.9L12 2.6zM18.6 15.4l.8 2.3 2.3.8-2.3.8-.8 2.3-.8-2.3-2.3-.8 2.3-.8.8-2.3z"/>',
   'diamond' => '<path d="M12 2.6L21.4 12 12 21.4 2.6 12 12 2.6zm0 3.4L6 12l6 6 6-6-6-6z"/>',
   'hexagon' => '<path d="M12 2.2l8.5 4.9v9.8L12 21.8l-8.5-4.9V7.1L12 2.2zm0 2.6L5.7 8.4v7.2L12 19.2l6.3-3.6V8.4L12 4.8z"/>',
   'gear'    => '<path d="M12 8.2a3.8 3.8 0 1 0 0 7.6 3.8 3.8 0 0 0 0-7.6zm0 5.9a2.1 2.1 0 1 1 0-4.2 2.1 2.1 0 0 1 0 4.2z"/><path d="M20.4 13.6l-1.5-.9a7 7 0 0 0 0-1.4l1.5-.9-1.7-3-1.6.7a7 7 0 0 0-1.2-.7L15.6 4h-3.2l-.3 1.7-.4.1-.4.2-1.6-.7-1.7 3 1.4.9a7 7 0 0 0 0 1.4l-1.4.9 1.7 3 1.6-.7c.4.3.8.5 1.2.7l.3 1.7h3.2l.3-1.7c.4-.2.8-.4 1.2-.7l1.6.7 1.7-3z" opacity=".55"/>',
   'spark'   => '<path d="M12 2l1.6 6.1L19.7 6l-4.4 4.4 6.1 1.6-6.1 1.6 4.4 4.4-6.1-2.1L12 22l-1.6-6.1L4.3 18l4.4-4.4L2.6 12l6.1-1.6L4.3 6l6.1 2.1L12 2z"/>',
   'shield'  => '<path d="M12 2.4l7.6 2.8v6.1c0 4.2-3 8-7.6 10.3-4.6-2.3-7.6-6.1-7.6-10.3V5.2L12 2.4zm0 2.2L6.6 6.6v4.7c0 3.2 2.2 6.1 5.4 8 3.2-1.9 5.4-4.8 5.4-8V6.6L12 4.6z"/>',
];

if (!isset($paths[$glyph])) {
   $glyph = 'crown';
}
?>
<svg class="dk-glyph dk-glyph--<?= esc_attr($glyph); ?>" viewBox="0 0 24 24" width="22" height="22"
   fill="currentColor" focusable="false" aria-hidden="true"><?= $paths[$glyph]; ?></svg>
