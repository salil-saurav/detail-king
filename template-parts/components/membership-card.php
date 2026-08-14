<?php

/**
 * Membership plan card.
 *
 *   get_template_part('template-parts/components/membership-card', null, ['plan' => $post]);
 *
 * Carries its own `data-animate`, so a row of plans resolves card by card
 * instead of the whole section arriving on one frame — the client's animation
 * reference does exactly this (TASK-BRIEF.md §3). The stagger index is assigned
 * at runtime by initScrollAnimations(), which numbers siblings that cross the
 * threshold together; nothing here needs to know its own position.
 *
 * CTA (BUILD-PLAN §7 Phase 1 step 9): when the plan's `plan_product` (set by
 * seed/membership-products.php) resolves to a real purchasable product, the
 * CTA becomes a real add-to-cart button using the same generic
 * [data-dk-add-to-cart] mechanism CrossSellService/cross-sell.js already
 * builds for the recommendation modal — clicking it opens that same modal
 * with cross-sell recommendations, exactly like a normal product add. Falls
 * back to the original `/memberships/` link when no product is linked, so a
 * plan can never render a broken buy button.
 *
 * Account mode (`'account' => true`, used only by
 * woocommerce/myaccount/dashboard.php): a state-aware view of a plan the
 * customer already owns — "member since" / an *estimated* renewal date
 * (from MembershipAccountService, real sync is Stripe Billing's job, not
 * built here) and a "Manage / Cancel" data-sp-toggle drawer with an honest
 * message, rather than the sales pitch below. Extending this one component
 * rather than forking a second, per this codebase's own house rule.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$plan = $args['plan'] ?? null;

if (!$plan instanceof WP_Post) {
   return;
}

$account = (bool) ($args['account'] ?? false);

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

$productId = (int) $meta->field('plan_product', $id, 0);
$product   = ($productId > 0 && function_exists('wc_get_product')) ? wc_get_product($productId) : null;
$canBuy    = $product instanceof \WC_Product && $product->is_purchasable() && $product->is_in_stock();

if ($account) :
   $since            = (string) ($args['since'] ?? '');
   $estimatedRenewal = (string) ($args['estimated_renewal'] ?? '');
   $manageId         = 'plan-manage-' . $id;
?>
<article class="plan-card plan-card--account<?= $featured ? ' plan-card--featured' : ''; ?>">

   <header class="plan-card__head">
      <span class="eyebrow eyebrow--badge"><?php esc_html_e('Active Membership', 'detailking'); ?></span>
      <h3 class="plan-card__title subheading-md"><?= esc_html(get_the_title($plan)); ?></h3>
   </header>

   <dl class="plan-card__meta">
      <?php if ($since !== '') : ?>
         <div><dt><?php esc_html_e('Member since', 'detailking'); ?></dt><dd><?= esc_html($since); ?></dd></div>
      <?php endif; ?>
      <?php if ($estimatedRenewal !== '') : ?>
         <div><dt><?php esc_html_e('Next renewal (estimated)', 'detailking'); ?></dt><dd><?= esc_html($estimatedRenewal); ?></dd></div>
      <?php endif; ?>
   </dl>

   <button type="button" class="btn-outline-light-dk plan-card__manage-toggle" data-sp-toggle="<?= esc_attr($manageId); ?>">
      <?php esc_html_e('Manage / Cancel', 'detailking'); ?>
   </button>

   <div class="plan-card__manage" id="<?= esc_attr($manageId); ?>">
      <p class="body-sm">
         <?php esc_html_e('Self-serve cancellation and billing management are coming soon. To manage or cancel your membership today, please contact us directly and we\'ll take care of it.', 'detailking'); ?>
      </p>
      <a class="btn-dark" href="<?= esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact Us', 'detailking'); ?></a>
   </div>

</article>
<?php
   return;
endif;
?>
<article class="plan-card<?= $featured ? ' plan-card--featured' : ''; ?>" data-animate>

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

   <?php if ($ctaText !== '' && $canBuy) : ?>
      <button type="button"
         class="<?= $featured ? 'btn-gold btn-arrow' : 'btn-dark'; ?> plan-card__cta"
         data-dk-add-to-cart
         data-product-id="<?= esc_attr((string) $productId); ?>">
         <?= esc_html($ctaText); ?>
      </button>
   <?php elseif ($ctaText !== '') : ?>
      <a class="<?= $featured ? 'btn-gold btn-arrow' : 'btn-dark'; ?> plan-card__cta"
         href="<?= esc_url($ctaUrl ?: home_url('/memberships/')); ?>">
         <?= esc_html($ctaText); ?>
      </a>
   <?php endif; ?>

</article>
