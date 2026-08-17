<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Protection Finder page field group.
 *
 * Bound to pages/template-protection-finder.php. Hero copy only — the wizard's
 * 5 questions/options and the result card come from
 * build/figma-data/protection-finder-scoring.md's locked rubric and the
 * matched dk_service post's own fields, not from page-level ACF fields.
 */
class PageMeta_ProtectionFinder extends AbstractPageMeta
{
   protected function template(): string
   {
      return 'pages/template-protection-finder.php';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_protection_finder';
   }

   protected function groupTitle(): string
   {
      return __('Protection Finder Page Content', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'pf';
   }

   protected function defaultsSlug(): ?string
   {
      return 'protectionfinder';
   }

   protected function fields(): array
   {
      $img = $this->imageField();

      return [
         /* ═══════════ HERO ═══════════ */
         $this->tab('tab_hero', __('Hero', 'detailking')),
         $this->field('hero_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('hero_title', __('Title', 'detailking')),
         $this->field('hero_title_gold', __('Title — gold word', 'detailking')),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),
      ];
   }
}
