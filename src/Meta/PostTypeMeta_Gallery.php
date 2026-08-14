<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Gallery CPT field group.
 *
 * Attached to `dk_gallery` post type. The CPT supports native title, thumbnail,
 * and page-attributes (menu_order), and is attached to the shared
 * `service_category` taxonomy.
 *
 * This meta group supplies per-item customisations beyond native fields:
 * custom eyebrow text, optional caption, and custom destination link URL.
 */
class PostTypeMeta_Gallery extends AbstractPostTypeMeta
{
   protected function postType(): string
   {
      return 'dk_gallery';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_gallery';
   }

   protected function groupTitle(): string
   {
      return __('Gallery Item Details', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'gallery_item';
   }

   protected function fields(): array
   {
      return [
         $this->field('gallery_eyebrow', __('Eyebrow Tag', 'detailking'), 'text', [
            'default_value' => 'DETAIL KING',
            'instructions'  => __('Small tag shown above the card title on hover (e.g. "DETAIL KING", vehicle make or service type).', 'detailking'),
         ]),
         $this->field('gallery_caption', __('Caption', 'detailking'), 'textarea', [
            'rows'         => 2,
            'instructions' => __('Optional brief note or specification shown on hover.', 'detailking'),
         ]),
         $this->field('gallery_link_url', __('Custom Link URL', 'detailking'), 'url', [
            'instructions' => __('Optional custom target URL. If blank, links to the relevant service or #.', 'detailking'),
         ]),
      ];
   }
}
