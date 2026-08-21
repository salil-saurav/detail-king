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
         if (get_post_type() === 'product' && function_exists('wc_get_page_permalink')) {
            $items[] = $this->link((string) wc_get_page_permalink('shop'), __('Shop', 'detailking'));
         } elseif (get_post_type() === 'dk_service') {
            // The 7 service singles sit under the real "Our Services" Page
            // (/our-services/ — what the nav actually links to), not under
            // /services/, the dk_service archive template that Woo-style
            // has_archive registration forces to exist but nothing links to.
            // Fall back to that archive only if the Page ever goes missing.
            $servicesPage = get_page_by_path('our-services');
            if ($servicesPage) {
               $items[] = $this->link((string) get_permalink($servicesPage), get_the_title($servicesPage));
            } else {
               $items[] = $this->link((string) get_post_type_archive_link('dk_service'), __('Services', 'detailking'));
            }
         } else {
            $categories = get_the_category();
            if (!empty($categories)) {
               $items[] = $this->link(get_category_link($categories[0]->term_id), $categories[0]->name);
            }
         }
         $items[] = $has_suffix
            ? $this->link(get_permalink(), get_the_title())
            : $this->current(get_the_title());
      } elseif (is_post_type_archive()) {
         // dk_service's own has_archive URL (/services/) — unlinked from the
         // nav (which points to /our-services/ instead) but still directly
         // reachable, and previously fell through every branch here to a
         // bare "Home" with no current-page crumb at all.
         $items[] = $this->current(post_type_archive_title('', false));
      } elseif (is_category() || is_tax() || is_tag()) {
         $term = get_queried_object();
         if ($term && !is_wp_error($term)) {
            // Product categories sit under Shop (same "Home / Shop / …" shape
            // the single-product branch above already uses) and can nest —
            // walk ancestors oldest-first so a child category still shows its
            // parent(s) rather than just the leaf term.
            if ($term->taxonomy === 'product_cat' && function_exists('wc_get_page_permalink')) {
               $items[] = $this->link((string) wc_get_page_permalink('shop'), __('Shop', 'detailking'));

               $ancestors = array_reverse(get_ancestors($term->term_id, $term->taxonomy));
               foreach ($ancestors as $ancestorId) {
                  $ancestor = get_term($ancestorId, $term->taxonomy);
                  if ($ancestor && !is_wp_error($ancestor)) {
                     $items[] = $this->link((string) get_term_link($ancestor), $ancestor->name);
                  }
               }
            }
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
      echo '<ul class="breadcrumb">';
      foreach ($items as $index => $item) {
         echo '<li class="breadcrumb__item">';
         echo $item;
         if ($index < count($items) - 1) {
            echo ' <span class="breadcrumb__separator mx-2">/</span> ';
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
