<?php

namespace DetailKing\Theme\Services\Media;

use DetailKing\Theme\Core\Singleton;

if (!defined('ABSPATH')) {
   exit;
}

/**
 * Utility for importing files into the media library from various sources.
 *
 * Not a registered service (does not implement ServiceInterface) — it is not
 * auto-booted. Call it on demand, e.g.:
 *
 *   use DetailKing\Theme\Services\Media\MediaUploader;
 *   $id = MediaUploader::getInstance()->fromUrl($url, $postId);
 *
 * SECURITY: when wiring these methods to untrusted input (AJAX/REST/forms),
 * the caller is responsible for capability checks (e.g. current_user_can(
 * 'upload_files')) and nonce verification. These methods do not perform them,
 * so they remain usable from trusted server-side contexts (cron, WP-CLI).
 */
class MediaUploader extends Singleton
{

   /**
    * Upload from a $_FILES form field.
    *
    * @return int|\WP_Error Attachment ID
    */
   public function fromForm(string $inputName, int $postId = 0): int|\WP_Error
   {
      $this->requireAdminIncludes();
      return media_handle_upload($inputName, $postId);
   }

   /**
    * Upload from a remote URL (e.g. pulling in an avatar or product image).
    *
    * @return int|\WP_Error Attachment ID
    */
   public function fromUrl(string $url, int $postId = 0, string $title = ''): int|\WP_Error
   {
      $this->requireAdminIncludes();

      // SSRF guard: reject internal/loopback hosts and non-http(s) schemes.
      if (!wp_http_validate_url($url)) {
         return new \WP_Error('invalid_url', 'The provided URL failed validation.');
      }

      $tmpFile = download_url($url);
      if (is_wp_error($tmpFile)) {
         return $tmpFile;
      }

      $fileArray = [
         'name'     => basename(parse_url($url, PHP_URL_PATH)),
         'tmp_name' => $tmpFile,
      ];

      $id = media_handle_sideload($fileArray, $postId, $title ?: null);

      if (file_exists($tmpFile)) {
         unlink($tmpFile);
      }

      return $id;
   }

   /**
    * Upload from a base64-encoded string (e.g. from a mobile app or API payload).
    *
    * @return int|\WP_Error Attachment ID
    */
   public function fromBase64(string $base64, string $filename, int $postId = 0): int|\WP_Error
   {
      $this->requireAdminIncludes();

      $decoded = base64_decode($base64, strict: true);
      if ($decoded === false) {
         return new \WP_Error('invalid_base64', 'Could not decode base64 string.');
      }

      // Unique, non-predictable temp path (avoids overwrite/collision in the
      // shared temp dir). media_handle_sideload still enforces allowed types.
      $tmpPath = wp_tempnam($filename);
      if (!$tmpPath) {
         return new \WP_Error('temp_failed', 'Could not create temp file.');
      }

      if (file_put_contents($tmpPath, $decoded) === false) {
         @unlink($tmpPath);
         return new \WP_Error('write_failed', 'Could not write temp file.');
      }

      $fileArray = [
         'name'     => sanitize_file_name($filename),
         'tmp_name' => $tmpPath,
      ];

      $id = media_handle_sideload($fileArray, $postId);

      if (file_exists($tmpPath)) {
         unlink($tmpPath);
      }

      return $id;
   }

   /**
    * Sideload a file already on disk into the media library.
    * Useful for files created by your own code (e.g. generated PDFs, exports).
    *
    * @return int|\WP_Error Attachment ID
    */
   public function fromPath(string $filePath, int $postId = 0, string $title = ''): int|\WP_Error
   {
      $this->requireAdminIncludes();

      if (!file_exists($filePath)) {
         return new \WP_Error('file_not_found', "File not found: {$filePath}");
      }

      $fileArray = [
         'name'     => basename($filePath),
         'tmp_name' => $filePath,
      ];

      return media_handle_sideload($fileArray, $postId, $title ?: null);
   }

   /**
    * WordPress admin includes are required for media functions
    * but are not loaded on the frontend by default.
    */
   private function requireAdminIncludes(): void
   {
      if (!function_exists('media_handle_upload')) {
         require_once ABSPATH . 'wp-admin/includes/file.php';
         require_once ABSPATH . 'wp-admin/includes/media.php';
         require_once ABSPATH . 'wp-admin/includes/image.php';
      }
   }
}
