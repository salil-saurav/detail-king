<?php

/**
 * Build Your Package — Hero Banner.
 *
 * Reuses template-parts/global/page-banner.php with args from BuildPackageDefaults / PageMeta.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();

$bgId  = $meta->field('hero_bg_image');
$bgUrl = $bgId ? wp_get_attachment_image_url((int) $bgId, 'full') : '';

get_template_part('template-parts/global/page-banner', null, [
   'eyebrow' => (string) $meta->fieldOr('hero_eyebrow', 'buildpackage'),
   'title'   => (string) $meta->fieldOr('hero_title', 'buildpackage'),
   'gold'    => (string) $meta->fieldOr('hero_title_gold', 'buildpackage'),
   'text'    => (string) $meta->fieldOr('hero_text', 'buildpackage'),
   'bg'      => $bgUrl,
   'break'   => false,
]);
