<?php

/**
 * Template Name: Contact Page
 *
 * Contact page template. Three bands in the comp's order:
 *  - hero/page-banner (GET CONNECTED / Contact Us)
 *  - form-direct (Section 1: Contact Form + Direct contact info / Location summary)
 *  - studios (Section 2: Visit One Of Our Two Studios + 2 dk_location studio cards with iframe maps)
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

$sections = [
   'hero',
   'form-direct',
   'studios',
];

foreach ($sections as $section) {
   get_template_part('template-parts/sections/contact/' . $section);
}

get_footer();
