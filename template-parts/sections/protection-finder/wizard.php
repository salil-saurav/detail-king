<?php

/**
 * Protection Finder — the wizard card + "Your Build" rail + result state.
 *
 * Static markup for all 5 question cards (assets/js/pages/protection-finder.js
 * shows/hides via .is-active — no page reload between questions), the rail,
 * and the result template. Question copy/options are transcribed by direct
 * pixel-read of build/pngs/protection-finder {1..5}.png (no Figma MCP access
 * this pass) — see build/figma-data/protection-finder-scoring.md for the
 * locked option keys this markup's data-value attributes must match exactly
 * (ProtectionFinderService::WEIGHTS keys on those same strings).
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$questions = [
   [
      'key'   => 'vehicle',
      'title' => 'WHAT ARE YOU <span class="text-gold-gradient">DRIVING?</span>',
      'text'  => "This tells us the surface and finish we're working with.",
      'options' => [
         ['value' => 'daily-driver',   'label' => 'Daily Driver',    'desc' => 'Everyday car or commuter'],
         ['value' => 'luxury-sports',  'label' => 'Luxury / Sports', 'desc' => 'Prestige or performance vehicle'],
         ['value' => 'suv-ute',        'label' => 'SUV / Ute',       'desc' => 'Larger family or work vehicle'],
         ['value' => 'brand-new',      'label' => 'Brand New',       'desc' => 'Fresh off the lot, factory perfect'],
      ],
   ],
   [
      'key'   => 'priority',
      'title' => 'WHAT MATTERS <span class="text-gold-gradient">MOST?</span>',
      'text'  => 'Pick the single outcome you care about the most.',
      'options' => [
         ['value' => 'deep-gloss',       'label' => 'Deep Gloss',        'desc' => 'A showroom shine that turns heads'],
         ['value' => 'chip-protection',  'label' => 'Chip Protection',   'desc' => 'Defend against stones & road rash'],
         ['value' => 'easy-cleaning',    'label' => 'Easy Cleaning',     'desc' => 'Less washing, water beads off'],
         ['value' => 'comfort-privacy',  'label' => 'Comfort & Privacy', 'desc' => 'Cooler cabin, less glare, privacy'],
      ],
   ],
   [
      'key'   => 'usage',
      'title' => 'HOW DO YOU <span class="text-gold-gradient">USE IT?</span>',
      'text'  => 'Your driving style changes how much protection you need.',
      'options' => [
         ['value' => 'city-short-trips', 'label' => 'City & Short Trips', 'desc' => 'Mostly urban, parking & errands'],
         ['value' => 'lots-of-highway',  'label' => 'Lots Of Highway',    'desc' => 'High kms, open-road driving'],
         ['value' => 'weekend-pride',    'label' => 'Weekend Pride',      'desc' => 'Garaged, driven for enjoyment'],
         ['value' => 'work-tough-use',   'label' => 'Work & Tough Use',   'desc' => 'Hard-working, all conditions'],
      ],
   ],
   [
      'key'   => 'longevity',
      'title' => 'HOW LONG SHOULD IT <span class="text-gold-gradient">LAST?</span>',
      'text'  => 'This points us toward the right level of protection.',
      'options' => [
         ['value' => 'a-season',           'label' => 'A Season',             'desc' => 'Fresh for an event or summer'],
         ['value' => 'a-few-years',        'label' => 'A Few Years',          'desc' => 'Lasting, low-maintenance protection'],
         ['value' => 'maximum-permanent',  'label' => 'Maximum / Permanent',  'desc' => 'The longest, hardest protection'],
      ],
   ],
   [
      'key'   => 'budget',
      'title' => "WHAT'S YOUR <span class=\"text-gold-gradient\">BUDGET COMFORT?</span>",
      'text'  => "We'll match a level that feels right — no pressure.",
      'options' => [
         ['value' => 'smart-value', 'label' => 'Smart Value', 'desc' => 'Great results, sensible spend'],
         ['value' => 'balanced',    'label' => 'Balanced',    'desc' => 'Strong protection, fair investment'],
         ['value' => 'premium',     'label' => 'Premium',     'desc' => 'The very best, whatever it takes'],
      ],
   ],
];

$railLabels = ['vehicle' => 'Vehicle', 'priority' => 'Priority', 'usage' => 'Usage', 'longevity' => 'Longevity', 'budget' => 'Budget'];
$total      = count($questions);
?>
<section class="pf-wizard-section section-padding-block pt-0" data-animate="fade">
   <div class="container-dk">
      <div class="pf-layout">

         <div class="pf-card" id="pf-card" data-pf-total="<?= (int) $total; ?>">

            <div class="pf-progress" data-pf-progress role="progressbar" aria-valuemin="0" aria-valuemax="<?= (int) $total; ?>" aria-valuenow="0">
               <?php for ($i = 0; $i < $total; $i++) : ?>
                  <span class="pf-progress__seg" data-pf-progress-seg="<?= (int) $i; ?>"></span>
               <?php endfor; ?>
            </div>

            <?php foreach ($questions as $i => $q) : ?>
               <div class="pf-question<?= $i === 0 ? ' is-active' : ''; ?>" data-pf-question="<?= (int) $i; ?>" data-pf-key="<?= esc_attr($q['key']); ?>">
                  <p class="pf-question__eyebrow eyebrow"><?= sprintf(esc_html__('QUESTION %1$d OF %2$d', 'detailking'), $i + 1, $total); ?></p>
                  <h2 class="pf-question__title"><?= wp_kses($q['title'], ['span' => ['class' => []]]); ?></h2>
                  <p class="pf-question__text body-lg"><?= esc_html($q['text']); ?></p>

                  <div class="pf-options" role="radiogroup" aria-label="<?= esc_attr($q['key']); ?>">
                     <?php foreach ($q['options'] as $opt) : ?>
                        <label class="pf-option">
                           <input
                              type="radio"
                              class="pf-option__input visually-hidden"
                              name="pf_<?= esc_attr($q['key']); ?>"
                              value="<?= esc_attr($opt['value']); ?>"
                              data-pf-option
                           >
                           <span class="pf-option__body">
                              <span class="pf-option__icon" aria-hidden="true">
                                 <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"></circle></svg>
                              </span>
                              <span class="pf-option__text">
                                 <strong><?= esc_html($opt['label']); ?></strong>
                                 <span><?= esc_html($opt['desc']); ?></span>
                              </span>
                              <span class="pf-option__check" aria-hidden="true">&#10003;</span>
                           </span>
                        </label>
                     <?php endforeach; ?>
                  </div>

                  <div class="pf-question__actions">
                     <button type="button" class="btn-gold btn-arrow" data-pf-next>
                        <?= $i === $total - 1 ? esc_html__('See My Match', 'detailking') : esc_html__('Next', 'detailking'); ?>
                     </button>
                  </div>
               </div>
            <?php endforeach; ?>

            <div class="pf-result" data-pf-result>
               <div class="pf-result__inner" data-pf-result-inner></div>
               <button type="button" class="pf-start-over" data-pf-start-over>
                  <?= esc_html__('↺ Start over', 'detailking'); ?>
               </button>
            </div>

         </div>

         <aside class="pf-rail" data-pf-rail>
            <div class="pf-rail__head">
               <span class="pf-rail__eyebrow eyebrow">YOUR BUILD</span>
               <p class="pf-rail__title" data-pf-rail-title><?= esc_html__('BUILDING YOUR MATCH…', 'detailking'); ?></p>
            </div>
            <ul class="pf-rail__rows">
               <?php foreach ($railLabels as $key => $label) : ?>
                  <li class="pf-rail__row" data-pf-rail-row="<?= esc_attr($key); ?>">
                     <span class="pf-rail__label"><?= esc_html($label); ?></span>
                     <span class="pf-rail__value" data-pf-rail-value="<?= esc_attr($key); ?>">—</span>
                  </li>
               <?php endforeach; ?>
               <li class="pf-rail__row pf-rail__row--progress">
                  <span class="pf-rail__label"><?= esc_html__('Match progress', 'detailking'); ?></span>
                  <span class="pf-rail__value" data-pf-rail-progress>0%</span>
               </li>
            </ul>
            <div class="pf-rail__photo" aria-hidden="true"></div>
            <p class="pf-rail__footnote" data-pf-rail-footnote>
               <?= esc_html__("Answer the questions and we'll reveal your ideal protection.", 'detailking'); ?>
            </p>
         </aside>

      </div>
   </div>
</section>

<template id="pf-result-template">
   <div class="pf-result__icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z"></path></svg>
   </div>
   <span class="pf-result__match">{{matchPct}}% match</span>
   <h2 class="pf-result__title">{{title}}</h2>
   <p class="pf-result__teaser">{{teaser}}</p>
   <div class="pf-result__features">{{features}}</div>
   <div class="pf-result__price">{{priceRow}}</div>
   <a class="btn-gold btn-arrow pf-result__cta" href="{{permalink}}"><?= esc_html__('Book This Service', 'detailking'); ?> &rarr;</a>
   <p class="pf-result__runner">{{runnerUp}}</p>
</template>
