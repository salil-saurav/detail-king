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

      // One generic example form. Add your own (or change this) via the filter.
      $defaults = [
         'contact' => [
            'label'  => __('Contact'),
            'fields' => [
               'name'    => ['label' => __('Full Name'), 'type' => 'text', 'required' => true],
               'email'   => ['label' => __('Email Address'), 'type' => 'email', 'required' => true],
               'message' => ['label' => __('Message'), 'type' => 'textarea', 'required' => false],
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
