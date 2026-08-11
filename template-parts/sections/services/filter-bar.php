<?php

/**
 * Our Services filter bar — design band y 697…790 (node 159:399).
 *
 * Pills come from the live `service_category` terms, ordered to match the
 * card grid below (canonical site order: dk_service menu_order), not the
 * comp's own pill order or its "Paint Protection Film" / "Add On" wording —
 * see figma-data/our-services-spec.md for why service_short_name wins here.
 * `Interior` is Gallery-only (seed/structure.php) and is excluded.
 *
 * `mode => 'scroll'`: these pills are jump links, not a filter. The client
 * brief is explicit — "When someone clicks on a service name, it will take them
 * to that service section on the same page" (TASK-BRIEF.md §1.5). The first
 * build read the comp's "FILTER" label literally and shipped show/hide; that
 * was wrong. Gallery still uses the default 'hide' mode.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Meta\MetaHelper;

if (!defined('ABSPATH')) exit;

$meta = MetaHelper::getInstance();

$services = get_posts([
   'post_type'        => 'dk_service',
   'posts_per_page'   => -1,
   'orderby'          => ['menu_order' => 'ASC', 'title' => 'ASC'],
   'suppress_filters' => false,
]);

$items = [['slug' => 'all', 'label' => __('All Services', 'detailking')]];

foreach ($services as $service) {
   $terms = get_the_terms($service, 'service_category');
   if (!$terms || is_wp_error($terms)) {
      continue;
   }
   $term  = $terms[0];
   $label = (string) $meta->field('service_short_name', $service->ID, get_the_title($service));

   $items[] = ['slug' => $term->slug, 'label' => $label];
}
?>
<?php
get_template_part('template-parts/components/filter-tabs', null, [
   'items'  => $items,
   'active' => 'all',
   'target' => '.svc-grid',
   'mode'   => 'scroll',
]);
