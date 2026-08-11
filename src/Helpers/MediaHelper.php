<?php

namespace DetailKing\Theme\Helpers;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Class MediaHelper
 *
 * Helper methods for generating responsive image markup using WordPress
 * built-in functions.
 */
class MediaHelper extends Singleton implements ServiceInterface
{
   public function register(): void {}

   /**
    * Generate responsive image HTML for a given attachment ID.
    *
    * @param int          $id    Attachment ID.
    * @param string|array $size  Image size (default 'full').
    * @param array        $attrs Additional HTML attributes for the <img> tag.
    *                            Pass 'remove_style' => true to strip inline styles.
    *
    * @return string HTML markup for the image.
    */
   public function get_image(int $id, string|array $size = 'full', array $attrs = []): string
   {
      if (!$id) {
         return '';
      }

      $defaultAttrs = [
         'loading' => 'lazy',
      ];

      $attrs = array_merge($defaultAttrs, $attrs);

      $defaultClass = 'img-fluid';
      $passedClass  = $attrs['class'] ?? '';

      $classes = trim($defaultClass . ' ' . $passedClass);
      $classes = implode(' ', array_unique(explode(' ', $classes)));

      $attrs['class'] = $classes;

      $removeStyle = !empty($attrs['remove_style']);
      unset($attrs['remove_style']);

      $mime = get_post_mime_type($id);
      $html = wp_get_attachment_image($id, $size, false, $attrs);

      // Remove width/height for SVGs
      if ($mime === 'image/svg+xml') {
         $html = preg_replace('/\s(width|height)="\d+"/i', '', $html);
      }

      if ($removeStyle) {
         $html = preg_replace('/\sstyle="[^"]*"/i', '', $html);
      }

      return $html;
   }
}
