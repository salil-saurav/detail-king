<?php

/**
 * About — Stats. Design band y 4931.7…5420.4 (dark).
 *
 * The homepage's .dk-stats--craft block, reused wholesale — same visual
 * pattern (4 cells, vertical dividers, gold-gradient number + label). Numbers
 * are page-level: About's set differs from the homepage's (see AboutDefaults).
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta  = MetaHelper::getInstance();
$stats = $meta->fieldRowsOr('about_stats', 'about');

if (!$stats) {
   return;
}
?>
<section class="about-stats section--dark" data-animate="fade">
   <div class="container-dk">
      <ul class="dk-stats dk-stats--craft about-stats__row">
         <?php foreach ($stats as $row) : ?>
            <li class="dk-stats__item">
               <span class="dk-stats__value text-gold-gradient" data-count-to="<?= esc_attr((string) ($row['stat_value'] ?? '')); ?>">
                  <?= esc_html((string) ($row['stat_value'] ?? '')); ?>
               </span>
               <span class="dk-stats__label body-sm"><?= esc_html((string) ($row['stat_label'] ?? '')); ?></span>
            </li>
         <?php endforeach; ?>
      </ul>
   </div>
</section>
