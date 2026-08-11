<?php

/**
 * About hero — the shared page-banner part with a studio photo background,
 * plus the same gold marquee ticker the homepage overlaps its hero with (see
 * home/marquee.php — it's a global option, not About-specific copy).
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'about';

$bg = $meta->imageUrl(
   $meta->field('hero_bg_image'),
   get_template_directory_uri() . '/assets/images/about/hero-bg.jpg'
);

get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => '',
   'title'   => $meta->fieldOr('hero_title', $D),
   'gold'    => $meta->fieldOr('hero_title_gold', $D),
   'text'    => $meta->fieldOr('hero_text', $D),
   'bg'      => $bg,
]);

get_template_part('template-parts/sections/home/marquee');
