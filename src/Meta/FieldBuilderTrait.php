<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

use DetailKing\Theme\Meta\Defaults\DefaultsRegistry;

defined('ABSPATH') || exit;

/**
 * Shared ACF field-array builders. Used by the options-page field group
 * (GlobalFields) and any per-page group (AbstractPageMeta subclasses) so the
 * array boilerplate lives in one place.
 *
 * TWO THINGS THIS SOLVES that the bare boilerplate did not:
 *
 * 1. ACF FIELD KEYS MUST BE GLOBALLY UNIQUE.
 *    Keys used to be derived as `field_{name}`, so the moment a second page
 *    group also declared `hero_heading` the two groups shared a key and ACF
 *    silently rendered one field in both places, reading and writing the same
 *    value. Every group now contributes a keyNamespace(), giving
 *    `field_{ns}_{name}` while the *name* (and therefore the meta key and the
 *    template call) stays short and readable.
 *
 * 2. DEFAULTS COME FROM A PROVIDER, NOT FROM ONE HARD-CODED CLASS.
 *    field() looks the name up in the group's DefaultsProvider and injects it as
 *    `default_value`, so a freshly created page arrives carrying the comp's own
 *    copy. Override defaultsSlug() to point a group at its provider. An explicit
 *    `default_value` in $extra always wins.
 */
trait FieldBuilderTrait
{
   /**
    * Prefix applied to ACF field *keys* (not names) so keys stay globally
    * unique. Override per group; an empty string keeps the legacy behaviour.
    */
   protected function keyNamespace(): string
   {
      return '';
   }

   /**
    * DefaultsRegistry provider slug backing this group, e.g. 'homepage' for
    * HomepageDefaults. Null means the group declares no defaults.
    */
   protected function defaultsSlug(): ?string
   {
      return null;
   }

   /** Build a namespaced ACF field key. */
   protected function fieldKey(string $name): string
   {
      $ns = $this->keyNamespace();

      return 'field_' . ($ns !== '' ? $ns . '_' : '') . $name;
   }

   /**
    * The declared default for a field name, or null when there is none.
    *
    * @return mixed
    */
   protected function defaultFor(string $name): mixed
   {
      $slug = $this->defaultsSlug();

      if ($slug === null) {
         return null;
      }

      $value = DefaultsRegistry::forProvider($slug)[$name] ?? null;

      // Repeater defaults are seeded as rows, not as an ACF default_value —
      // ACF has no concept of a default repeater. Skip arrays here.
      return is_array($value) ? null : $value;
   }

   protected function tab(string $name, string $label): array
   {
      return [
         'key'       => $this->fieldKey($name),
         'label'     => $label,
         'name'      => '',
         'type'      => 'tab',
         'placement' => 'left',
      ];
   }

   /**
    * @param array<string,mixed> $extra
    */
   protected function field(string $name, string $label, string $type = 'text', array $extra = []): array
   {
      $base = [
         'key'   => $this->fieldKey($name),
         'label' => $label,
         'name'  => $name,
         'type'  => $type,
      ];

      // Inject the declared default unless the caller set one explicitly.
      if (!array_key_exists('default_value', $extra)) {
         $default = $this->defaultFor($name);
         if ($default !== null && $default !== '') {
            $base['default_value'] = $default;
         }
      }

      return array_merge($base, $extra);
   }

   /**
    * @param array<int,array<string,mixed>> $subFields
    * @param array<string,mixed>            $extra
    */
   protected function repeater(string $name, string $label, array $subFields, array $extra = []): array
   {
      return array_merge(
         [
            'key'          => $this->fieldKey($name),
            'label'        => $label,
            'name'         => $name,
            'type'         => 'repeater',
            'layout'       => 'block',
            'button_label' => __('Add Row', 'detailking'),
            'sub_fields'   => $subFields,
         ],
         $extra
      );
   }

   /** Shared image-field config: store the attachment ID for MetaHelper::imageTag(). */
   protected function imageArgs(): array
   {
      return ['return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all'];
   }

   /** Reusable icon / title / text sub-field set for feature repeaters. */
   protected function iconItemSubFields(string $prefix): array
   {
      return [
         $this->field($prefix . '_icon', __('Icon', 'detailking'), 'image', $this->imageArgs()),
         $this->field($prefix . '_title', __('Title', 'detailking')),
         $this->field($prefix . '_text', __('Description', 'detailking'), 'textarea', ['rows' => 3]),
      ];
   }

   /**
    * Link pair — the comp's CTAs are always a label plus a URL, and they are
    * always wanted together.
    *
    * @return array<int,array<string,mixed>>
    */
   protected function linkFields(string $prefix, string $label): array
   {
      return [
         $this->field($prefix . '_text', $label . ' ' . __('Label', 'detailking')),
         $this->field($prefix . '_url', $label . ' ' . __('URL', 'detailking'), 'text'),
      ];
   }
}
