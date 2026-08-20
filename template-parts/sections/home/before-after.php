<?php

/**
 * Before / after comparison. Design band y 5795.1–6515.1, node 59:2379.
 *
 * Copy left, draggable comparison slider right. Preset pills switch the image
 * pair. Comp note: "Draggable divider (touch + mouse). Auto-teases left-right once
 * on view" — the tease is in home.js.
 *
 * NOTE on the comp's images: node 59:2379 contains exactly ONE photo. The comp
 * fakes the pair by colour-grading one half of a single image, so there is no
 * genuine "before" asset to extract. Both sides therefore seed to the same photo
 * and the "before" side carries a CSS grade that approximates the comp. Each
 * preset has separate before/after image fields so real pairs replace it without
 * a template change.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();
$D    = 'homepage';

$presets = $meta->fieldRowsOr('ba_presets', $D);
$seedImg = get_template_directory_uri() . '/assets/images/home/before-after.jpg';

$labelBefore = (string) $meta->fieldOr('ba_label_before', $D);
$labelAfter  = (string) $meta->fieldOr('ba_label_after', $D);

// Normalise each preset to a usable image pair up front, so the markup below is
// not littered with fallback logic.
$sets = [];
foreach ($presets as $row) {
   $before = $meta->imageUrl($row['preset_before'] ?? null, $seedImg);
   $after  = $meta->imageUrl($row['preset_after'] ?? null, $seedImg);
   $sets[] = [
      'label'  => (string) ($row['preset_label'] ?? ''),
      'before' => $before,
      'after'  => $after,
      // Marks a pair that is really one photo, so the CSS can grade the before
      // side instead of showing two identical halves.
      'faux'   => $before === $after,
   ];
}
?>
<section class="home-ba section--dark" data-animate="fade">
   <div class="container-dk home-ba__inner">

      <div class="home-ba__copy">
         <?php
         get_template_part('template-parts/components/section-heading', null, [
            'eyebrow' => $meta->fieldOr('ba_eyebrow', $D),
            'title'   => $meta->fieldOr('ba_heading', $D),
            'gold'    => $meta->fieldOr('ba_heading_gold', $D),
            'size'    => 'display-sm',
            'text'    => $meta->fieldOr('ba_text', $D),
            'eyebrow_animate' => 'fade-up',
            'animate' => 'fade-up',
            'text_animate' => 'fade-up',
         ]);
         ?>

         <?php if (count($sets) > 1) : ?>
            <div class="home-ba__presets" role="tablist" aria-label="<?php esc_attr_e('Comparison sets', 'detailking'); ?>">
               <?php foreach ($sets as $i => $set) : ?>
                  <?php if ($set['label'] === '') {
                     continue;
                  } ?>
                  <button class="home-ba__preset<?= $i === 0 ? ' is-active' : ''; ?>"
                     type="button"
                     role="tab"
                     aria-selected="<?= $i === 0 ? 'true' : 'false'; ?>"
                     data-ba-preset="<?= esc_attr((string) $i); ?>"
                     data-before="<?= esc_url($set['before']); ?>"
                     data-after="<?= esc_url($set['after']); ?>"
                     data-faux="<?= $set['faux'] ? '1' : '0'; ?>"
                     data-animate="fade-up">
                     <?= esc_html($set['label']); ?>
                  </button>
               <?php endforeach; ?>
            </div>
         <?php endif; ?>
      </div>

      <?php if ($sets) : $first = $sets[0]; ?>
         <div class="home-ba__viewer">
            <div class="dk-compare<?= $first['faux'] ? ' dk-compare--faux' : ''; ?>"
               data-dk-compare
               style="--dk-compare-pos:60%">

               <img class="dk-compare__img dk-compare__img--after" src="<?= esc_url($first['after']); ?>" alt="<?= esc_attr($labelAfter); ?>" loading="lazy" decoding="async">

               <div class="dk-compare__before" aria-hidden="true">
                  <img class="dk-compare__img dk-compare__img--before" src="<?= esc_url($first['before']); ?>" alt="" loading="lazy" decoding="async">
               </div>

               <?php if ($labelBefore !== '') : ?>
                  <span class="dk-compare__label dk-compare__label--before eyebrow"><?= esc_html($labelBefore); ?></span>
               <?php endif; ?>
               <?php if ($labelAfter !== '') : ?>
                  <span class="dk-compare__label dk-compare__label--after eyebrow"><?= esc_html($labelAfter); ?></span>
               <?php endif; ?>

               <span class="dk-compare__divider" aria-hidden="true"></span>

               <input class="dk-compare__range"
                  type="range" min="0" max="100" value="60" step="0.1"
                  aria-label="<?php esc_attr_e('Reveal the before image', 'detailking'); ?>"
                  data-animate="fade-up">

               <span class="dk-compare__handle" aria-hidden="true"></span>
            </div>
         </div>
      <?php endif; ?>

   </div>
</section>
