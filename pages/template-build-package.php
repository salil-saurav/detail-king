<?php

/**
 * Template Name: Build Your Package Page
 *
 * Build Your Package Page template.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

$sections = [
   'hero',
   'builder',
   'help',
];

foreach ($sections as $section) {
   get_template_part('template-parts/sections/build-package/' . $section);
}

get_footer();
