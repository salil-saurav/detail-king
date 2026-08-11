<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Content;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Configuration-driven registrar for custom post types and their taxonomies.
 *
 * Define post types via the 'detailking/theme/post_types' filter — labels are
 * generated automatically and sensible defaults are merged in. Example:
 *
 *   add_filter('detailking/theme/post_types', function (array $types): array {
 *      $types['testimonial'] = [
 *         'singular'   => 'Testimonial',
 *         'plural'     => 'Testimonials',
 *         'args'       => ['menu_icon' => 'dashicons-testimonial'],
 *         'taxonomies' => [
 *            'testimonial_cat' => ['singular' => 'Category', 'plural' => 'Categories'],
 *         ],
 *      ];
 *      return $types;
 *   });
 */
class PostTypeService extends Singleton implements ServiceInterface
{
   private array $postTypes = [];

   public function register(): void
   {
      add_action('init', [$this, 'registerAll']);
   }

   public function registerAll(): void
   {
      $this->postTypes = (array) apply_filters('detailking/theme/post_types', []);

      foreach ($this->postTypes as $slug => $config) {
         $this->registerSinglePostType((string) $slug, (array) $config);
      }
   }

   private function registerSinglePostType(string $slug, array $config): void
   {
      $slug = sanitize_key($slug);

      $singular = $config['singular'] ?? ucfirst($slug);
      $plural   = $config['plural'] ?? $singular . 's';

      $args = wp_parse_args($config['args'] ?? [], [
         'labels'          => $this->getPostTypeLabels($singular, $plural),
         'public'          => true,
         'has_archive'     => true,
         'menu_icon'       => 'dashicons-admin-post',
         'supports'        => ['title', 'editor', 'thumbnail'],
         'show_in_rest'    => true,
         'rewrite'         => ['slug' => str_replace('_', '-', $slug)],
         'capability_type' => 'post',
      ]);

      register_post_type($slug, $args);

      foreach (($config['taxonomies'] ?? []) as $taxSlug => $taxConfig) {
         $this->registerSingleTaxonomy((string) $taxSlug, $slug, (array) $taxConfig);
      }
   }

   private function registerSingleTaxonomy(string $slug, string $postType, array $config): void
   {
      $slug = sanitize_key($slug);

      if (taxonomy_exists($slug)) {
         register_taxonomy_for_object_type($slug, $postType);
         return;
      }

      $singular = $config['singular'] ?? ucfirst($slug);
      $plural   = $config['plural'] ?? $singular . 's';

      $args = wp_parse_args($config['args'] ?? [], [
         'hierarchical'      => true,
         'labels'            => $this->getTaxonomyLabels($singular, $plural),
         'show_ui'           => true,
         'show_admin_column' => true,
         'query_var'         => true,
         'show_in_rest'      => true,
         'rewrite'           => ['slug' => str_replace('_', '-', $slug)],
      ]);

      register_taxonomy($slug, $postType, $args);
   }

   private function getPostTypeLabels(string $singular, string $plural): array
   {
      return [
         'name'                  => $plural,
         'singular_name'         => $singular,
         'menu_name'             => $plural,
         'add_new'               => __('Add New'),
         'add_new_item'          => sprintf(__('Add New %s'), $singular),
         'edit_item'             => sprintf(__('Edit %s'), $singular),
         'new_item'              => sprintf(__('New %s'), $singular),
         'view_item'             => sprintf(__('View %s'), $singular),
         'view_items'            => sprintf(__('View %s'), $plural),
         'search_items'          => sprintf(__('Search %s'), $plural),
         'not_found'             => sprintf(__('No %s found'), strtolower($plural)),
         'not_found_in_trash'    => sprintf(__('No %s found in Trash'), strtolower($plural)),
         'all_items'             => sprintf(__('All %s'), $plural),
         'archives'              => sprintf(__('%s Archives'), $singular),
         'attributes'            => sprintf(__('%s Attributes'), $singular),
         'insert_into_item'      => sprintf(__('Insert into %s'), strtolower($singular)),
         'uploaded_to_this_item' => sprintf(__('Uploaded to this %s'), strtolower($singular)),
         'featured_image'        => __('Featured Image'),
         'set_featured_image'    => __('Set featured image'),
         'remove_featured_image' => __('Remove featured image'),
         'use_featured_image'    => __('Use as featured image'),
      ];
   }

   private function getTaxonomyLabels(string $singular, string $plural): array
   {
      return [
         'name'              => $plural,
         'singular_name'     => $singular,
         'search_items'      => sprintf(__('Search %s'), $plural),
         'all_items'         => sprintf(__('All %s'), $plural),
         'parent_item'       => sprintf(__('Parent %s'), $singular),
         'parent_item_colon' => sprintf(__('Parent %s:'), $singular),
         'edit_item'         => sprintf(__('Edit %s'), $singular),
         'update_item'       => sprintf(__('Update %s'), $singular),
         'add_new_item'      => sprintf(__('Add New %s'), $singular),
         'new_item_name'     => sprintf(__('New %s Name'), $singular),
         'menu_name'         => $plural,
      ];
   }
}
