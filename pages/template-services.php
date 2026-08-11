<?php

/**
 * Template Name: Our Services Page
 *
 * Our Services. Four bands in the comp's order — hero/page-banner, filter
 * bar, card grid, CTA. Footer is global. Each is its own part under
 * template-parts/sections/services/, mirroring the homepage/About convention.
 *
 * Section boundaries: projects/detail-king/figma-data/our-services-spec.md.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

$sections = [
   'hero',        // 0    - 697
   'filter-bar',  // 697  - 790
   'grid',        // 790  - 5182
   'cta',         // 5182 - 5776
];

foreach ($sections as $section) {
   get_template_part('template-parts/sections/services/' . $section);
}

get_footer();
