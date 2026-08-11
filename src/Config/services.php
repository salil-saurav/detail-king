<?php

/**
 * Service registry.
 *
 * Each listed directory is recursively scanned; any concrete class implementing
 * DetailKing\Theme\Core\ServiceInterface is auto-registered on boot. Add a new folder
 * here (e.g. 'Modules') to have it picked up automatically.
 */

use DetailKing\Theme\Core\ServiceLoader;

defined('ABSPATH') || exit;

// Always the parent (template) directory so the framework boots even when a
// child theme is active.
$base = get_template_directory() . '/src/';
$ns   = 'DetailKing\\Theme\\';

return array_merge(
   ServiceLoader::load($base . 'Services', $ns . 'Services'),
   ServiceLoader::load($base . 'Helpers', $ns . 'Helpers'),
   ServiceLoader::load($base . 'Meta', $ns . 'Meta'),
);
