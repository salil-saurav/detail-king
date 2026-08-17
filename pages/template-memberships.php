<?php

/**
 * Template Name: Membership Plans
 *
 * Standalone Memberships page template. Five bands top-to-bottom:
 * hero, plans grid, loyalty rewards, value checklist, CTA.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

wp_enqueue_style('dk-memberships', get_template_directory_uri() . '/assets/css/pages/memberships.css', ['sp-global'], '1.0');

get_header();

$sections = [
   'hero',
   'plans',
   'loyalty',
   'value',
   'cta',
];

foreach ($sections as $section) {
   get_template_part('template-parts/sections/memberships/' . $section);
}

get_footer();
