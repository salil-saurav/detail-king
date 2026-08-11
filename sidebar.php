<?php

/**
 * The sidebar containing the main widget area.
 *
 * No widget area is registered by default. To use this, register one in a
 * service (register_sidebar(['id' => 'sidebar-1', ...])) and include it from a
 * template with get_sidebar().
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

if (!is_active_sidebar('sidebar-1')) {
   return;
}
?>
<aside id="secondary" class="widget-area">
   <?php dynamic_sidebar('sidebar-1'); ?>
</aside>
