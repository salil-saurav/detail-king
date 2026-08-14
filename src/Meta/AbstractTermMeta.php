<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Base class for field groups attached to a taxonomy term rather than a page
 * template or post type.
 *
 * Reuses everything AbstractPageMeta provides and only swaps the ACF location
 * rule. Values are read with MetaHelper::field($name, $term) — ACF accepts a
 * WP_Term object directly as the "post id" for a term-context field.
 */
abstract class AbstractTermMeta extends AbstractPageMeta
{
   /** The taxonomy this group attaches to, e.g. 'product_cat'. */
   abstract protected function taxonomy(): string;

   /** Unused for a term group; kept satisfied for the parent contract. */
   protected function template(): string
   {
      return '';
   }

   protected function location(): array
   {
      return [[['param' => 'taxonomy', 'operator' => '==', 'value' => $this->taxonomy()]]];
   }
}
