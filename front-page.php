<?php

/**
 * The homepage.
 *
 * Twelve sections in the comp's order. Each is its own part under
 * template-parts/sections/home/ so a section can be reordered, removed or
 * reasoned about on its own; this file is only the running order.
 *
 * Section boundaries and their design-px bands are recorded in
 * projects/detail-king/figma-data/homepage-sections.txt, which is also what the
 * band-diff reads.
 *
 * `data-animate` goes on each section per the house convention — see
 * global.css §7.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

$sections = [
   'hero',          // 0        - 1155.6
   'marquee',       //   overlaps the hero base at y=1080
   'services',      // 1155.6   - 2953
   'seam-image',    // 2953     - 3643.2
   'video',         // 3643.2   - 4970.2
   'why-us',        // 4970.2   - 5795.1
   'before-after',  // 5795.1   - 6515.1
   'membership',    // 6515.1   - 7743.1
   'shop',          // 7743.1   - 8724.8
   'testimonials',  // 8724.8   - 9605.2
   'instagram',     // 9605.2   - 10215.1
   'booking',       // 10215.1  - 11267.5
   'faq',           // 11267.5  - 12303.1
];

foreach ($sections as $section) {
   get_template_part('template-parts/sections/home/' . $section);
}

get_footer();
