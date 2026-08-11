<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Google-review fields for the testimonial carousel.
 *
 * Initials are their own field rather than derived from the name: the comp shows
 * "JM" for "James M.", and deriving that correctly across every name format
 * (single names, three names, non-Latin scripts) is not worth the guesswork when a
 * two-character field is exact. It falls back to a derived value when left blank.
 */
class PostTypeMeta_Testimonial extends AbstractPostTypeMeta
{
   protected function postType(): string
   {
      return 'dk_testimonial';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_testimonial';
   }

   protected function groupTitle(): string
   {
      return __('Review Details', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'review';
   }

   protected function fields(): array
   {
      return [
         $this->field('review_text', __('Review', 'detailking'), 'textarea', ['rows' => 4]),
         $this->field('reviewer_name', __('Reviewer Name', 'detailking')),
         $this->field('reviewer_initials', __('Initials', 'detailking'), 'text', [
            'maxlength'    => 3,
            'instructions' => __('Avatar text. Derived from the name when left blank.', 'detailking'),
         ]),
         $this->field('reviewer_vehicle', __('Vehicle', 'detailking')),
         $this->field('review_date', __('When', 'detailking'), 'text', [
            'instructions' => __('Free text, as Google shows it — e.g. "2 weeks ago".', 'detailking'),
         ]),
         $this->field('review_rating', __('Stars', 'detailking'), 'number', [
            'default_value' => 5,
            'min'           => 1,
            'max'           => 5,
            'step'          => 1,
         ]),
      ];
   }
}
