<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Content;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use WP_Query;

defined('ABSPATH') || exit;

/**
 * AJAX-powered live search for the header search overlay.
 *
 * Registers an admin-ajax endpoint (`detailking_live_search`) that returns a
 * small JSON list of matching posts/pages, and localizes the endpoint URL plus
 * a nonce onto the main theme script so global.js can query it.
 *
 * Extend the searched post types via the `detailking/theme/search/post_types`
 * filter (e.g. add your custom post types).
 */
class SearchService extends Singleton implements ServiceInterface
{
   /** Max results returned per live-search request. */
   private const RESULT_LIMIT = 6;

   /** Nonce action/name shared between PHP and JS. */
   private const NONCE_ACTION = 'detailking_live_search';

   public function register(): void
   {
      add_action('wp_ajax_detailking_live_search', [$this, 'handleSearch']);
      add_action('wp_ajax_nopriv_detailking_live_search', [$this, 'handleSearch']);

      // Run after AssetsService (priority 10) so the 'sp-main' handle exists.
      add_action('wp_enqueue_scripts', [$this, 'localize'], 20);

      // Render the overlay as a direct child of <body> so its fixed positioning
      // is viewport-relative (never trapped by a transformed/sticky ancestor).
      add_action('wp_footer', [$this, 'renderOverlay']);
   }

   /**
    * Output the search overlay + dropdown panel at the end of <body>.
    */
   public function renderOverlay(): void
   {
?>
      <!-- Live search overlay + dropdown panel -->
      <div class="search-overlay" id="header-search-overlay" data-search-overlay hidden>
         <div class="search-panel" id="header-search-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Search the site'); ?>">
            <div class="container">
               <form class="search-form" role="search" method="get" action="<?= esc_url(home_url('/')); ?>" data-search-form>
                  <input
                     type="search"
                     name="s"
                     class="search-form__input form-control"
                     placeholder="<?php esc_attr_e('Search…'); ?>"
                     aria-label="<?php esc_attr_e('Search'); ?>"
                     autocomplete="off"
                     data-search-input>
                  <button type="button" class="search-form__close btn" aria-label="<?php esc_attr_e('Close search'); ?>" data-search-close>&times;</button>
               </form>
               <div class="search-results" data-search-results aria-live="polite"></div>
            </div>
         </div>
      </div>
<?php
   }

   /**
    * Expose the AJAX URL + nonce to the front-end script.
    */
   public function localize(): void
   {
      wp_localize_script('sp-main', 'detailkingSearch', [
         'ajaxUrl' => admin_url('admin-ajax.php'),
         'action'  => self::NONCE_ACTION,
         'nonce'   => wp_create_nonce(self::NONCE_ACTION),
         'i18n'    => [
            'empty'     => __('No results found.'),
            'searching' => __('Searching…'),
            'viewAll'   => __('View all results'),
            'minChars'  => __('Type at least 2 characters…'),
         ],
      ]);
   }

   /**
    * Handle the live-search AJAX request and return matching results as JSON.
    */
   public function handleSearch(): void
   {
      check_ajax_referer(self::NONCE_ACTION, 'nonce');

      $term = isset($_GET['q']) ? sanitize_text_field(wp_unslash((string) $_GET['q'])) : '';
      $term = trim($term);

      if (mb_strlen($term) < 2) {
         wp_send_json_success(['results' => [], 'term' => $term]);
      }

      $post_types = (array) apply_filters('detailking/theme/search/post_types', ['post', 'page']);

      $query = new WP_Query([
         's'                   => $term,
         'post_type'           => $post_types,
         'post_status'         => 'publish',
         'posts_per_page'      => self::RESULT_LIMIT,
         'no_found_rows'       => true,
         'ignore_sticky_posts' => true,
      ]);

      $results = [];

      foreach ($query->posts as $post) {
         $thumb = get_the_post_thumbnail_url($post, 'thumbnail');

         $results[] = [
            'title'     => html_entity_decode(get_the_title($post), ENT_QUOTES),
            'url'       => get_permalink($post),
            'type'      => get_post_type_object($post->post_type)->labels->singular_name ?? '',
            'excerpt'   => wp_trim_words(wp_strip_all_tags((string) $post->post_excerpt ?: $post->post_content), 18),
            'thumbnail' => $thumb ?: '',
         ];
      }

      wp_reset_postdata();

      wp_send_json_success([
         'results' => $results,
         'term'    => $term,
         'viewAll' => add_query_arg('s', rawurlencode($term), home_url('/')),
      ]);
   }
}
