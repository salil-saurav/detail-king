<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Shop landing page field group (WooCommerce's Shop page, rendered by
 * woocommerce/archive-product.php's is_shop() branch as a category-tile
 * grid — see figma-data/shop-spec.md).
 *
 * Bound directly to the Shop page's post ID rather than a page_template rule:
 * WooCommerce forces its own template hierarchy for this page regardless of
 * what's assigned in the editor (same reason PageMeta_Homepage binds on
 * `page_type == front_page` instead of a template).
 */
class PageMeta_Shop extends AbstractPageMeta
{
   /** Unused: this group binds to a specific page ID, not an assignable template. */
   protected function template(): string
   {
      return '';
   }

   protected function location(): array
   {
      $shopId = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;

      return [[['param' => 'page', 'operator' => '==', 'value' => (string) $shopId]]];
   }

   protected function groupKey(): string
   {
      return 'group_detailking_shop';
   }

   protected function groupTitle(): string
   {
      return __('Shop Page Content', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'shoppage';
   }

   protected function defaultsSlug(): ?string
   {
      return 'shop';
   }

   protected function fields(): array
   {
      $img = $this->imageArgs();

      return [
         /* ═══════════ HERO ═══════════ */
         $this->tab('tab_hero', __('Hero', 'detailking')),
         $this->field('hero_eyebrow', __('Badge', 'detailking')),
         $this->field('hero_title', __('Title', 'detailking')),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),

         /* ═══════════ WHY SHOP ═══════════ */
         $this->tab('tab_why', __('Why Shop', 'detailking')),
         $this->field('why_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('why_heading', __('Heading', 'detailking')),
         $this->field('why_heading_gold', __('Heading — gold tail', 'detailking')),
         $this->field('why_text', __('Text', 'detailking'), 'textarea', ['rows' => 2]),
         $this->field('why_watermark', __('Watermark', 'detailking')),
         $this->repeater('why_features', __('Features', 'detailking'), [
            $this->field('feature_icon_glyph', __('Icon', 'detailking'), 'select', [
               'choices' => [
                  'truck'   => 'Truck',
                  'shield'  => 'Shield',
                  'clock'   => 'Clock',
                  'headset' => 'Headset',
                  'crown'   => 'Crown',
                  'sparkle' => 'Sparkle',
                  'diamond' => 'Diamond',
                  'hexagon' => 'Hexagon',
                  'gear'    => 'Gear',
                  'spark'   => 'Spark',
               ],
               'default_value' => 'shield',
            ]),
            $this->field('feature_title', __('Title', 'detailking')),
            $this->field('feature_text', __('Text', 'detailking')),
         ], [
            'button_label' => __('Add Feature', 'detailking'),
            'max'          => 4,
         ]),
      ];
   }
}
