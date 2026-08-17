<?php

/**
 * Reusable dark CTA card: rounded card, radial gold glow, two-tone heading,
 * a lead paragraph and a primary/secondary button pair.
 *
 * Recurs on About, Services, Gallery and Blog with different copy each time —
 * the *component* is shared, the *copy* is not (see AboutDefaults), so every
 * consuming page passes its own args from its own field group. Every arg has
 * a sane fallback so a page that forgets one never renders an empty card.
 *
 *   get_template_part('template-parts/global/cta-banner', null, [
 *      'title'          => 'Ready To Give Your Car',
 *      'gold'           => 'The Royal Treatment?',
 *      'text'           => 'Comprehensive auto detailing…',
 *      'primary_text'   => 'Book a Service',
 *      'primary_url'    => '/contact/',
 *      'secondary_text' => 'Explore Services',
 *      'secondary_url'  => '/services/',
 *   ]);
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$title    = isset($args['title']) ? (string) $args['title'] : 'Ready To Give Your Car';
$gold     = isset($args['gold'])  ? (string) $args['gold']  : 'The Royal Treatment?';
$text     = isset($args['text'])  ? (string) $args['text']  : '';

$primaryText   = isset($args['primary_text'])   ? (string) $args['primary_text']   : 'Book a Service';
$primaryUrl    = isset($args['primary_url'])    ? (string) $args['primary_url']    : home_url('/contact/');
$secondaryText = isset($args['secondary_text']) ? (string) $args['secondary_text'] : '';
$secondaryUrl  = isset($args['secondary_url'])  ? (string) $args['secondary_url']  : home_url('/services/');
?>
<?php
/* The zoom reveal lives on the CARD, not on the consuming section — same
   correction the homepage's seam-image already carries, for the same reason.
   motion.js holds a data-animate="zoom" element at scale(1.08) until it
   reveals; on a full-bleed <section> that grew the band 8% past the viewport
   and scrolled the document sideways by 76px at 1920 for as long as the
   section sat unrevealed below the fold. Scaling the container-capped card
   keeps the growth inside the gutters, and matches the spec's own reading
   (§15: the card scales and lifts, the band does not). global.css clips the
   residual on narrower viewports, where the gutter is thinner than the growth. */
?>
<div class="dk-cta glow-gold" data-animate="zoom">
   <h2 class="dk-cta__title">
      <?= esc_html($title); ?>
      <?php if ($gold !== '') : ?>
         <br><span class="text-gold-gradient"><?= esc_html($gold); ?></span>
      <?php endif; ?>
   </h2>

   <?php if ($text !== '') : ?>
      <p class="dk-cta__text body-lg"><?= esc_html($text); ?></p>
   <?php endif; ?>

   <div class="dk-cta__actions">
      <?php if ($primaryText !== '') : ?>
         <a class="btn-gold btn-arrow" href="<?= esc_url($primaryUrl); ?>"><?= esc_html($primaryText); ?></a>
      <?php endif; ?>
      <?php if ($secondaryText !== '') : ?>
         <a class="btn-outline-light-dk" href="<?= esc_url($secondaryUrl); ?>"><?= esc_html($secondaryText); ?></a>
      <?php endif; ?>
   </div>
</div>
