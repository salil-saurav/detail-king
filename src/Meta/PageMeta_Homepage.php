<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

defined('ABSPATH') || exit;

/**
 * Homepage field group.
 *
 * Bound to the front page rather than to a page template — front-page.php is
 * selected by WordPress automatically and is never assigned in the editor, so
 * the inherited `page_template` rule would never match.
 *
 * Content NOT held here, and why:
 *   - service cards      → dk_service CPT (they have their own pages)
 *   - membership cards   → dk_membership CPT (reused on the Memberships page)
 *   - reviews            → dk_testimonial CPT
 *   - FAQ items          → dk_faq CPT, filtered by the faq_group taxonomy
 *   - products           → WooCommerce
 *   - ticker, review average/count, contact details → global options, because
 *     they appear on other page frames too
 *
 * The stat repeaters ARE page-level, against what CLAUDE_OPUS_5_PLAN.md proposed:
 * the homepage carries two separate sets (3 in the hero, 4 in the video section)
 * and the About frame's numbers differ again (4,134+ vs 4,800+). Routing them to a
 * shared options page would have one page silently overwrite the other's figures.
 */
class PageMeta_Homepage extends AbstractPageMeta
{
   protected function template(): string
   {
      return 'front-page.php';
   }

   protected function location(): array
   {
      return [[['param' => 'page_type', 'operator' => '==', 'value' => 'front_page']]];
   }

   protected function groupKey(): string
   {
      return 'group_detailking_homepage';
   }

   protected function groupTitle(): string
   {
      return __('Homepage Content', 'detailking');
   }

   protected function keyNamespace(): string
   {
      return 'home';
   }

   protected function defaultsSlug(): ?string
   {
      return 'homepage';
   }

   /** Value + label pair, used by both stat repeaters. */
   private function statSubFields(): array
   {
      return [
         $this->field('stat_value', __('Value', 'detailking')),
         $this->field('stat_label', __('Label', 'detailking')),
      ];
   }

   protected function fields(): array
   {
      $img = $this->imageArgs();

      return [
         /* ═══════════ HERO ═══════════ */
         $this->tab('tab_hero', __('Hero', 'detailking')),
         $this->field('hero_badge', __('Badge', 'detailking')),
         $this->field('hero_heading', __('Heading', 'detailking'), 'textarea', ['rows' => 2]),
         $this->field('hero_heading_gold', __('Heading — gold tail', 'detailking'), 'text', [
            'instructions' => __('Rendered in the gold gradient, continuing the heading.', 'detailking'),
         ]),
         $this->field('hero_text', __('Intro Text', 'detailking'), 'textarea', [
            'rows'         => 3,
            'instructions' => __('Basic HTML allowed — &lt;strong&gt; is used for the emphasised service names.', 'detailking'),
         ]),
         $this->field('hero_bg_image', __('Background Image', 'detailking'), 'image', $img),
         ...$this->linkFields('hero_cta_primary', __('Primary CTA', 'detailking')),
         ...$this->linkFields('hero_cta_secondary', __('Secondary CTA', 'detailking')),
         ...$this->linkFields('hero_video', __('Video Link', 'detailking')),
         $this->repeater('hero_stats', __('Hero Stats', 'detailking'), $this->statSubFields(), [
            'button_label' => __('Add Stat', 'detailking'),
            'max'          => 5,
         ]),

         /* ═══════════ SERVICES ═══════════ */
         $this->tab('tab_services', __('Services', 'detailking')),
         $this->field('services_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('services_heading', __('Heading', 'detailking')),
         $this->field('services_heading_gold', __('Heading — gold tail', 'detailking')),
         ...$this->linkFields('services_cta', __('Section CTA', 'detailking')),
         $this->field('services_card_link_text', __('Card Link Label', 'detailking'), 'text', [
            'instructions' => __('Shown on every service card.', 'detailking'),
         ]),
         $this->field('services_promo_title', __('Promo Card — Title', 'detailking'), 'text', [
            'instructions' => __('The last card is a promo, not a service. Leave the title blank to hide it.', 'detailking'),
         ]),
         $this->field('services_promo_link_text', __('Promo Card — Link Label', 'detailking')),
         $this->field('services_promo_url', __('Promo Card — URL', 'detailking')),
         $this->field('services_promo_image', __('Promo Card — Image', 'detailking'), 'image', $img),
         $this->repeater('services_features', __('Feature Strip', 'detailking'), [
            $this->field('feature_title', __('Title', 'detailking')),
            $this->field('feature_text', __('Text', 'detailking')),
         ], ['button_label' => __('Add Feature', 'detailking')]),

         /* ═══════════ SEAM IMAGE ═══════════ */
         $this->tab('tab_seam', __('Seam Image', 'detailking')),
         $this->field('seam_image', __('Image', 'detailking'), 'image', array_merge($img, [
            'instructions' => __('Sits across the light/dark section boundary. Leave blank to hide the band.', 'detailking'),
         ])),

         /* ═══════════ VIDEO ═══════════ */
         $this->tab('tab_video', __('Studio Video', 'detailking')),
         $this->field('video_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('video_heading', __('Heading', 'detailking')),
         $this->field('video_heading_gold', __('Heading — gold tail', 'detailking')),
         $this->field('video_thumbnail', __('Thumbnail', 'detailking'), 'image', $img),
         $this->field('video_url', __('Video URL', 'detailking'), 'text', [
            'instructions' => __('YouTube or Vimeo. Opens in a lightbox; the play button is hidden when empty.', 'detailking'),
         ]),
         $this->field('video_badge', __('Corner Badge', 'detailking')),
         $this->field('video_meta', __('Corner Meta', 'detailking')),
         $this->field('video_caption', __('Caption', 'detailking')),
         $this->field('video_watermark', __('Watermark', 'detailking')),
         $this->repeater('craft_stats', __('Craft Stats', 'detailking'), $this->statSubFields(), [
            'button_label' => __('Add Stat', 'detailking'),
            'max'          => 6,
         ]),

         /* ═══════════ WHY US ═══════════ */
         $this->tab('tab_why', __('Why Us', 'detailking')),
         $this->field('why_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('why_heading', __('Heading', 'detailking')),
         $this->field('why_heading_gold', __('Heading — gold tail', 'detailking')),
         $this->field('why_watermark', __('Watermark', 'detailking')),
         $this->field('why_image', __('Centre Image', 'detailking'), 'image', $img),
         $this->field('why_image_badge', __('Centre Image Badge', 'detailking')),
         $this->repeater('why_features_left', __('Features — left column', 'detailking'), $this->whyFeatureSubFields(), [
            'button_label' => __('Add Feature', 'detailking'),
         ]),
         $this->repeater('why_features_right', __('Features — right column', 'detailking'), $this->whyFeatureSubFields('r'), [
            'button_label' => __('Add Feature', 'detailking'),
         ]),

         /* ═══════════ BEFORE / AFTER ═══════════ */
         $this->tab('tab_ba', __('Before / After', 'detailking')),
         $this->field('ba_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('ba_heading', __('Heading', 'detailking')),
         $this->field('ba_heading_gold', __('Heading — gold tail', 'detailking')),
         $this->field('ba_text', __('Text', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('ba_label_before', __('"Before" Label', 'detailking')),
         $this->field('ba_label_after', __('"After" Label', 'detailking')),
         $this->repeater('ba_presets', __('Comparison Sets', 'detailking'), [
            $this->field('preset_label', __('Label', 'detailking')),
            $this->field('preset_before', __('Before Image', 'detailking'), 'image', $img),
            $this->field('preset_after', __('After Image', 'detailking'), 'image', $img),
         ], [
            'button_label' => __('Add Comparison', 'detailking'),
            'instructions' => __('Each set switches the slider images. The first is shown on load.', 'detailking'),
         ]),

         /* ═══════════ MEMBERSHIP ═══════════ */
         $this->tab('tab_membership', __('Membership', 'detailking')),
         $this->field('membership_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('membership_heading', __('Heading', 'detailking')),
         $this->field('membership_heading_gold', __('Heading — gold tail', 'detailking')),
         $this->field('membership_text', __('Text', 'detailking'), 'textarea', ['rows' => 2]),
         $this->field('membership_watermark', __('Watermark', 'detailking')),
         $this->field('membership_footnote', __('Footnote', 'detailking')),

         /* ═══════════ SHOP ═══════════ */
         $this->tab('tab_shop', __('Shop', 'detailking')),
         $this->field('shop_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('shop_heading', __('Heading', 'detailking')),
         $this->field('shop_heading_gold', __('Heading — gold tail', 'detailking')),
         ...$this->linkFields('shop_cta', __('Section CTA', 'detailking')),
         $this->field('shop_watermark', __('Watermark', 'detailking')),

         /* ═══════════ TESTIMONIALS ═══════════ */
         $this->tab('tab_reviews', __('Reviews', 'detailking')),
         $this->field('reviews_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('reviews_heading', __('Heading', 'detailking')),
         $this->field('reviews_heading_gold', __('Heading — gold tail', 'detailking')),
         $this->field('reviews_card_title', __('Summary Card Title', 'detailking')),
         $this->field('reviews_card_note', __('Summary Card Note', 'detailking')),

         /* ═══════════ INSTAGRAM ═══════════ */
         $this->tab('tab_instagram', __('Instagram', 'detailking')),
         $this->field('instagram_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('instagram_heading', __('Heading', 'detailking')),
         $this->field('instagram_heading_gold', __('Heading — gold tail', 'detailking')),
         ...$this->linkFields('instagram_cta', __('CTA', 'detailking')),
         $this->repeater('instagram_images', __('Images', 'detailking'), [
            $this->field('ig_image', __('Image', 'detailking'), 'image', $img),
            $this->field('ig_url', __('Link', 'detailking')),
         ], [
            'button_label' => __('Add Image', 'detailking'),
            'instructions' => __('Full-bleed row. Any number works; the row divides evenly.', 'detailking'),
         ]),

         /* ═══════════ BOOKING ═══════════ */
         $this->tab('tab_booking', __('Booking', 'detailking')),
         $this->field('booking_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('booking_heading', __('Heading', 'detailking')),
         $this->field('booking_heading_gold', __('Heading — gold tail', 'detailking')),
         $this->field('booking_text', __('Text', 'detailking'), 'textarea', ['rows' => 3]),
         $this->field('booking_label', __('Gold Label', 'detailking')),
         $this->field('booking_form_title', __('Form Title', 'detailking')),
         $this->field('booking_form_title_gold', __('Form Title — gold tail', 'detailking')),
         $this->field('booking_submit_text', __('Submit Button', 'detailking')),

         /* ═══════════ FAQ ═══════════ */
         $this->tab('tab_faq', __('FAQ', 'detailking')),
         $this->field('faq_eyebrow', __('Eyebrow', 'detailking')),
         $this->field('faq_heading', __('Heading', 'detailking')),
         $this->field('faq_heading_gold', __('Heading — gold tail', 'detailking')),
         $this->field('faq_watermark', __('Watermark', 'detailking')),
         $this->field('faq_image', __('Panel Image', 'detailking'), 'image', $img),
         $this->field('faq_panel_lead', __('Panel Lead (gold)', 'detailking')),
         $this->field('faq_panel_text', __('Panel Text', 'detailking'), 'textarea', ['rows' => 2]),
      ];
   }

   /**
    * Why-us feature sub-fields. The suffix exists only to keep ACF field keys
    * unique between the left and right repeaters, which share sub-field names.
    */
   private function whyFeatureSubFields(string $suffix = 'l'): array
   {
      $glyphs = [
         'crown'   => 'Crown',
         'sparkle' => 'Sparkle',
         'diamond' => 'Diamond',
         'hexagon' => 'Hexagon',
         'gear'    => 'Gear',
         'spark'   => 'Spark',
         'shield'  => 'Shield',
      ];

      return [
         array_merge(
            $this->field('feature_icon_glyph', __('Icon', 'detailking'), 'select', [
               'choices'       => $glyphs,
               'default_value' => 'crown',
            ]),
            ['key' => $this->fieldKey('feature_icon_glyph_' . $suffix)]
         ),
         array_merge(
            $this->field('feature_title', __('Title', 'detailking')),
            ['key' => $this->fieldKey('feature_title_' . $suffix)]
         ),
         array_merge(
            $this->field('feature_text', __('Text', 'detailking')),
            ['key' => $this->fieldKey('feature_text_' . $suffix)]
         ),
      ];
   }
}
