<?php

/**
 * Membership plan card.
 *
 *   get_template_part('template-parts/components/membership-card', null, ['plan' => $post]);
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$plan = $args['plan'] ?? null;

if (!$plan instanceof WP_Post) {
   return;
}

$meta = MetaHelper::getInstance();
$id   = $plan->ID;

$featured = (bool) $meta->field('plan_is_featured', $id, false);
$badge    = (string) $meta->field('plan_badge', $id, '');
$currency = (string) $meta->field('plan_currency', $id, '$');
$price    = (string) $meta->field('plan_price', $id, '');
$period   = (string) $meta->field('plan_period', $id, '/ month');
$tagline  = (string) $meta->field('plan_tagline', $id, '');
$ctaText  = (string) $meta->field('plan_cta_text', $id, __('Join Membership', 'detailking'));
$ctaUrl   = (string) $meta->field('plan_cta_url', $id, '');
$features = $meta->fieldRows('plan_features', $id);
?>
<article class="plan-card<?= $featured ? ' plan-card--featured' : ''; ?>">

   <?php if ($featured && $badge !== '') : ?>
      <span class="plan-card__badge body-base-med"><?= esc_html($badge); ?></span>
   <?php endif; ?>

   <header class="plan-card__head">
      <h3 class="plan-card__title subheading-md"><?= esc_html(get_the_title($plan)); ?></h3>
      <?php if ($tagline !== '') : ?>
         <p class="plan-card__tagline body-sm"><?= esc_html($tagline); ?></p>
      <?php endif; ?>
   </header>

   <?php if ($price !== '') : ?>
      <p class="plan-card__price">
         <span class="plan-card__currency"><?= esc_html($currency); ?></span>
         <span class="plan-card__amount display-sm text-gold-gradient"><?= esc_html($price); ?></span>
         <?php if ($period !== '') : ?>
            <span class="plan-card__period body-sm"><?= esc_html($period); ?></span>
         <?php endif; ?>
      </p>
   <?php endif; ?>

   <?php if ($features) : ?>
      <ul class="plan-card__features">
         <?php foreach ($features as $row) : ?>
            <li>
               <span class="plan-card__tick" aria-hidden="true"></span>
               <span class="body-base"><?= esc_html((string) ($row['feature_text'] ?? '')); ?></span>
            </li>
         <?php endforeach; ?>
      </ul>
   <?php endif; ?>

   <?php if ($ctaText !== '') : ?>
      <a class="<?= $featured ? 'btn-gold btn-arrow' : 'btn-dark'; ?> plan-card__cta"
         href="<?= esc_url($ctaUrl ?: home_url('/memberships/')); ?>">
         <?= esc_html($ctaText); ?>
      </a>
   <?php endif; ?>

</article>
