<?php

/**
 * Single service — one template for all 7 `dk_service` posts. Four bands in
 * the comp's order — hero/page-banner, Intro ("Why X?"), the embedded 3-step
 * booking widget, CTA. Footer is global. Each is its own part under
 * template-parts/sections/service/, mirroring the About/Our Services
 * convention.
 *
 * Section boundaries + content model: projects/detail-king/figma-data/
 * service-single-spec.md.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

get_header();

while (have_posts()) :
   the_post();

   $sections = [
      'hero',    // 0    - 721
      'intro',   // 721  - 1439
      'booking', // 1439 - 3443 (varies with package count)
      'cta',     // 3443 - 4027
   ];

   foreach ($sections as $section) {
      get_template_part('template-parts/sections/service/' . $section);
   }

endwhile;

get_footer();
