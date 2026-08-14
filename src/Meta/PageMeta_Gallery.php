<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Gallery page field group.
 *
 * Bound to pages/template-gallery.php. Hero banner, gallery empty state, and
 * the closing CTA are page-level fields. Filter categories are derived live
 * from the `service_category` taxonomy terms and the gallery cards from the
 * `dk_gallery` CPT.
 */
class PageMeta_Gallery extends AbstractPageMeta
{
   protected function template(): string
   {
      return 'pages/template-gallery.php';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_gallery_page';
   }

   protected function groupTitle(): string
   {
      return __('Gallery Page Content', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'gallery_page';
   }

   protected function defaultsSlug(): ?string
   {
      return 'gallery';
   }

   protected function fields(): array
   {
      $img = $this->imageArgs();

      return [
         /* ═══════════ HERO ═══════════ */
         $this->tab('tab_hero', __('Hero', 'detailking')),
         $this->field('hero_eyebrow', __('Badge / Eyebrow', 'detailking')),
         $this->field('hero_title', __('Title', 'detailking')),
         $this->field('hero_title_gold', __('Title — gold word', 'detailking'), 'text', [
            'instructions' => __('Joined inline after Title — e.g. "The <gold>Gallery</gold>".', 'detailking'),
         ]),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),

         /* ═══════════ GALLERY ═══════════ */
         $this->tab('tab_gallery', __('Gallery', 'detailking')),
         $this->field('filter_all_label', __('"All" Filter Label', 'detailking'), 'text', [
            'default_value' => 'All Work',
         ]),
         $this->field('empty_text', __('Empty Filter State Text', 'detailking'), 'text', [
            'default_value' => 'No gallery items found in this category.',
         ]),

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
