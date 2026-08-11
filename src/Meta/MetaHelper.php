<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use DetailKing\Theme\Helpers\MediaHelper;

defined('ABSPATH') || exit;

/**
 * Theme-wide ACF accessor used across every template context — options pages,
 * pages, single posts, archives and taxonomy terms.
 *
 * Thin wrappers around get_field() with consistent fallbacks:
 *   - opt()/rows()/optImage()  → the shared ACF option store ('option').
 *   - field()/fieldRows()      → any other context (current post/term, or an
 *     explicit ACF post id), with a caller-supplied default.
 *
 * Degrades gracefully: if ACF is not active, templates still render using the
 * passed defaults (empty by default).
 *
 * Usage in templates:
 *   $meta = \DetailKing\Theme\Meta\MetaHelper::getInstance();
 *   echo esc_html($meta->opt('header_brand_name'));          // option store
 *   echo esc_html($meta->field('subtitle'));                 // current post/page
 *   foreach ($meta->rows('social_links') as $row) { ... }
 *   echo esc_url($meta->optImage('header_logo'));
 */
class MetaHelper extends Singleton implements ServiceInterface
{
   /** This is a passive accessor; nothing to hook on boot. */
   public function register(): void {}

   /**
    * Get a single option-page field value, with default fallback.
    *
    * @param string $name    Field name.
    * @param mixed  $default Optional fallback when ACF is inactive or empty.
    * @return mixed
    */
   public function opt(string $name, mixed $default = ''): mixed
   {
      $value = function_exists('get_field') ? get_field($name, 'option') : null;

      if ($value === null || $value === false || $value === '') {
         return $default;
      }

      return $value;
   }

   /**
    * Generic get_field() wrapper for any context (term, post, page).
    *
    * @param string                    $name    Field name.
    * @param int|string|bool|\WP_Term  $postId  ACF post id (term object/"taxonomy_ID", post ID, "option"). False = current.
    * @param mixed                     $default Fallback when ACF is inactive or the value is empty.
    * @return mixed
    */
   public function field(string $name, int|string|bool|\WP_Term $postId = false, mixed $default = ''): mixed
   {
      $value = function_exists('get_field') ? get_field($name, $postId) : null;

      if ($value === null || $value === false || $value === '') {
         return $default;
      }

      return $value;
   }

   /**
    * field() with the default resolved from the DefaultsProvider instead of the
    * caller having to remember it.
    *
    * Prefer this over field() in templates. The third argument of field() is what
    * makes the ACF layer optional, and the whole class of "empty field silently
    * deletes the element" bugs comes from someone omitting it. Here it cannot be
    * omitted — the default is looked up from the same declaration that seeded the
    * ACF field in the first place.
    *
    * @return mixed
    */
   public function fieldOr(string $name, string $providerSlug, int|string|bool|\WP_Term $postId = false): mixed
   {
      return $this->field($name, $postId, Defaults\DefaultsRegistry::get($name, $providerSlug));
   }

   /**
    * opt() with the default resolved from the DefaultsProvider.
    *
    * @return mixed
    */
   public function optOr(string $name, string $providerSlug = 'global'): mixed
   {
      return $this->opt($name, Defaults\DefaultsRegistry::get($name, $providerSlug));
   }

   /**
    * Repeater rows, falling back to the declared default rows when the field is
    * empty or ACF is inactive. This is the only way a repeater can degrade
    * gracefully — ACF has no `default_value` for repeaters.
    *
    * @return array<int,array<string,mixed>>
    */
   public function fieldRowsOr(string $name, string $providerSlug, int|string|bool|\WP_Term $postId = false): array
   {
      $rows = $this->fieldRows($name, $postId);

      if ($rows !== []) {
         return $rows;
      }

      $default = Defaults\DefaultsRegistry::get($name, $providerSlug, []);

      return is_array($default) ? $default : [];
   }

   /**
    * Options-page repeater rows with the declared default rows as fallback.
    *
    * @return array<int,array<string,mixed>>
    */
   public function rowsOr(string $name, string $providerSlug = 'global'): array
   {
      $rows = $this->rows($name);

      if ($rows !== []) {
         return $rows;
      }

      $default = Defaults\DefaultsRegistry::get($name, $providerSlug, []);

      return is_array($default) ? $default : [];
   }

   /**
    * Generic get_field() for a repeater in any context; always returns an array.
    *
    * @param string                    $name   Field name.
    * @param int|string|bool|\WP_Term  $postId ACF post id (term object accepted). False = current.
    * @return array<int,array<string,mixed>>
    */
   public function fieldRows(string $name, int|string|bool|\WP_Term $postId = false): array
   {
      $rows = function_exists('get_field') ? get_field($name, $postId) : null;
      return (is_array($rows)) ? $rows : [];
   }

   /**
    * Get an option-page repeater's rows; always returns an array.
    *
    * @param string $name Repeater field name.
    * @return array<int,array<string,mixed>>
    */
   public function rows(string $name): array
   {
      $rows = function_exists('get_field') ? get_field($name, 'option') : null;
      return (is_array($rows)) ? $rows : [];
   }

   /**
    * Resolve an image field value (ACF array, attachment ID, or URL) to a URL.
    *
    * @param mixed  $value   Already-retrieved field value.
    * @param string $default Fallback URL.
    * @return string
    */
   public function imageUrl(mixed $value, string $default = ''): string
   {
      if (is_array($value)) {
         return !empty($value['url']) ? $value['url'] : $default;
      }
      if (is_numeric($value)) {
         $url = wp_get_attachment_image_url((int) $value, 'full');
         return $url ?: $default;
      }
      if (is_string($value) && $value !== '') {
         return $value;
      }
      return $default;
   }

   /**
    * Convenience: resolve an option-page image field to a URL with default fallback.
    *
    * @param string $name    Field name.
    * @param string $default Fallback URL.
    * @return string
    */
   public function optImage(string $name, string $default = ''): string
   {
      $value = function_exists('get_field') ? get_field($name, 'option') : null;
      return $this->imageUrl($value, $default);
   }

   /**
    * Resolve image alt text from an ACF image array, with fallback.
    *
    * @param mixed  $value   The field value.
    * @param string $default Fallback alt text.
    * @return string
    */
   public function imageAlt(mixed $value, string $default = ''): string
   {
      if (is_array($value) && !empty($value['alt'])) {
         return $value['alt'];
      }
      return $default;
   }

   /**
    * Render an image field as responsive <img> markup via MediaHelper::get_image().
    *
    * Image fields store an attachment ID (ACF return_format 'id'); when one is set
    * we hand it to MediaHelper. When it is empty we fall back to an explicit URL
    * value (a legacy URL or a default placeholder) so the layout still renders.
    *
    * @param mixed        $value       Attachment ID (or legacy URL/array).
    * @param string|array $size        Image size passed to get_image() (default 'full').
    * @param array        $attrs       Extra <img> attributes for get_image().
    * @param string       $placeholder Fallback image URL when no attachment is set.
    * @return string
    */
   public function imageTag(mixed $value, string|array $size = 'full', array $attrs = [], string $placeholder = ''): string
   {
      $id = $this->attachmentId($value);

      if ($id > 0) {
         return MediaHelper::getInstance()->get_image($id, $size, $attrs);
      }

      // No attachment: use the value itself if it's already a URL, else the placeholder.
      $url = (is_string($value) && $value !== '') ? $value : $placeholder;

      return $url !== '' ? $this->placeholderTag($url, $attrs) : '';
   }

   /**
    * Convenience for option-page image fields: renders via imageTag().
    *
    * @param string       $name        Field name.
    * @param string|array $size        Image size (default 'full').
    * @param array        $attrs       Extra <img> attributes for get_image().
    * @param string       $placeholder Fallback image URL when no attachment is set.
    * @return string
    */
   public function optImageTag(string $name, string|array $size = 'full', array $attrs = [], string $placeholder = ''): string
   {
      $value = function_exists('get_field') ? get_field($name, 'option') : null;
      return $this->imageTag($value, $size, $attrs, $placeholder);
   }

   /**
    * Normalise an image field value to an attachment ID. Accepts the ID return
    * format, plus legacy array / numeric-string values for safety.
    */
   private function attachmentId(mixed $value): int
   {
      if (is_array($value)) {
         return (int) ($value['ID'] ?? $value['id'] ?? 0);
      }
      return is_numeric($value) ? (int) $value : 0;
   }

   /**
    * Minimal <img> for a placeholder/legacy URL, honouring the alt/class attrs
    * that would otherwise have been passed to get_image().
    *
    * @param array $attrs
    */
   private function placeholderTag(string $url, array $attrs = []): string
   {
      $alt   = isset($attrs['alt']) ? (string) $attrs['alt'] : '';
      $class = trim('img-fluid ' . (string) ($attrs['class'] ?? ''));

      return sprintf(
         '<img src="%s" alt="%s" class="%s" loading="lazy">',
         esc_url($url),
         esc_attr($alt),
         esc_attr($class)
      );
   }
}
