<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * One extra field on the Woo product edit screen: the small "compatible
 * surfaces" tag row shown on Product Detail (node 185:8178 — "✓ Paint",
 * "✓ Headlights", "✓ Trim", "✓ Wheels"). A plain comma-separated field rather
 * than a full Woo product attribute + taxonomy: per woocommerce.md, extra
 * product data belongs on product meta OR an attribute, and this is purely
 * decorative copy with no filtering/variation use, so the lighter of the two
 * is the right call.
 */
class PostTypeMeta_Product extends AbstractPostTypeMeta
{
   protected function postType(): string
   {
      return 'product';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_product';
   }

   protected function groupTitle(): string
   {
      return __('Detail Card', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'product';
   }

   protected function fields(): array
   {
      return [
         $this->field('compat_tags', __('Compatible Surfaces', 'detailking'), 'text', [
            'instructions' => __('Comma-separated, e.g. "Paint, Headlights, Trim, Wheels". Leave blank to hide the row.', 'detailking'),
         ]),
      ];
   }
}
