<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Our Services page field group.
 *
 * Bound to pages/template-services.php. Only the hero banner and the closing
 * CTA are page-level fields here — the filter pills come from the live
 * `service_category` terms and the card grid comes from the `dk_service` CPT
 * (see PostTypeMeta_Service), neither of which belongs in a page field group.
 */
class PageMeta_Services extends AbstractPageMeta
{
   protected function template(): string
   {
      return 'pages/template-services.php';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_services';
   }

   protected function groupTitle(): string
   {
      return __('Our Services Page Content', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'svcpage';
   }

   protected function defaultsSlug(): ?string
   {
      return 'services';
   }

   protected function fields(): array
   {
      $img = $this->imageArgs();

      return [
         /* ═══════════ HERO ═══════════ */
         $this->tab('tab_hero', __('Hero', 'detailking')),
         $this->field('hero_title', __('Title', 'detailking')),
         $this->field('hero_title_gold', __('Title — gold word', 'detailking'), 'text', [
            'instructions' => __('Joined inline after Title (not a forced line break) — the comp reads "Our Services" on one line.', 'detailking'),
         ]),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),

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
