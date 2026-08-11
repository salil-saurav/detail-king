<?php

/**
 * Reusable inner-page banner: dark hero with optional background image, an
 * eyebrow badge, a two-tone title, an optional lead paragraph and the
 * breadcrumb trail.
 *
 * Pass args via get_template_part():
 *   get_template_part('template-parts/global/page-banner', null, [
 *      'eyebrow' => 'Registered Electricians',
 *      'title'   => 'Our Services',
 *      'gold'    => 'Built To Last',       // optional, own line, gold gradient
 *      'text'    => 'Optional lead paragraph.',
 *      'bg'      => 'https://…/banner.jpg', // optional
 *   ]);
 *
 * `gold` renders on a forced second line (a `<br>`), not appended inline like
 * `components/section-heading`'s `gold` arg — every page-banner comp seen so
 * far (About, Contact, CTA) breaks the two halves onto separate lines, and
 * that break is real intent (two clauses), not a reflow artefact to avoid.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Helpers\ThemeHelper;

if (!defined('ABSPATH')) exit;

$eyebrow = $args['eyebrow'] ?? '';
$title   = $args['title']   ?? wp_get_document_title();
$gold    = $args['gold']    ?? '';
$text    = $args['text']    ?? '';
$bg      = $args['bg']      ?? '';
?>
<section class="page-banner<?= $bg ? ' has-bg' : ''; ?>">
   <?php if ($bg) : ?>
      <div class="page-banner__bg" aria-hidden="true">
         <img class="img-fluid" fetchpriority="high" src="<?= esc_url($bg); ?>" alt="<?= esc_attr($title) ?>">
      </div>
   <?php endif; ?>
   <div class="container-dk">
      <div class="page-banner__inner">
         <div class="page-banner__breadcrumb">
            <?= ThemeHelper::getInstance()->get_breadcrumbs(); ?>
         </div>
         <?php if ($eyebrow) : ?>
            <span class="eyebrow eyebrow--badge"><?= esc_html($eyebrow); ?></span>
         <?php endif; ?>
         <h1 class="page-banner__title">
            <?= esc_html($title); ?>
            <?php if ($gold !== '') : ?>
               <br><span class="text-gold-gradient"><?= esc_html($gold); ?></span>
            <?php endif; ?>
         </h1>
         <?php if ($text !== '') : ?>
            <p class="page-banner__text body-lg"><?= esc_html($text); ?></p>
         <?php endif; ?>
      </div>
   </div>
</section>
