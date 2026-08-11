<?php

/**
 * Template Name: About Page
 *
 * About. Seven bands in the comp's order — hero/page-banner, Who We Are, Our
 * Story (dark), Equipment, Our Approach (+ process steps), Stats, CTA. Each is
 * its own part under template-parts/sections/about/, mirroring the homepage's
 * one-file-per-section convention.
 *
 * Section boundaries: projects/detail-king/figma-data/about-spec.md.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

$sections = [
   'hero',        // 0        - 833   (page-banner + marquee overlap)
   'who-we-are',  // 833      - 1695
   'our-story',   // 1695     - 2789.6
   'equipment',   // 2789.6   - 3723.4
   'approach',    // 3723.4   - 4931.7
   'stats',       // 4931.7   - 5420.4
   'cta',         // 5420.4   - 6021
];

foreach ($sections as $section) {
   get_template_part('template-parts/sections/about/' . $section);
}

get_footer();
