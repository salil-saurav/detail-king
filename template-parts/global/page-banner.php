<?php

/**
 * Reusable inner-page banner: dark hero with optional background image, an
 * eyebrow badge, a title and the breadcrumb trail.
 *
 * Pass args via get_template_part():
 *   get_template_part('template-parts/global/page-banner', null, [
 *      'eyebrow' => 'Registered Electricians',
 *      'title'   => 'Our Services',
 *      'bg'      => 'https://…/banner.jpg',   // optional
 *   ]);
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Helpers\ThemeHelper;

if (!defined('ABSPATH')) exit;

$eyebrow = $args['eyebrow'] ?? '';
$title   = $args['title']   ?? wp_get_document_title();
$bg      = $args['bg']      ?? '';
?>
<section class="page-banner<?= $bg ? ' has-bg' : ''; ?>">
   <?php if ($bg) : ?>
      <div class="page-banner__bg" aria-hidden="true">
         <img class="img-fluid" fetchpriority="high" src="<?= esc_url($bg); ?>" alt="<?= esc_attr($title) ?>">
      </div>
   <?php endif; ?>
   <div class="container-xxl">
      <div class="page-banner__inner">
         <?php if ($eyebrow) : ?>
            <span class="eyebrow eyebrow--badge"><?= esc_html($eyebrow); ?></span>
         <?php endif; ?>
         <h1 class="page-banner__title"><?= esc_html($title); ?></h1>
         <div class="page-banner__breadcrumb">
            <?= ThemeHelper::getInstance()->get_breadcrumbs(); ?>
         </div>
      </div>
   </div>
</section>
