<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Contact page field group.
 *
 * Bound to pages/template-contact.php.
 */
class PageMeta_Contact extends AbstractPageMeta
{
   protected function template(): string
   {
      return 'pages/template-contact.php';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_contact';
   }

   protected function groupTitle(): string
   {
      return __('Contact Page Content', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'contact';
   }

   protected function defaultsSlug(): ?string
   {
      return 'contact';
   }

   protected function fields(): array
   {
      $img = $this->imageArgs();

      return [
         /* ═══════════ HERO ═══════════ */
         $this->tab('tab_hero', __('Hero', 'detailking')),
         $this->field('hero_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('hero_title', __('Title', 'detailking')),
         $this->field('hero_title_gold', __('Title — gold word', 'detailking'), 'text', [
            'instructions' => __('Joined inline after Title (e.g. "Contact" + "Us").', 'detailking'),
         ]),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),

         /* ═══════════ FORM & DIRECT CONTACT ═══════════ */
         $this->tab('tab_form', __('Form & Direct Info', 'detailking')),
         $this->field('form_title', __('Form Title', 'detailking')),
         $this->field('form_title_gold', __('Form Title — gold word', 'detailking')),
         $this->field('form_text', __('Form Subtitle', 'detailking'), 'textarea', ['rows' => 2]),
         $this->field('form_submit_text', __('Submit Button Text', 'detailking')),
         $this->field('direct_title', __('Direct Contact Title', 'detailking')),
         $this->field('direct_phone', __('Direct Phone', 'detailking')),
         $this->field('direct_email', __('Direct Email', 'detailking')),
         $this->field('direct_hours', __('Direct Hours', 'detailking')),
         $this->field('direct_locations_title', __('Locations Summary Title', 'detailking')),

         /* ═══════════ STUDIOS SECTION ═══════════ */
         $this->tab('tab_studios', __('Studios Section', 'detailking')),
         $this->field('studios_eyebrow', __('Section Eyebrow', 'detailking')),
         $this->field('studios_title', __('Section Title', 'detailking')),
         $this->field('studios_title_gold', __('Section Title — gold lines', 'detailking'), 'textarea', [
            'rows'         => 2,
            'instructions' => __('Use line breaks for multiple gold lines (e.g. "Two\nStudios").', 'detailking'),
         ]),
         $this->field('studios_text', __('Section Description', 'detailking'), 'textarea', ['rows' => 2]),
      ];
   }
}
