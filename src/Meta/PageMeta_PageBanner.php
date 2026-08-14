<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Banner for any Page still on WordPress' "Default Template" — Cart,
 * Checkout, My Account, Contact, Memberships, Login, Signup and every other
 * page that hasn't (yet) grown its own dedicated page_template + PageMeta
 * class. None of these have a Figma comp (see TASK-BRIEF.md §11 "Missing
 * designs"), so this gives editors the same eyebrow/title/text/bg-image
 * control the designed pages get, via the shared page-banner part, instead
 * of leaving them on the unstyled fallback header in page.php.
 *
 * Once a page like Gallery or Blog gets its own dedicated template + PageMeta
 * group, ACF's 'default' page_template location rule simply stops matching
 * it — no cleanup needed here.
 */
class PageMeta_PageBanner extends AbstractPageMeta
{
   protected function template(): string
   {
      return 'default';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_page_banner';
   }

   protected function groupTitle(): string
   {
      return __('Page Banner', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'pagebanner';
   }

   protected function fields(): array
   {
      return [
         $this->field('hero_eyebrow', __('Badge', 'detailking')),
         $this->field('hero_title', __('Title', 'detailking'), 'text', [
            'instructions' => __('Leave blank to use the page title.', 'detailking'),
         ]),
         $this->field('hero_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $this->imageArgs()),
      ];
   }
}
