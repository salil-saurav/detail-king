<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Base class for field groups attached to a custom post type rather than to a page
 * template.
 *
 * Reuses everything AbstractPageMeta provides (auto-discovery, the field builders,
 * the key namespace, the defaults provider) and only swaps the ACF location rule.
 * Subclasses declare postType() instead of template().
 */
abstract class AbstractPostTypeMeta extends AbstractPageMeta
{
   /** The post type this group attaches to, e.g. 'dk_membership'. */
   abstract protected function postType(): string;

   /** Unused for a post-type group; kept satisfied for the parent contract. */
   protected function template(): string
   {
      return '';
   }

   protected function location(): array
   {
      return [[['param' => 'post_type', 'operator' => '==', 'value' => $this->postType()]]];
   }
}
