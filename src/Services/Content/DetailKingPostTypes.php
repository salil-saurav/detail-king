<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Content;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Every custom post type and taxonomy this project needs, declared through the
 * `detailking/theme/post_types` filter that PostTypeService reads. Labels and
 * sensible arg defaults are generated for us — only the deviations are listed.
 *
 * Which of these is public matters, and it is a content-model decision rather
 * than a detail:
 *
 *   - dk_service is the only one with its own URL and archive. The seven service
 *     pages ARE this post type; single-dk_service.php renders all of them.
 *   - dk_gallery is public so a gallery item can be deep-linked, but has no
 *     archive — the Gallery page template owns the listing.
 *   - Packages, add-ons, memberships, testimonials, locations and FAQs are
 *     content *fragments*. They are only ever rendered inside another page, so a
 *     public single view would be a thin, duplicate-content URL. public=false.
 *   - dk_booking is a submission record, not content. Admin-only, and not
 *     creatable by hand — rows arrive from the wizard.
 *
 * service_category is registered on dk_service first and then attached to
 * dk_gallery, so the filter pills on the Services page and the Gallery page are
 * driven by one term list instead of two that drift apart.
 */
class DetailKingPostTypes extends Singleton implements ServiceInterface
{
   public function register(): void
   {
      add_filter('detailking/theme/post_types', [$this, 'definePostTypes']);
   }

   /**
    * @param array<string,array<string,mixed>> $types
    * @return array<string,array<string,mixed>>
    */
   public function definePostTypes(array $types): array
   {
      /* ── Services — the 7 service pages ─────────────────────────────── */
      $types['dk_service'] = [
         'singular'   => __('Service', 'detailking'),
         'plural'     => __('Services', 'detailking'),
         'args'       => [
            'menu_icon'   => 'dashicons-car',
            'has_archive' => 'services',
            'supports'    => ['title', 'editor', 'thumbnail', 'page-attributes'],
            'rewrite'     => ['slug' => 'services', 'with_front' => false],
         ],
         'taxonomies' => [
            'service_category' => [
               'singular' => __('Service Category', 'detailking'),
               'plural'   => __('Service Categories', 'detailking'),
               'args'     => ['rewrite' => ['slug' => 'service-category', 'with_front' => false]],
            ],
         ],
      ];

      /* ── Packages — the priced cards inside a service's booking builder ── */
      $types['dk_package'] = [
         'singular' => __('Package', 'detailking'),
         'plural'   => __('Service Packages', 'detailking'),
         'args'     => [
            'menu_icon'    => 'dashicons-tag',
            'public'       => false,
            'show_ui'      => true,
            'has_archive'  => false,
            'supports'     => ['title', 'page-attributes'],
            'rewrite'      => false,
            'show_in_rest' => false,
         ],
      ];

      /* ── Add-on services ────────────────────────────────────────────── */
      $types['dk_addon'] = [
         'singular' => __('Add-On', 'detailking'),
         'plural'   => __('Add-On Services', 'detailking'),
         'args'     => [
            'menu_icon'    => 'dashicons-plus-alt',
            'public'       => false,
            'show_ui'      => true,
            'has_archive'  => false,
            'supports'     => ['title', 'thumbnail', 'page-attributes'],
            'rewrite'      => false,
            'show_in_rest' => false,
         ],
      ];

      /* ── Membership plans ───────────────────────────────────────────── */
      $types['dk_membership'] = [
         'singular' => __('Membership Plan', 'detailking'),
         'plural'   => __('Membership Plans', 'detailking'),
         'args'     => [
            'menu_icon'    => 'dashicons-awards',
            'public'       => false,
            'show_ui'      => true,
            'has_archive'  => false,
            'supports'     => ['title', 'page-attributes'],
            'rewrite'      => false,
            'show_in_rest' => false,
         ],
      ];

      /* ── Testimonials (the Google-review carousel) ──────────────────── */
      $types['dk_testimonial'] = [
         'singular' => __('Testimonial', 'detailking'),
         'plural'   => __('Testimonials', 'detailking'),
         'args'     => [
            'menu_icon'    => 'dashicons-format-quote',
            'public'       => false,
            'show_ui'      => true,
            'has_archive'  => false,
            'supports'     => ['title', 'page-attributes'],
            'rewrite'      => false,
            'show_in_rest' => false,
         ],
      ];

      /* ── Studio locations ───────────────────────────────────────────── */
      $types['dk_location'] = [
         'singular' => __('Location', 'detailking'),
         'plural'   => __('Locations', 'detailking'),
         'args'     => [
            'menu_icon'    => 'dashicons-location',
            'public'       => false,
            'show_ui'      => true,
            'has_archive'  => false,
            'supports'     => ['title', 'page-attributes'],
            'rewrite'      => false,
            'show_in_rest' => false,
         ],
      ];

      /* ── FAQs, grouped by the page they appear on ───────────────────── */
      $types['dk_faq'] = [
         'singular'   => __('FAQ', 'detailking'),
         'plural'     => __('FAQs', 'detailking'),
         'args'       => [
            'menu_icon'    => 'dashicons-editor-help',
            'public'       => false,
            'show_ui'      => true,
            'has_archive'  => false,
            'supports'     => ['title', 'page-attributes'],
            'rewrite'      => false,
            'show_in_rest' => false,
         ],
         'taxonomies' => [
            'faq_group' => [
               'singular' => __('FAQ Group', 'detailking'),
               'plural'   => __('FAQ Groups', 'detailking'),
               'args'     => ['public' => false, 'rewrite' => false, 'show_in_rest' => false],
            ],
         ],
      ];

      /* ── Gallery items. Reuses service_category, declared above. ─────── */
      $types['dk_gallery'] = [
         'singular'   => __('Gallery Item', 'detailking'),
         'plural'     => __('Gallery', 'detailking'),
         'args'       => [
            'menu_icon'   => 'dashicons-format-gallery',
            'has_archive' => false,
            'supports'    => ['title', 'thumbnail', 'page-attributes'],
            'rewrite'     => ['slug' => 'gallery', 'with_front' => false],
         ],
         'taxonomies' => [
            'service_category' => [],
         ],
      ];

      /* ── Bookings & enquiries submitted by the wizard ────────────────── */
      $types['dk_booking'] = [
         'singular' => __('Booking', 'detailking'),
         'plural'   => __('Bookings', 'detailking'),
         'args'     => [
            'menu_icon'    => 'dashicons-calendar-alt',
            'public'       => false,
            'show_ui'      => true,
            'has_archive'  => false,
            'supports'     => ['title'],
            'rewrite'      => false,
            'show_in_rest' => false,
            // Rows arrive from the booking wizard; there is no reason to type one
            // by hand, and an editor doing so would produce a record with no
            // pricing context.
            'capabilities' => ['create_posts' => 'do_not_allow'],
            'map_meta_cap' => true,
         ],
      ];

      return $types;
   }
}
