<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * FAQ answer field. The question is the post title, so it is not duplicated here.
 *
 * Which page an FAQ appears on is the `faq_group` taxonomy, not a field — that way
 * one FAQ can appear on several pages without being copied.
 */
class PostTypeMeta_Faq extends AbstractPostTypeMeta
{
   protected function postType(): string
   {
      return 'dk_faq';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_faq';
   }

   protected function groupTitle(): string
   {
      return __('Answer', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'faq';
   }

   protected function fields(): array
   {
      return [
         $this->field('faq_answer', __('Answer', 'detailking'), 'wysiwyg', [
            'tabs'         => 'visual',
            'media_upload' => 0,
            'toolbar'      => 'basic',
         ]),
      ];
   }
}
