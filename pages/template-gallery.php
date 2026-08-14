<?php

/**
 * Template Name: Gallery Page
 *
 * The Gallery page. Three bands:
 *  1. Hero / Page Banner (`template-parts/sections/gallery/hero`)
 *  2. Filter bar & Masonry Grid (`template-parts/sections/gallery/grid`)
 *  3. CTA Banner (`template-parts/sections/gallery/cta`)
 *
 * Footer is global.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

$sections = [
   'hero',
   'grid',
   'cta',
];

foreach ($sections as $section) {
   get_template_part('template-parts/sections/gallery/' . $section);
}

get_footer();
