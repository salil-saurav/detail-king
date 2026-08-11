<?php

/**
 * Homepage hero. Design band y 0–1155.6.
 *
 * Full-bleed photo with a left-weighted dark gradient, badge pill, two-tone
 * display heading, intro copy, a CTA pair, a separate video pill and a stat row
 * with vertical rules.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$badge    = (string) $meta->fieldOr('hero_badge', $D);
$heading  = (string) $meta->fieldOr('hero_heading', $D);
$gold     = (string) $meta->fieldOr('hero_heading_gold', $D);
$text     = (string) $meta->fieldOr('hero_text', $D);
$stats    = $meta->fieldRowsOr('hero_stats', $D);

$ctaText  = (string) $meta->fieldOr('hero_cta_primary_text', $D);
$ctaUrl   = (string) $meta->fieldOr('hero_cta_primary_url', $D);
$cta2Text = (string) $meta->fieldOr('hero_cta_secondary_text', $D);
$cta2Url  = (string) $meta->fieldOr('hero_cta_secondary_url', $D);
$vidText  = (string) $meta->fieldOr('hero_video_text', $D);
$vidUrl   = (string) $meta->fieldOr('hero_video_url', $D);

$bg = $meta->imageUrl(
   $meta->field('hero_bg_image'),
   get_template_directory_uri() . '/assets/images/home/hero-bg.jpg'
);
?>
<section class="home-hero section--dark" data-hero>

   <div class="home-hero__bg" aria-hidden="true" data-hero-bg data-parallax-scope>
      <img src="<?= esc_url($bg); ?>" alt="" fetchpriority="high" decoding="async" data-parallax="4">
   </div>

   <div class="container-dk home-hero__inner">

      <?php if ($badge !== '') : ?>
         <span class="eyebrow eyebrow--badge home-hero__badge"><?= esc_html($badge); ?></span>
      <?php endif; ?>

      <?php
      // display-md (88px), not display-xxl (120px). Measured off the export: the
      // three heading lines occupy design y 270…540, a 90px pitch, and the cap
      // height of line one is 62px — which is an 88px Bebas, not a 120px one.
      ?>
      <h1 class="home-hero__title display-md">
         <?php
         echo esc_html($heading);
         if ($gold !== '') {
            echo ' <span class="text-gold-gradient">' . esc_html($gold) . '</span>';
         }
         ?>
      </h1>

      <?php if ($text !== '') : ?>
         <p class="home-hero__text body-base"><?= wp_kses_post($text); ?></p>
      <?php endif; ?>

      <div class="home-hero__actions">
         <?php if ($ctaText !== '') : ?>
            <a class="btn-gold btn-arrow" href="<?= esc_url($ctaUrl ?: home_url('/contact/')); ?>">
               <?= esc_html($ctaText); ?>
            </a>
         <?php endif; ?>

         <?php if ($cta2Text !== '') : ?>
            <a class="btn-outline-light-dk" href="<?= esc_url($cta2Url ?: home_url('/memberships/')); ?>">
               <?= esc_html($cta2Text); ?>
            </a>
         <?php endif; ?>
      </div>

      <?php if ($vidText !== '') : ?>
         <?php
         // No video URL yet? Still render the pill, but as a plain element rather
         // than a link to nowhere.
         $vidTag  = $vidUrl !== '' ? 'a' : 'span';
         $vidAttr = $vidUrl !== ''
            ? ' href="' . esc_url($vidUrl) . '" data-dk-lightbox'
            : '';
         ?>
         <<?= $vidTag; ?> class="home-hero__video"<?= $vidAttr; ?>>
            <?php
            // The icon carries two ripple rings: one on its ::after, one on this
            // child span, so the two can run half a cycle apart (::before is the
            // play triangle). Decorative — the whole icon is aria-hidden.
            ?>
            <span class="home-hero__videoicon" aria-hidden="true"><span class="home-hero__videowave"></span></span>
            <span><?= esc_html($vidText); ?></span>
         </<?= $vidTag; ?>>
      <?php endif; ?>

      <?php if ($stats) : ?>
         <ul class="dk-stats dk-stats--hero">
            <?php foreach ($stats as $row) : ?>
               <li class="dk-stats__item">
                  <span class="dk-stats__value heading-xs" data-count-to="<?= esc_attr((string) ($row['stat_value'] ?? '')); ?>">
                     <?= esc_html((string) ($row['stat_value'] ?? '')); ?>
                  </span>
                  <span class="dk-stats__label body-sm"><?= esc_html((string) ($row['stat_label'] ?? '')); ?></span>
               </li>
            <?php endforeach; ?>
         </ul>
      <?php endif; ?>

   </div>
</section>
