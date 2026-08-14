<?php

/**
 * Contact Hero / Page Banner.
 *
 * Reuses the shared global/page-banner part per BUILD-PLAN §4.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'contact';

$bgId  = (int) $meta->field('hero_bg_image');
$bgUrl = $bgId > 0 ? wp_get_attachment_image_url($bgId, 'full') : '';

get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => (string) $meta->fieldOr('hero_eyebrow', $D),
   'title'   => (string) $meta->fieldOr('hero_title', $D),
   'gold'    => (string) $meta->fieldOr('hero_title_gold', $D),
   'text'    => (string) $meta->fieldOr('hero_text', $D),
   'bg'      => $bgUrl,
   'break'   => false, // Inline join "Contact Us" on one line matching the comp
]);
