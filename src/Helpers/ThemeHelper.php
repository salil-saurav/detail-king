<?php

namespace DetailKing\Theme\Helpers;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * General-purpose theme helpers. Currently provides a breadcrumb builder for
 * standard WordPress contexts (pages, single posts, archives, search, 404).
 */
class ThemeHelper extends Singleton implements ServiceInterface
{
   public function register(): void {}

   /**
    * Build a breadcrumb trail for the current request.
    *
    * @param array<string,string> $suffix Optional label => url pairs appended
    *                                      to the trail. An empty url renders the
    *                                      label as the current (non-linked) item.
    */
   public function get_breadcrumbs(array $suffix = []): string
   {
      $items      = [];
      $has_suffix = !empty($suffix);

      // Home
      $items[] = sprintf(
         '<a class="breadcrumb__link" href="%s" rel="nofollow">%s</a>',
         esc_url(home_url('/')),
         esc_html__('Home')
      );

      if (is_page()) {
         $items[] = $has_suffix
            ? $this->link(get_permalink(), get_the_title())
            : $this->current(get_the_title());
      } elseif (is_single()) {
         $categories = get_the_category();
         if (!empty($categories)) {
            $items[] = $this->link(get_category_link($categories[0]->term_id), $categories[0]->name);
         }
         $items[] = $has_suffix
            ? $this->link(get_permalink(), get_the_title())
            : $this->current(get_the_title());
      } elseif (is_category() || is_tax() || is_tag()) {
         $term = get_queried_object();
         if ($term && !is_wp_error($term)) {
            $items[] = $this->current($term->name);
         }
      } elseif (is_search()) {
         $items[] = $this->current(sprintf(
            /* translators: %s: search query. */
            __('Search results for "%s"'),
            get_search_query()
         ));
      } elseif (is_404()) {
         $items[] = $this->current(__('404 Error'));
      }

      // Suffix
      if ($has_suffix) {
         foreach ($suffix as $label => $link) {
            $items[] = !empty($link) ? $this->link($link, $label) : $this->current($label);
         }
      }

      ob_start();
      echo '<ul class="breadcrumb mb-0">';
      foreach ($items as $index => $item) {
         echo '<li class="breadcrumb__item">';
         echo $item;
         if ($index < count($items) - 1) {
            echo ' <span class="breadcrumb__separator mx-2">/</span>';
         }
         echo '</li>';
      }
      echo '</ul>';

      return ob_get_clean();
   }

   private function link(string $url, string $label): string
   {
      return sprintf(
         '<a class="breadcrumb__link" href="%s">%s</a>',
         esc_url($url),
         esc_html($label)
      );
   }

   private function current(string $label): string
   {
      return sprintf('<span class="breadcrumb__current">%s</span>', esc_html($label));
   }
}
