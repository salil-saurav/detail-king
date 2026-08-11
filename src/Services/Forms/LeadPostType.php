<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Forms;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use WP_Post;
use WP_Query;

defined('ABSPATH') || exit;

/**
 * The "Leads" custom post type — every custom-form submission is stored as one
 * lead and listed in the standard WordPress admin list table (search, sorting,
 * trash, bulk-delete) just like Posts.
 *
 * Leads are storage-only: the type is private (not publicly queryable, hidden
 * from search/REST) and read-only in the admin — there is no "Add New" and the
 * editor is replaced by a read-only details panel. They are only ever created
 * programmatically by FormService::storeLead().
 *
 * Field values are saved as post meta under the META_PREFIX; the field set is
 * driven by FormRegistry so columns/labels stay in sync with the forms.
 */
class LeadPostType extends Singleton implements ServiceInterface
{
   public const POST_TYPE   = 'detailking_lead';
   public const META_PREFIX = '_sp_lead_';

   public function register(): void
   {
      add_action('init', [$this, 'registerPostType']);

      // Admin list-table columns.
      add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'columns']);
      add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'renderColumn'], 10, 2);
      add_filter('manage_edit-' . self::POST_TYPE . '_sortable_columns', [$this, 'sortableColumns']);

      // Read-only edit screen: details metabox instead of the editor.
      add_action('add_meta_boxes', [$this, 'addMetaBox']);
      add_action('admin_head', [$this, 'hideAddNewButton']);

      // Full-text search across the stored meta in the admin list.
      add_action('pre_get_posts', [$this, 'extendAdminSearch']);
   }

   public function registerPostType(): void
   {
      $labels = [
         'name'               => __('Leads'),
         'singular_name'      => __('Lead'),
         'menu_name'          => __('Leads'),
         'all_items'          => __('All Leads'),
         'search_items'       => __('Search Leads'),
         'not_found'          => __('No leads found'),
         'not_found_in_trash' => __('No leads found in Trash'),
         'edit_item'          => __('View Lead'),
         'view_item'          => __('View Lead'),
      ];

      register_post_type(self::POST_TYPE, [
         'labels'              => $labels,
         'public'              => false,
         'show_ui'             => true,
         'show_in_menu'        => true,
         'show_in_rest'        => false,
         'publicly_queryable'  => false,
         'exclude_from_search' => true,
         'has_archive'         => false,
         'rewrite'             => false,
         'query_var'           => false,
         'menu_icon'           => 'dashicons-email-alt',
         'menu_position'       => 26,
         'supports'            => ['title'],
         'map_meta_cap'        => true,
         // Leads are created only by form submissions, never by hand.
         'capabilities'        => ['create_posts' => 'do_not_allow'],
      ]);
   }

   // -------------------------------------------------------------------------
   // List table
   // -------------------------------------------------------------------------

   /**
    * @param array<string,string> $columns
    * @return array<string,string>
    */
   public function columns(array $columns): array
   {
      return [
         'cb'       => $columns['cb'] ?? '<input type="checkbox" />',
         'title'    => __('Name'),
         'sp_email' => __('Email'),
         'sp_form'  => __('Form'),
         'date'     => __('Submitted'),
      ];
   }

   /**
    * @param array<string,string> $columns
    * @return array<string,string>
    */
   public function sortableColumns(array $columns): array
   {
      $columns['sp_form'] = 'sp_form';
      return $columns;
   }

   public function renderColumn(string $column, int $postId): void
   {
      switch ($column) {
         case 'sp_email':
            $email = (string) get_post_meta($postId, self::META_PREFIX . 'field_email', true);
            echo $email !== ''
               ? '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>'
               : '&mdash;';
            break;

         case 'sp_form':
            $formId = (string) get_post_meta($postId, self::META_PREFIX . 'form', true);
            $def    = FormRegistry::getInstance()->get($formId);
            echo esc_html($def['label'] ?? ($formId !== '' ? $formId : '—'));
            break;
      }
   }

   /**
    * Let the admin list search box match against stored field meta, not just the
    * post title.
    */
   public function extendAdminSearch(WP_Query $query): void
   {
      if (!is_admin() || !$query->is_main_query()) {
         return;
      }
      if ($query->get('post_type') !== self::POST_TYPE) {
         return;
      }
      $term = trim((string) $query->get('s'));
      if ($term === '') {
         return;
      }

      // Find lead IDs whose meta contains the search term, then OR them in.
      global $wpdb;
      $like = '%' . $wpdb->esc_like($term) . '%';
      $ids  = $wpdb->get_col($wpdb->prepare(
         "SELECT DISTINCT post_id FROM {$wpdb->postmeta}
          WHERE meta_key LIKE %s AND meta_value LIKE %s",
         $wpdb->esc_like(self::META_PREFIX . 'field_') . '%',
         $like
      ));

      if (!empty($ids)) {
         $query->set('s', '');
         $query->set('post__in', array_map('intval', $ids));
      }
   }

   // -------------------------------------------------------------------------
   // Read-only details screen
   // -------------------------------------------------------------------------

   public function addMetaBox(): void
   {
      add_meta_box(
         'sp_lead_details',
         __('Lead Details'),
         [$this, 'renderMetaBox'],
         self::POST_TYPE,
         'normal',
         'high'
      );
   }

   public function renderMetaBox(WP_Post $post): void
   {
      $formId = (string) get_post_meta($post->ID, self::META_PREFIX . 'form', true);
      $def    = FormRegistry::getInstance()->get($formId);
      $fields = $def['fields'] ?? [];

      echo '<table class="widefat striped" style="margin-top:8px">';
      echo '<tbody>';

      // Submitted field values, labelled from the registry.
      foreach ($fields as $key => $field) {
         $value = (string) get_post_meta($post->ID, self::META_PREFIX . 'field_' . $key, true);
         printf(
            '<tr><th style="width:200px;text-align:left">%s</th><td>%s</td></tr>',
            esc_html($field['label']),
            $value !== '' ? nl2br(esc_html($value)) : '&mdash;'
         );
      }

      // Context / provenance.
      $meta = [
         __('Form')       => $def['label'] ?? $formId,
         __('Submitted')  => get_the_date('Y-m-d H:i:s', $post) . ' ' . get_the_time('', $post),
         __('IP Address') => (string) get_post_meta($post->ID, self::META_PREFIX . 'ip', true),
         __('Source URL') => (string) get_post_meta($post->ID, self::META_PREFIX . 'referer', true),
         __('User Agent') => (string) get_post_meta($post->ID, self::META_PREFIX . 'ua', true),
      ];
      foreach ($meta as $label => $value) {
         if ($value === '') {
            continue;
         }
         printf(
            '<tr><th style="width:200px;text-align:left">%s</th><td>%s</td></tr>',
            esc_html((string) $label),
            esc_html((string) $value)
         );
      }

      echo '</tbody></table>';
   }

   /** Hide the "Add New" affordance on the leads screens (belt-and-braces). */
   public function hideAddNewButton(): void
   {
      $screen = function_exists('get_current_screen') ? get_current_screen() : null;
      if ($screen && $screen->post_type === self::POST_TYPE) {
         echo '<style>.page-title-action{display:none!important}</style>';
      }
   }
}
