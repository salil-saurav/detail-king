<?php

/**
 * Our Services archive — the real template WordPress loads for `/services/`.
 *
 * BUG FOUND during content-resilience QA (15 Aug 2026): `dk_service` is
 * registered with `has_archive => 'services'` (DetailKingPostTypes.php), so
 * `/services/` is a **post-type archive**, not a WP Page — no "Services" Page
 * post exists in the database at all. `pages/template-services.php` is a
 * `Template Name:` **Page** template, which WordPress only ever applies to an
 * actual Page post selected to use it; it can never be reached by a CPT
 * archive URL. With no `archive-dk_service.php` on the theme, WordPress's
 * template hierarchy fell through to the generic `archive.php` (unstyled
 * Bootstrap `.post-card` grid) for every real visitor — the designed 7-card
 * zig-zag grid (`template-parts/sections/services/*`) was never live.
 *
 * Fix: this file, named for WP's `archive-{post_type}.php` hierarchy slot,
 * reuses the exact same four sections `template-services.php` already
 * composes. Safe 1:1 reuse rather than a rewrite — every section
 * (hero/filter-bar/grid/cta) already reads its own ACF Options-group content
 * (`MetaHelper::field()` with no post ID) or runs its own `get_posts()`
 * query for `dk_service`; none of them read the main archive loop or a Page's
 * `post_content`, so behaviour is identical whether reached as a Page
 * template or a CPT archive template. `template-services.php` is left in
 * place rather than deleted — orphaned, harmless, and not this pass's call to
 * remove.
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
