<?php

/**
 * Our Services hero — the shared page-banner part. Design band y 0…697
 * (node 159:3). Unlike every other page-banner caller, the gold word joins
 * inline on the same line ("Our Services"), not on a forced second line — see
 * page-banner's own doc comment and figma-data/our-services-spec.md.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'services';

$bg = $meta->imageUrl(
   $meta->field('hero_bg_image'),
   get_template_directory_uri() . '/assets/images/home/hero-bg.jpg'
);

get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => '',
   'title'   => $meta->fieldOr('hero_title', $D),
   'gold'    => $meta->fieldOr('hero_title_gold', $D),
   'text'    => $meta->fieldOr('hero_text', $D),
   'bg'      => $bg,
   'break'   => false,
]);
