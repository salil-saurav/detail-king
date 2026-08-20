<?php

/**
 * Protection Finder — the wizard card + "Your Build" rail + result state.
 *
 * Static markup for all 5 question cards (assets/js/pages/protection-finder.js
 * shows/hides via .is-active — no page reload between questions), the rail,
 * and the result template. Questions/options transcribed verbatim from the
 * client's "PPF calculator" doc (Google Doc, supplied 17 Aug 2026 — the
 * calculation Shreya promised on 29 Jul, TASK-BRIEF §5) — see
 * build/figma-data/protection-finder-scoring.md for the locked option keys
 * this markup's data-value attributes must match exactly
 * (ProtectionFinderService::WEIGHTS keys on those same strings).
 *
 * Question 2 carries a 5th option, "Matte / Stealth Look", that the doc's
 * points table scores but its own bullet list omits — treated as a real 5th
 * radio choice here (not a separate toggle question), since that is what
 * makes the doc's own "if the customer selects Matte / Stealth Look…"
 * tie-break rule meaningful. Flagged for client sign-off, not blocking.
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
         ['value' => 'luxury-sports', 'label' => 'Luxury / Sports Car', 'desc' => 'Prestige or performance vehicle'],
         ['value' => 'suv-ute',       'label' => 'SUV / Ute',           'desc' => 'Larger family or work vehicle'],
         ['value' => 'sedan-hatch',   'label' => 'Sedan / Hatchback',   'desc' => 'Everyday car or commuter'],
         ['value' => 'new-car',       'label' => 'New Car',             'desc' => 'Fresh off the lot, factory perfect'],
      ],
   ],
   [
      'key'   => 'priority',
      'title' => 'WHAT DO YOU WANT <span class="text-gold-gradient">PROTECTION FROM?</span>',
      'text'  => 'Pick the single outcome you care about the most.',
      'options' => [
         ['value' => 'stone-chips',         'label' => 'Stone Chips',         'desc' => 'Defend against stones & road rash'],
         ['value' => 'scratches',           'label' => 'Scratches',          'desc' => 'Guard against swirls, scuffs & scratches'],
         ['value' => 'sun-uv',              'label' => 'Sun / UV',            'desc' => 'Cooler cabin, less fade, less glare'],
         ['value' => 'general-protection',  'label' => 'General Protection',  'desc' => 'All-round, everyday protection'],
         ['value' => 'matte-stealth-look',  'label' => 'Matte / Stealth Look','desc' => 'A flat, low-sheen finish with the protection'],
      ],
   ],
   [
      'key'   => 'usage',
      'title' => 'HOW DO YOU <span class="text-gold-gradient">USE IT?</span>',
      'text'  => 'Your driving style changes how much protection you need.',
      'options' => [
         ['value' => 'city-driving',       'label' => 'City Driving',              'desc' => 'Mostly urban, parking & errands'],
         ['value' => 'highway-driving',    'label' => 'Highway Driving',           'desc' => 'High kms, open-road driving'],
         ['value' => 'weekend-occasional', 'label' => 'Weekend / Occasional Use',  'desc' => 'Garaged, driven for enjoyment'],
         ['value' => 'work-offroad',       'label' => 'Work / Off-road Use',       'desc' => 'Hard-working, all conditions'],
      ],
   ],
   [
      'key'   => 'level',
      'title' => 'WHAT LEVEL OF <span class="text-gold-gradient">PROTECTION?</span>',
      'text'  => 'This points us toward the right coverage for your car.',
      'options' => [
         ['value' => 'basic-protection',    'label' => 'Basic Protection',    'desc' => 'Coverage for the most exposed panels'],
         ['value' => 'front-protection',    'label' => 'Front Protection',    'desc' => 'Complete front-end coverage'],
         ['value' => 'full-car-protection', 'label' => 'Full Car Protection', 'desc' => 'Total coverage, every panel'],
         ['value' => 'maximum-protection',  'label' => 'Maximum Protection',  'desc' => 'The longest, hardest protection available'],
      ],
   ],
   [
      'key'   => 'budget',
      'title' => "WHAT'S YOUR <span class=\"text-gold-gradient\">BUDGET COMFORT?</span>",
      'text'  => "We'll match a level that feels right — no pressure.",
      'options' => [
         ['value' => 'best-value', 'label' => 'Best Value', 'desc' => 'Great results, sensible spend'],
         ['value' => 'mid-range',  'label' => 'Mid-Range',  'desc' => 'Strong protection, fair investment'],
         ['value' => 'premium',    'label' => 'Premium',    'desc' => 'The very best, whatever it takes'],
      ],
   ],
];

$railLabels = ['vehicle' => 'Vehicle', 'priority' => 'Priority', 'usage' => 'Usage', 'level' => 'Level', 'budget' => 'Budget'];
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
                  <div class="pf-rail__progress-top">
                     <span class="pf-rail__label"><?= esc_html__('Match progress', 'detailking'); ?></span>
                     <span class="pf-rail__value" data-pf-rail-progress>0%</span>
                  </div>
                  <div class="pf-progressbar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-pf-rail-progressbar>
                     <span class="pf-progressbar__fill" data-pf-rail-progress-fill></span>
                  </div>
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
