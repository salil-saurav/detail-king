<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Forms;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Single source of truth for every custom (non-Contact-Form-7) form on the site.
 *
 * Each entry declares the form's id (used in the REST route, the markup
 * `data-sp-form` attribute and the stored lead), a human label, and its fields
 * with a sanitiser + required flag. A REST endpoint is registered for every
 * form here (see FormService), and the admin lead listing reads the field
 * labels back from this registry.
 *
 * Add or change forms via the `detailking/theme/forms` filter — no other file
 * needs to change.
 *
 * Field `type` controls sanitisation:
 *   text | tel | select  → sanitize_text_field
 *   email                 → sanitize_email + is_email() validation
 *   textarea              → sanitize_textarea_field
 *   url                   → esc_url_raw
 */
class FormRegistry extends Singleton implements ServiceInterface
{
   /** @var array<string,array<string,mixed>>|null Lazily built, cached per request. */
   private ?array $forms = null;

   /** Passive registry; nothing to hook on boot. */
   public function register(): void {}

   /**
    * All registered forms, keyed by form id.
    *
    * @return array<string,array<string,mixed>>
    */
   public function all(): array
   {
      if ($this->forms !== null) {
         return $this->forms;
      }

      $defaults = [
         'contact' => [
            'label'  => __('Contact'),
            'fields' => [
               'name'     => ['label' => __('Name'), 'type' => 'text', 'required' => true],
               'phone'    => ['label' => __('Phone'), 'type' => 'tel', 'required' => false],
               'email'    => ['label' => __('Email'), 'type' => 'email', 'required' => true],
               'location' => ['label' => __('Location'), 'type' => 'select', 'required' => false],
               'message'  => ['label' => __('Message'), 'type' => 'textarea', 'required' => false],
            ],
         ],

         /**
          * Homepage "Book in Minutes" quick-booking form.
          *
          * This existed in the markup with `data-sp-form="home-booking"` but was
          * never registered here, so every submission failed as an unknown form
          * id — the field labels and order below mirror
          * template-parts/sections/home/booking.php exactly.
          *
          * Deliberately a *lead*, not a cart action. It is a short enquiry form
          * with no package price or vehicle-size multiplier attached, so it
          * cannot produce a priced line item the way the service-page widget
          * will once BUILD-PLAN §7 step 7 turns that one into a cart
          * configurator. Keeping the two distinct is the point.
          */
         'home-booking' => [
            'label'  => __('Homepage Quick Booking'),
            'fields' => [
               'service'      => ['label' => __('Choose Service'), 'type' => 'select', 'required' => false],
               'package'      => ['label' => __('Choose Package'), 'type' => 'select', 'required' => false],
               'vehicle_type' => ['label' => __('Vehicle Type'), 'type' => 'select', 'required' => false],
               'addons'       => ['label' => __('Optional Add-Ons'), 'type' => 'select', 'required' => false],
               'name'         => ['label' => __('Name'), 'type' => 'text', 'required' => true],
               'phone'        => ['label' => __('Phone Number'), 'type' => 'tel', 'required' => true],
               'email'        => ['label' => __('Email'), 'type' => 'email', 'required' => true],
               'location'     => ['label' => __('Location'), 'type' => 'select', 'required' => false],
               'drop_date'    => ['label' => __('Drop Date'), 'type' => 'text', 'required' => false],
               'drop_time'    => ['label' => __('Drop Time'), 'type' => 'text', 'required' => false],
            ],
         ],
      ];

      /**
       * Filter the registered custom forms.
       *
       * @param array<string,array<string,mixed>> $forms
       */
      $forms = (array) apply_filters('detailking/theme/forms', $defaults);

      // Normalise: every form gets an `id` and every field a fully-shaped def.
      $normalised = [];
      foreach ($forms as $id => $form) {
         $id = sanitize_key((string) $id);
         if ($id === '' || empty($form['fields']) || !is_array($form['fields'])) {
            continue;
         }

         $fields = [];
         foreach ($form['fields'] as $key => $field) {
            $key = sanitize_key((string) $key);
            if ($key === '') {
               continue;
            }
            $fields[$key] = [
               'label'    => (string) ($field['label'] ?? ucfirst($key)),
               'type'     => (string) ($field['type'] ?? 'text'),
               'required' => (bool) ($field['required'] ?? false),
            ];
         }

         $normalised[$id] = [
            'id'     => $id,
            'label'  => (string) ($form['label'] ?? ucfirst($id)),
            'fields' => $fields,
         ];
      }

      return $this->forms = $normalised;
   }

   /**
    * A single form definition, or null if the id is unknown.
    *
    * @return array<string,mixed>|null
    */
   public function get(string $id): ?array
   {
      return $this->all()[sanitize_key($id)] ?? null;
   }

   /** Whether a form id is registered. */
   public function exists(string $id): bool
   {
      return isset($this->all()[sanitize_key($id)]);
   }
}
