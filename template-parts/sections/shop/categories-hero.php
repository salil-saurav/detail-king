<?php

/**
 * Shop landing hero — the shared page-banner part. Fields read against the
 * Shop page's real post ID rather than "current post": WooCommerce's main
 * query on is_shop() iterates products, not the Shop page itself, so the
 * ambient post context here cannot be trusted before/between the_post().
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta   = MetaHelper::getInstance();
$D      = 'shop';
$pageId = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;

$bg = $meta->imageUrl(
   $meta->field('hero_bg_image', $pageId),
   ''
);

get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => $meta->fieldOr('hero_eyebrow', $D, $pageId),
   'title'   => $meta->fieldOr('hero_title', $D, $pageId),
   'text'    => $meta->fieldOr('hero_text', $D, $pageId),
   'bg'      => $bg,
]);
