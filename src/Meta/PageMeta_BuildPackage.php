<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Build Your Package page field group.
 *
 * Bound to pages/template-build-package.php.
 * Hero banner and Help CTA copy only — vehicle sizes come from GlobalFields,
 * services from the dk_service CPT, packages and add-ons from dk_package.
 */
class PageMeta_BuildPackage extends AbstractPageMeta
{
   protected function template(): string
   {
      return 'pages/template-build-package.php';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_build_package';
   }

   protected function groupTitle(): string
   {
      return __('Build Your Package Content', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'byop';
   }

   protected function defaultsSlug(): ?string
   {
      return 'buildpackage';
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
            'instructions' => __('Joined inline after Title (e.g. "BUILD YOUR" + "PACKAGE").', 'detailking'),
         ]),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),

         /* ═══════════ NEED A HAND? (HELP CTA) ═══════════ */
         $this->tab('tab_help', __('Need A Hand? CTA', 'detailking')),
         $this->field('help_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('help_title', __('Title', 'detailking')),
         $this->field('help_title_gold', __('Title — gold line', 'detailking')),
         $this->field('help_text', __('Description', 'detailking'), 'textarea', ['rows' => 4]),
         $this->field('help_primary_text', __('Button Text', 'detailking')),
         $this->field('help_primary_url', __('Button URL', 'detailking')),
         $this->field('help_image', __('Studio / Vehicle Photo', 'detailking'), 'image', $img),
      ];
   }
}
