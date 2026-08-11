<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * About page field group.
 *
 * Bound to pages/template-about.php via the inherited page_template location
 * rule. Everything on this page is page-level — none of it repeats on another
 * page frame (see AboutDefaults for the confirmation against the homepage's
 * own stats, which is the recurring test for "global option vs page field").
 */
class PageMeta_About extends AbstractPageMeta
{
   protected function template(): string
   {
      return 'pages/template-about.php';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_about';
   }

   protected function groupTitle(): string
   {
      return __('About Page Content', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'about';
   }

   protected function defaultsSlug(): ?string
   {
      return 'about';
   }

   protected function fields(): array
   {
      $img = $this->imageArgs();

      return [
         /* ═══════════ HERO ═══════════ */
         $this->tab('tab_hero', __('Hero', 'detailking')),
         $this->field('hero_title', __('Title', 'detailking')),
         $this->field('hero_title_gold', __('Title — gold line', 'detailking'), 'text', [
            'instructions' => __('Rendered on its own line in the gold gradient.', 'detailking'),
         ]),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 2]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),

         /* ═══════════ WHO WE ARE ═══════════ */
         $this->tab('tab_who', __('Who We Are', 'detailking')),
         $this->field('who_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('who_title', __('Title', 'detailking')),
         $this->field('who_title_gold', __('Title — gold tail', 'detailking')),
         $this->field('who_text_1', __('Paragraph 1', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('who_text_2', __('Paragraph 2', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('who_image', __('Photo', 'detailking'), 'image', $img),
         $this->field('who_badge_year', __('Photo Badge — Year', 'detailking')),
         $this->field('who_badge_text', __('Photo Badge — Text', 'detailking')),

         /* ═══════════ OUR STORY ═══════════ */
         $this->tab('tab_story', __('Our Story', 'detailking')),
         $this->field('story_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('story_title', __('Title', 'detailking')),
         $this->field('story_title_gold', __('Title — gold lines', 'detailking'), 'text', [
            'instructions' => __('Rendered on its own line(s), starting after a forced break from Title.', 'detailking'),
         ]),
         $this->field('story_text_1', __('Paragraph 1', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('story_text_2', __('Paragraph 2', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('story_text_3', __('Paragraph 3', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('story_image', __('Photo', 'detailking'), 'image', $img),
         $this->field('story_watermark', __('Background Watermark', 'detailking')),

         /* ═══════════ EQUIPMENT ═══════════ */
         $this->tab('tab_equip', __('Equipment', 'detailking')),
         $this->field('equip_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('equip_title', __('Title', 'detailking')),
         $this->field('equip_title_gold', __('Title — gold tail', 'detailking')),
         $this->field('equip_text', __('Lead Text', 'detailking'), 'textarea', ['rows' => 2]),
         $this->field('equip_watermark', __('Background Watermark', 'detailking')),
         $this->repeater('about_equipment', __('Equipment Cards', 'detailking'), [
            $this->field('equip_glyph', __('Icon', 'detailking'), 'select', [
               'choices'       => $this->glyphChoices(),
               'default_value' => 'gear',
            ]),
            $this->field('equip_title', __('Title', 'detailking')),
            $this->field('equip_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         ], ['button_label' => __('Add Card', 'detailking')]),

         /* ═══════════ OUR APPROACH ═══════════ */
         $this->tab('tab_approach', __('Our Approach', 'detailking')),
         $this->field('approach_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('approach_title', __('Title', 'detailking')),
         $this->field('approach_title_gold', __('Title — gold tail', 'detailking')),
         $this->field('approach_text_1', __('Paragraph 1', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('approach_text_2', __('Paragraph 2', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('approach_image', __('Photo', 'detailking'), 'image', $img),
         $this->repeater('about_steps', __('Process Steps', 'detailking'), [
            $this->field('step_number', __('Number', 'detailking'), 'text', ['instructions' => __('e.g. 01', 'detailking')]),
            $this->field('step_title', __('Title', 'detailking')),
            $this->field('step_text', __('Description', 'detailking'), 'textarea', ['rows' => 2]),
         ], ['button_label' => __('Add Step', 'detailking')]),

         /* ═══════════ STATS ═══════════ */
         $this->tab('tab_stats', __('Stats', 'detailking')),
         $this->repeater('about_stats', __('Stats', 'detailking'), [
            $this->field('stat_value', __('Value', 'detailking')),
            $this->field('stat_label', __('Label', 'detailking')),
         ], ['button_label' => __('Add Stat', 'detailking'), 'max' => 6]),

         /* ═══════════ CTA ═══════════ */
         $this->tab('tab_cta', __('CTA', 'detailking')),
         $this->field('cta_title', __('Title', 'detailking')),
         $this->field('cta_title_gold', __('Title — gold line', 'detailking')),
         $this->field('cta_text', __('Text', 'detailking'), 'textarea', ['rows' => 2]),
         ...$this->linkFields('cta_primary', __('Primary CTA', 'detailking')),
         ...$this->linkFields('cta_secondary', __('Secondary CTA', 'detailking')),
      ];
   }

   /** @return array<string,string> */
   private function glyphChoices(): array
   {
      return [
         'crown'   => 'Crown',
         'sparkle' => 'Sparkle',
         'diamond' => 'Diamond',
         'hexagon' => 'Hexagon',
         'gear'    => 'Gear',
         'spark'   => 'Spark',
         'shield'  => 'Shield',
      ];
   }
}
