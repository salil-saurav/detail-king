<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * One extra field on the product_cat term edit screen: the small tracking-caps
 * eyebrow tag the comp shows on every category card ("9H · LIGHT · SPORT",
 * "RAIN · GLASS"). Everything else the card needs is already native: the term's
 * own name, its Description textarea (card body copy) and WooCommerce's own
 * category Thumbnail (card image) — no reason to duplicate those as ACF fields.
 *
 * No defaultsSlug(): a single field name is shared by every term instance, so
 * there is no one sensible ACF default_value across 8 different categories.
 * The real per-term copy is written directly by seed/product-categories.php.
 */
class TermMeta_ProductCategory extends AbstractTermMeta
{
   protected function taxonomy(): string
   {
      return 'product_cat';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_product_category';
   }

   protected function groupTitle(): string
   {
      return __('Category Card', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'productcat';
   }

   protected function fields(): array
   {
      return [
         $this->field('category_tagline', __('Card Eyebrow', 'detailking'), 'text', [
            'instructions' => __('Short tracking-caps tag shown on the category card, e.g. "9H · LIGHT · SPORT".', 'detailking'),
         ]),
      ];
   }
}
