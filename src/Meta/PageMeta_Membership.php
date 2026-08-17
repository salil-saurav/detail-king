<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Membership Plans page field group.
 *
 * Bound to pages/template-memberships.php.
 */
class PageMeta_Membership extends AbstractPageMeta
{
   protected function template(): string
   {
      return 'pages/template-memberships.php';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_memberships_page';
   }

   protected function groupTitle(): string
   {
      return __('Membership Page Content', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'mship';
   }

   protected function defaultsSlug(): ?string
   {
      return 'memberships';
   }

   protected function fields(): array
   {
      $img = $this->imageArgs();

      return [
         /* ═══════════ HERO ═══════════ */
         $this->tab('tab_hero', __('Hero', 'detailking')),
         $this->field('hero_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('hero_title', __('Title', 'detailking')),
         $this->field('hero_title_gold', __('Title — gold word', 'detailking')),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),

         /* ═══════════ PLANS ═══════════ */
         $this->tab('tab_plans', __('Plans', 'detailking')),
         $this->field('plans_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('plans_title', __('Heading', 'detailking')),
         $this->field('plans_title_gold', __('Heading — gold word', 'detailking')),
         $this->field('plans_text', __('Subtext', 'detailking'), 'textarea', ['rows' => 2]),

         /* ═══════════ LOYALTY REWARDS ═══════════ */
         $this->tab('tab_loyalty', __('Loyalty Rewards', 'detailking')),
         $this->field('loyalty_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('loyalty_title', __('Heading', 'detailking')),
         $this->field('loyalty_title_gold', __('Heading — gold word', 'detailking')),
         $this->field('loyalty_text', __('Subtext', 'detailking'), 'textarea', ['rows' => 2]),
         $this->repeater('loyalty_items', __('Loyalty Cards', 'detailking'), [
            $this->field('loyalty_icon', __('Icon (clock/diamond/crown)', 'detailking')),
            $this->field('loyalty_title', __('Title', 'detailking')),
            $this->field('loyalty_text', __('Description', 'detailking'), 'textarea', ['rows' => 2]),
         ], ['button_label' => __('Add Loyalty Card', 'detailking')]),

         /* ═══════════ VALUE / CHECKLIST ═══════════ */
         $this->tab('tab_value', __('Why Become A Member', 'detailking')),
         $this->field('value_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('value_title', __('Heading', 'detailking')),
         $this->field('value_title_gold', __('Heading — gold word', 'detailking')),
         $this->field('value_text', __('Subtext', 'detailking'), 'textarea', ['rows' => 2]),
         $this->field('value_watermark', __('Watermark Word', 'detailking')),
         $this->repeater('value_checklist', __('Checklist Items', 'detailking'), [
            $this->field('item_text', __('Checklist Text', 'detailking')),
         ], ['button_label' => __('Add Checklist Item', 'detailking')]),

         /* ═══════════ CTA ═══════════ */
         $this->tab('tab_cta', __('CTA', 'detailking')),
         $this->field('cta_title', __('Title', 'detailking')),
         $this->field('cta_title_gold', __('Title — gold line', 'detailking')),
         $this->field('cta_text', __('Text', 'detailking'), 'textarea', ['rows' => 2]),
         ...$this->linkFields('cta_primary', __('Primary CTA', 'detailking')),
         ...$this->linkFields('cta_secondary', __('Secondary CTA', 'detailking')),
      ];
   }
}
