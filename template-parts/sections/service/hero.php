<?php

/**
 * Single service hero — the shared page-banner part, plus the same gold
 * marquee ticker About's hero overlaps (GlobalDefaults' own doc comment:
 * "the same strip appears on About and on every service page").
 *
 * Unlike every page-banner caller before Our Services, the gold word joins
 * inline on the same line as the title ("Window Tinting"), not a forced
 * second line — see page-banner's own doc comment.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();

$bg = $meta->imageUrl(
   $meta->field('hero_bg_image'),
   get_template_directory_uri() . '/assets/images/home/hero-bg.jpg'
);

get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => (string) $meta->field('hero_eyebrow'),
   'title'   => (string) $meta->field('hero_title', false, get_the_title()),
   'gold'    => (string) $meta->field('hero_title_gold'),
   'text'    => (string) $meta->field('hero_text'),
   'bg'      => $bg,
   'break'   => false,
]);

get_template_part('template-parts/sections/home/marquee');
