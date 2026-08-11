<?php

/**
 * DetailKing bootstrap.
 *
 * Loads the Composer autoloader and boots the Application, which auto-discovers
 * and registers every service under src/ (see src/Config/services.php).
 *
 * Project-specific wiring (custom post types, redirects, form definitions, etc.)
 * belongs here via the documented filters — see docs/filter.md.
 *
 * @package DetailKing Theme
 */

use DetailKing\Theme\Core\Application;

if (!defined('ABSPATH')) exit;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
   require_once __DIR__ . '/vendor/autoload.php';
}

if (class_exists(Application::class)) {
   Application::getInstance()->boot();
}

/**
 * Example: register a custom post type + taxonomy.
 *
 * Uncomment and adapt. Labels are generated automatically; `args` is merged over
 * sensible defaults (see Content\PostTypeService). Re-save Settings → Permalinks
 * once after adding a type.
 */
// add_filter('detailking/theme/post_types', function (array $types): array {
//    $types['portfolio'] = [
//       'singular'   => 'Project',
//       'plural'     => 'Projects',
//       'args'       => [
//          'menu_icon' => 'dashicons-portfolio',
//          'supports'  => ['title', 'editor', 'thumbnail', 'excerpt'],
//       ],
//       'taxonomies' => [
//          'portfolio_cat' => ['singular' => 'Category', 'plural' => 'Categories'],
//       ],
//    ];
//    return $types;
// });
