<?php

/**
 * Gallery Hero section — reuses the global page-banner component.
 * Design band y 0…616 (Figma node 179:6034).
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'gallery';

$bg = $meta->imageUrl(
   $meta->field('hero_bg_image'),
   get_template_directory_uri() . '/assets/images/home/hero-bg.jpg'
);

get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => $meta->fieldOr('hero_eyebrow', $D),
   'title'   => $meta->fieldOr('hero_title', $D),
   'gold'    => $meta->fieldOr('hero_title_gold', $D),
   'text'    => $meta->fieldOr('hero_text', $D),
   'bg'      => $bg,
   'break'   => false,
]);
