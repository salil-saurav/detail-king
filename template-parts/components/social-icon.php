<?php

/**
 * Inline SVG icon by name.
 *
 *   get_template_part('template-parts/components/social-icon', null, ['icon' => 'facebook']);
 *
 * Inline rather than an icon font or sprite file: these are a handful of small
 * paths that need to inherit currentColor (the footer tints them gold on hover),
 * and inlining avoids both a webfont request and a flash of unstyled icons.
 *
 * Returns nothing for an unknown name — deliberately silent, because the caller
 * is a decorative wrapper and an editor picking a value we do not have should
 * not produce a broken-image glyph.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$icon = isset($args['icon']) ? (string) $args['icon'] : '';

/**
 * Each entry is the inner markup of a 24x24 viewBox.
 *
 * @var array<string,string> $paths
 */
$paths = [
   'facebook'  => '<path d="M15.1 8.3h2.2V5.4c-.4 0-1.7-.1-3.1-.1-3 0-4.5 1.8-4.5 5v2.4H7.3v3.2h2.4v8.2h3.4v-8.2h2.6l.4-3.2h-3v-2c0-1.6.5-2.4 1.6-2.4z"/>',
   'instagram' => '<path d="M12 7.4a4.6 4.6 0 1 0 0 9.2 4.6 4.6 0 0 0 0-9.2zm0 7.6a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/><circle cx="17" cy="7" r="1.1"/><path d="M16.3 3H7.7A4.7 4.7 0 0 0 3 7.7v8.6A4.7 4.7 0 0 0 7.7 21h8.6a4.7 4.7 0 0 0 4.7-4.7V7.7A4.7 4.7 0 0 0 16.3 3zm3.1 13.3a3.1 3.1 0 0 1-3.1 3.1H7.7a3.1 3.1 0 0 1-3.1-3.1V7.7a3.1 3.1 0 0 1 3.1-3.1h8.6a3.1 3.1 0 0 1 3.1 3.1z"/>',
   'youtube'   => '<path d="M21.1 8.2a2.6 2.6 0 0 0-1.8-1.8C17.7 6 12 6 12 6s-5.7 0-7.3.4A2.6 2.6 0 0 0 2.9 8.2C2.5 9.8 2.5 12 2.5 12s0 2.2.4 3.8a2.6 2.6 0 0 0 1.8 1.8C6.3 18 12 18 12 18s5.7 0 7.3-.4a2.6 2.6 0 0 0 1.8-1.8c.4-1.6.4-3.8.4-3.8s0-2.2-.4-3.8zM10.2 15V9l5.2 3-5.2 3z"/>',
   'linkedin'  => '<path d="M6.9 20H3.6V9.4h3.3V20zM5.2 8a1.9 1.9 0 1 1 0-3.9 1.9 1.9 0 0 1 0 3.9zM20.4 20h-3.3v-5.6c0-1.4-.5-2.3-1.7-2.3-1 0-1.5.6-1.8 1.3-.1.2-.1.6-.1.9V20H10s.1-9.1 0-10.6h3.3v1.5c.4-.7 1.2-1.7 3-1.7 2.2 0 3.9 1.4 3.9 4.5V20z"/>',
   'tiktok'    => '<path d="M16.6 5.8a4.3 4.3 0 0 1-1-2.8h-2.9v11.6a2.3 2.3 0 1 1-1.7-2.2V9.4a5.2 5.2 0 1 0 4.6 5.2V9.9a7 7 0 0 0 4 1.3V8.3a4.2 4.2 0 0 1-3-2.5z"/>',
   'x'         => '<path d="M17.5 3h3.2l-7 8 7.5 10h-5.8l-4.5-6-5.2 6H2.5l7.3-8.4L2.6 3h5.9l4.2 5.6L17.5 3zm-1.1 16h1.8L7.4 4.9H5.5l10.9 14.1z"/>',

   /* Contact-block glyphs. The comp draws a building, a phone, an envelope and a
      clock beside the four contact rows. */
   'building'  => '<path d="M4 21V6.2a1 1 0 0 1 .7-1l7-2.1a1 1 0 0 1 1.3 1V21H4zm2-2h5V6.4L6 7.9V19zm9 2v-9h4a1 1 0 0 1 1 1v8h-5zm2-2h1v-5h-1v5z"/>',
   'phone'     => '<path d="M20 15.5a12.8 12.8 0 0 1-4-.6.9.9 0 0 0-1 .2l-1.8 1.8a13.6 13.6 0 0 1-5.5-5.5l1.8-1.8a1 1 0 0 0 .2-1 12.8 12.8 0 0 1-.6-4 1 1 0 0 0-1-1H5a1 1 0 0 0-1 1A16 16 0 0 0 20 21a1 1 0 0 0 1-1v-3.5a1 1 0 0 0-1-1z"/>',
   'mail'      => '<path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4.4-8 5-8-5V6l8 5 8-5v2.4z"/>',
   'clock'     => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm.6-13h-1.5v6l5 3 .8-1.3-4.3-2.5V7z"/>',
   'pin'       => '<path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>',

   /* The Shop category sidebar's mobile drawer trigger — a funnel, the
      universal "filter" glyph. */
   'filter'    => '<path d="M3 4h18l-7 8v6l-4 2v-8L3 4z"/>',
];

if (!isset($paths[$icon])) {
   return;
}
?>
<svg class="dk-icon dk-icon--<?= esc_attr($icon); ?>" viewBox="0 0 24 24" width="20" height="20"
   fill="currentColor" focusable="false" aria-hidden="true"><?= $paths[$icon]; ?></svg>
