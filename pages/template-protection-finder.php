<?php

/**
 * Template Name: Protection Finder Page
 *
 * Protection Finder. Two bands: hero, then the interactive 5-question wizard
 * (self-contained in wizard.php — progress bar, question card, "Your Build"
 * rail, result state). Scoring happens server-side via
 * ProtectionFinderService (REST detailking/v1/protection-finder); see
 * build/figma-data/protection-finder-scoring.md for the locked rubric.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

$sections = [
   'hero',
   'wizard',
];

foreach ($sections as $section) {
   get_template_part('template-parts/sections/protection-finder/' . $section);
}

get_footer();
