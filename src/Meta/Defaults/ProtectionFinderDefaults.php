<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Protection Finder page default content, transcribed by direct pixel-read of
 * `build/pngs/protection-finder 1.png` (the real page shell — no Figma MCP
 * access was available this pass).
 *
 * Hero copy only. The 5 questions, their options and the scoring rubric are
 * NOT page fields — see build/figma-data/protection-finder-scoring.md, which
 * is the locked spec ProtectionFinderService reads. Making the questions
 * ACF-editable would over-engineer a placeholder scoring model that's going
 * to be replaced once the client supplies real protection-plan data.
 */
final class ProtectionFinderDefaults implements DefaultsProvider
{
   public function defaults(): array
   {
      return [
         'hero_eyebrow' => 'PROTECTION FINDER · 5 QUICK QUESTIONS',
         'hero_title'      => 'Find Your',
         'hero_title_gold' => 'Protection',
         'hero_text'       => "Every car has different needs. Answer five quick questions and we'll match you with the ideal protection — tailored to your vehicle, driving and goals.",
         'hero_bg_image'   => '',
      ];
   }
}
