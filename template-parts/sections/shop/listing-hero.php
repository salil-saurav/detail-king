<?php

/**
 * Category archive hero — no background photo in the comp (node 183:7622,
 * "All Products"), just the dark gradient page-banner already gives by
 * default. Title is the real term name; the comp's literal "All Products"
 * text is one example state, not static copy.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$term    = is_product_category() ? get_queried_object() : null;
$title   = $term instanceof WP_Term ? $term->name : __('All Products', 'detailking');
$eyebrow = $term instanceof WP_Term ? (string) MetaHelper::getInstance()->field('category_tagline', $term, '') : '';
?>
<?php
get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => $eyebrow,
   'title'   => $title,
]);
