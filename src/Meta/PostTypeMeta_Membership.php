<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Membership plan fields. Rendered on the homepage and on the Memberships page from
 * this one source.
 */
class PostTypeMeta_Membership extends AbstractPostTypeMeta
{
   protected function postType(): string
   {
      return 'dk_membership';
   }

   protected function groupKey(): string
   {
      return 'group_detailking_membership';
   }

   protected function groupTitle(): string
   {
      return __('Plan Details', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'plan';
   }

   protected function fields(): array
   {
      return [
         $this->field('plan_tagline', __('Tagline', 'detailking'), 'text', [
            'instructions' => __('One line under the plan name.', 'detailking'),
         ]),
         $this->field('plan_currency', __('Currency Symbol', 'detailking'), 'text', ['default_value' => '$']),
         $this->field('plan_price', __('Monthly Price', 'detailking'), 'text'),
         $this->field('plan_period', __('Period Label', 'detailking'), 'text', ['default_value' => '/ month']),
         $this->field('plan_is_featured', __('Featured Plan', 'detailking'), 'true_false', [
            'ui'           => 1,
            'instructions' => __('Renders the dark, taller card with the badge.', 'detailking'),
         ]),
         $this->field('plan_badge', __('Badge Text', 'detailking'), 'text', [
            'default_value' => '★ Most Popular',
            'conditional_logic' => [[[
               'field'    => $this->fieldKey('plan_is_featured'),
               'operator' => '==',
               'value'    => '1',
            ]]],
         ]),
         $this->repeater('plan_features', __('Included', 'detailking'), [
            $this->field('feature_text', __('Feature', 'detailking')),
         ], ['button_label' => __('Add Feature', 'detailking')]),
         ...$this->linkFields('plan_cta', __('CTA', 'detailking')),
      ];
   }
}
