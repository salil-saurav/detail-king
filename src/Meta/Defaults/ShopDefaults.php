<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Shop landing page (the Woo Shop page, rendered as a category-tile grid —
 * node 183:7236, "Product Categories" — see projects/detail-king/figma-data/
 * shop-spec.md). Bound the same way HomepageDefaults is: this page can't
 * carry an assignable page_template because WooCommerce forces
 * woocommerce/archive-product.php for it regardless of what's assigned.
 *
 * The category tiles themselves are NOT here — they come from the real
 * product_cat terms (name + native Description + native category thumbnail +
 * TermMeta_ProductCategory's tagline field), seeded in
 * seed/product-categories.php. Only the hero and the "Why Shop" band, which
 * don't repeat on any other page frame, are page-level copy.
 *
 * Two copy strings below are marked unconfirmed: the export never resolved
 * their text (Figma returned a generic "Text" node past the token budget for
 * that section). Safe, on-brand placeholders — not comp-accurate.
 */
final class ShopDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         /* ═══════════ HERO / PAGE BANNER ═══════════ */
         'hero_eyebrow'  => 'Pro-Grade Detailing Products', // unconfirmed copy
         'hero_title'    => 'Product Categories',
         'hero_text'     => 'The same pro-grade products our detailers trust in studio — now available to take home. Browse by category below.',
         'hero_bg_image' => '',

         /* ═══════════ WHY SHOP ═══════════ */
         'why_eyebrow'      => 'Why Shop With Us', // unconfirmed copy
         'why_heading'      => 'Pro Products,',
         'why_heading_gold' => 'Delivered',
         'why_text'         => "Everything you need to maintain that showroom finish between visits.", // unconfirmed copy
         'why_watermark'    => 'WHY SHOP',
         'why_features'     => [
            [
               'feature_icon_glyph' => 'truck',
               'feature_title'      => 'Free NZ Shipping',
               'feature_text'       => 'On retail orders over $99 nationwide.',
            ],
            [
               'feature_icon_glyph' => 'shield',
               'feature_title'      => 'Pro-Grade Only',
               'feature_text'       => 'The same products our detailers use in studio.',
            ],
            [
               'feature_icon_glyph' => 'clock',
               'feature_title'      => 'Fast Dispatch',
               'feature_text'       => 'Most orders ship within one business day.',
            ],
            [
               'feature_icon_glyph' => 'headset',
               'feature_title'      => 'Expert Advice',
               'feature_text'       => "Not sure what you need? Our team will help.",
            ],
         ],
      ];
   }
}
