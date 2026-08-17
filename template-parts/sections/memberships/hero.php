<?php

/**
 * Memberships Hero section.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'memberships';

$bgId = $meta->fieldOr('hero_bg_image', $D);
$bg   = $bgId ? (string) wp_get_attachment_image_url((int) $bgId, 'full') : '';

get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => (string) $meta->fieldOr('hero_eyebrow', $D),
   'title'   => (string) $meta->fieldOr('hero_title', $D),
   'gold'    => (string) $meta->fieldOr('hero_title_gold', $D),
   'text'    => (string) $meta->fieldOr('hero_text', $D),
   'bg'      => $bg,
   'break'   => false,
]);
