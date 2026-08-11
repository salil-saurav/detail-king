<?php

/**
 * Reusable category filter pill row — the "FILTER" strip on Our Services, and
 * (per plan) Gallery next.
 *
 *   get_template_part('template-parts/components/filter-tabs', null, [
 *      'label'  => 'Filter',                     // small caps label before the pills
 *      'items'  => [['slug' => 'grooming', 'label' => 'Grooming'], …],
 *      'active' => 'all',                        // defaults to 'all'
 *      'target' => '.svc-grid',                  // selector the pills act on
 *      'mode'   => 'scroll',                     // 'hide' (default) | 'scroll'
 *   ]);
 *
 * Purely presentational + a data-attribute contract — `initFilterTabs()` in
 * global.js does the work. Cards must carry
 * `data-dk-filter-cats="<space-separated slugs>"`; `all` means everything.
 *
 * `mode` decides what a pill click does:
 *   'hide'   — show/hide non-matching cards (Gallery's wall of items)
 *   'scroll' — leave everything visible, scroll to the matching card. This is
 *              what the client brief specifies for Our Services: "When someone
 *              clicks on a service name, it will take them to that service
 *              section on the same page" (TASK-BRIEF.md §1.5). Default stays
 *              'hide' so no other caller changes behaviour.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

$label  = isset($args['label'])  ? (string) $args['label']  : __('Filter', 'detailking');
$items  = isset($args['items']) && is_array($args['items']) ? $args['items'] : [];
$active = isset($args['active']) ? (string) $args['active'] : 'all';
$target = isset($args['target']) ? (string) $args['target'] : '';
$mode   = (isset($args['mode']) && $args['mode'] === 'scroll') ? 'scroll' : 'hide';

if (!$items) {
   return;
}
?>
<div class="dk-filter-bar">
   <div class="container-dk">
      <div class="dk-filter-bar__inner" data-dk-filter-group data-dk-filter-mode="<?= esc_attr($mode); ?>"<?= $target !== '' ? ' data-dk-filter-target="' . esc_attr($target) . '"' : ''; ?>>
         <span class="dk-filter-bar__label"><?= esc_html($label); ?></span>
         <?php foreach ($items as $item) :
            $slug = (string) ($item['slug'] ?? '');
            if ($slug === '') {
               continue;
            }
            $isActive = $slug === $active;
            ?>
            <button type="button"
               class="dk-filter-pill<?= $isActive ? ' is-active' : ''; ?>"
               data-dk-filter="<?= esc_attr($slug); ?>"
               aria-pressed="<?= $isActive ? 'true' : 'false'; ?>">
               <?= esc_html((string) ($item['label'] ?? $slug)); ?>
            </button>
         <?php endforeach; ?>
      </div>
   </div>
</div>
