<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * "Global Options" ACF options page — site-wide content (logo, brand name,
 * footer copyright) and the custom-forms / leads settings read by FormService.
 *
 * A deliberately small starting point: add tabs and fields as your project
 * grows. Every value is read through MetaHelper::opt() / rows(). The whole
 * group is registered only when ACF is active, so the theme works without it.
 */
class GlobalFields extends Singleton implements ServiceInterface
{
   public const OPTIONS_SLUG = 'detailking-global-options';

   use FieldBuilderTrait;

   /** Namespaces this group's ACF field keys so they cannot collide with a page group. */
   protected function keyNamespace(): string
   {
      return 'global';
   }

   /** Backed by GlobalDefaults, so every field arrives carrying the comp's copy. */
   protected function defaultsSlug(): ?string
   {
      return 'global';
   }

   public function register(): void
   {
      add_action('acf/init', [$this, 'registerFields'], 10);
   }

   public function registerFields(): void
   {
      if (!function_exists('acf_add_local_field_group')) {
         return;
      }

      if (function_exists('acf_add_options_page')) {
         acf_add_options_page([
            'page_title'  => __('Global Options'),
            'menu_title'  => __('Global Options'),
            'menu_slug'   => self::OPTIONS_SLUG,
            'capability'  => 'edit_theme_options',
            'parent_slug' => SiteContentMenu::PARENT_SLUG,
            'redirect'    => false,
         ]);
      }

      // Image fields store the attachment ID, so templates can render them with
      // MediaHelper::get_image() (see MetaHelper::imageTag()).
      $img = ['return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all'];

      $fields = [

         /* ===== HEADER ===== */
         $this->tab('tab_header', __('Header', 'detailking')),
         $this->field('header_logo', __('Logo'), 'image', $img),
         $this->field('header_brand_name', __('Brand Name')),
         $this->field('header_account_text', __('Account Link Label', 'detailking')),
         ...$this->linkFields('header_cta', __('Header CTA', 'detailking')),

         /* ===== FOOTER ===== */
         $this->tab('tab_footer', __('Footer', 'detailking')),
         $this->field('footer_about_text', __('About Blurb', 'detailking'), 'textarea', ['rows' => 4]),
         $this->field('footer_watermark', __('Watermark Text', 'detailking'), 'text', [
            'instructions' => __('Large outlined wordmark behind the footer. Leave blank to hide it.', 'detailking'),
         ]),
         $this->repeater('footer_social_links', __('Social Links', 'detailking'), [
            $this->field('social_label', __('Label', 'detailking')),
            $this->field('social_icon', __('Icon', 'detailking'), 'select', [
               'choices' => [
                  'facebook'  => 'Facebook',
                  'instagram' => 'Instagram',
                  'youtube'   => 'YouTube',
                  'linkedin'  => 'LinkedIn',
                  'tiktok'    => 'TikTok',
                  'x'         => 'X / Twitter',
               ],
               'default_value' => 'facebook',
            ]),
            $this->field('social_url', __('URL', 'detailking')),
         ], ['button_label' => __('Add Social Link', 'detailking')]),
         $this->field('footer_copyright', __('Copyright Text'), 'text', [
            'instructions' => __('Use {year} for the current year.'),
         ]),
         $this->field('footer_legal_text', __('Legal / Tagline Line', 'detailking')),

         /* ===== CONTACT =====
            Shown in the footer, on the Contact page and in every booking form.
            One source, because three copies of a phone number drift. */
         $this->tab('tab_contact', __('Contact Details', 'detailking')),
         $this->field('contact_address', __('Address', 'detailking')),
         $this->field('contact_phone', __('Phone', 'detailking')),
         $this->field('contact_email', __('Email', 'detailking'), 'email'),
         $this->field('contact_hours', __('Opening Hours', 'detailking')),

         /* ===== SOCIAL PROOF =====
            The ticker and the review summary both appear on more than one page
            frame, so they are global rather than per-page. */
         $this->tab('tab_proof', __('Ticker & Reviews', 'detailking')),
         $this->field('ticker_text', __('Marquee Ticker Text', 'detailking'), 'text', [
            'instructions' => __('Scrolls infinitely across the gold bar. Separate items with ◆.', 'detailking'),
         ]),
         $this->field('reviews_average', __('Review Average', 'detailking')),
         $this->field('reviews_count', __('Review Count', 'detailking')),
         $this->field('instagram_handle', __('Instagram Handle', 'detailking')),

         /* ===== FORMS / LEADS ===== */
         $this->tab('tab_forms', __('Forms & Leads')),
         $this->field('forms_thank_you_page', __('Thank You Page'), 'page_link', [
            'instructions' => __('Visitors are redirected here after a successful submission. Defaults to the "thank-you" page.'),
            'post_type'    => ['page'],
            'allow_null'   => 1,
            'multiple'     => 0,
         ]),
         $this->field('forms_notify_email', __('Notification Email'), 'email', [
            'instructions' => __('Where new-lead notifications are sent. Defaults to the site admin email.'),
         ]),
         $this->field('forms_throttle_seconds', __('Min. Seconds Between Submissions'), 'number', [
            'instructions'  => __('Per-visitor cooldown between submissions (anti-spam).'),
            'default_value' => 30,
            'min'           => 0,
         ]),
         $this->field('forms_throttle_max', __('Max Submissions Per Hour'), 'number', [
            'instructions'  => __('Per-visitor hourly submission cap (anti-spam).'),
            'default_value' => 8,
            'min'           => 1,
         ]),
         $this->field('forms_min_fill_seconds', __('Min. Fill Time (Seconds)'), 'number', [
            'instructions'  => __('Submissions faster than this are treated as bots.'),
            'default_value' => 3,
            'min'           => 0,
         ]),
      ];

      acf_add_local_field_group([
         'key'                   => 'group_detailking_global',
         'title'                 => __('Global Options'),
         'fields'                => $fields,
         'location'              => [
            [
               [
                  'param'    => 'options_page',
                  'operator' => '==',
                  'value'    => self::OPTIONS_SLUG,
               ],
            ],
         ],
         'menu_order'            => 0,
         'position'              => 'normal',
         'style'                 => 'default',
         'label_placement'       => 'top',
         'instruction_placement' => 'label',
         'active'                => true,
      ]);
   }
}
