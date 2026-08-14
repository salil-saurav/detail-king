<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Post fields for native WordPress posts (The Detail King Journal / Blog).
 *
 * Provides optional editorial controls:
 *  - reading time (auto-calculated from word count when blank)
 *  - featured post flag (shown prominently on the Blog Index)
 *  - custom sidebar CTA overrides for single post view
 */
class PostTypeMeta_Post extends AbstractPostTypeMeta
{
   protected function postType(): string
   {
      return 'post';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_post_meta';
   }

   protected function groupTitle(): string
   {
      return __('Post Settings & Sidebar CTA', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'post';
   }

   protected function fields(): array
   {
      return [
         $this->tab('post_settings_tab', __('Article Settings', 'detailking')),
         $this->field('reading_time', __('Reading Time', 'detailking'), 'text', [
            'instructions' => __('e.g. "6 min read". Leave blank to calculate automatically from content length.', 'detailking'),
            'placeholder'  => '6 min read',
         ]),
         $this->field('featured_post', __('Featured Article', 'detailking'), 'true_false', [
            'instructions' => __('Highlight this article in the featured slot on the Blog Index page.', 'detailking'),
            'ui'           => 1,
         ]),

         $this->tab('post_sidebar_tab', __('Sidebar CTA', 'detailking')),
         $this->field('sidebar_cta_title', __('CTA Title', 'detailking'), 'text', [
            'default_value' => 'Protect Your Paint',
            'placeholder'   => 'Protect Your Paint',
         ]),
         $this->field('sidebar_cta_text', __('CTA Description', 'detailking'), 'textarea', [
            'rows'          => 3,
            'default_value' => 'Ready for a coating that lasts years, not weeks? Book your Ceramic Pro with our team.',
         ]),
         $this->field('sidebar_cta_button_text', __('CTA Button Label', 'detailking'), 'text', [
            'default_value' => 'Book Ceramic Pro',
            'placeholder'   => 'Book Ceramic Pro',
         ]),
         $this->field('sidebar_cta_button_url', __('CTA Button URL', 'detailking'), 'text', [
            'default_value' => '/services/ceramic-pro/',
            'placeholder'   => '/services/ceramic-pro/',
         ]),
      ];
   }
}
