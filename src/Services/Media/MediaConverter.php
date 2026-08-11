<?php

namespace DetailKing\Theme\Services\Media;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

if (!defined('ABSPATH')) {
  exit;
}

class MediaConverter extends Singleton implements ServiceInterface
{

  private const AVIF_QUALITY = 70;
  private const WEBP_QUALITY = 80;

  private const ALLOWED_MIME = [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/webp',
    'image/gif',
  ];

  public function register(): void
  {
    add_filter('wp_handle_upload', [$this, 'convertImageUpload'], 10, 2);
    add_action('delete_attachment', [$this, 'deleteConvertedImages']);
  }

  public function convertImageUpload(array $fileinfo): array
  {
    $filePath = $fileinfo['file'];

    // Validate by real image content, not the (spoofable) file extension.
    $realMime = function_exists('wp_get_image_mime') ? wp_get_image_mime($filePath) : false;

    if (!is_string($realMime) || !in_array($realMime, self::ALLOWED_MIME, true)) {
      return $fileinfo;
    }

    $uploadDir = wp_upload_dir();
    // Preserve original name for SEO and media library titles
    $baseName = pathinfo($filePath, PATHINFO_FILENAME);
    $newBase  = $uploadDir['path'] . '/' . sanitize_file_name($baseName);

    try {
      [$manager, $ext, $newMime] = $this->resolveDriver();

      $quality  = $ext === 'avif' ? self::AVIF_QUALITY : self::WEBP_QUALITY;
      $newFile  = $newBase . '.' . $ext;

      $image = $manager->read($filePath);
      $ext === 'avif'
        ? $image->toAvif($quality)->save($newFile)
        : $image->toWebp($quality)->save($newFile);

      if (!file_exists($newFile)) {
        return $fileinfo;
      }

      if ($filePath !== $newFile && file_exists($filePath)) {
        if (!unlink($filePath)) {
          error_log("MediaConverter: could not delete original file: {$filePath}");
        }
      }

      $fileinfo['file'] = $newFile;
      $fileinfo['url']  = $uploadDir['url'] . '/' . basename($newFile);
      $fileinfo['type'] = $newMime;
    } catch (\Throwable $e) {
      error_log('MediaConverter: conversion failed — ' . $e->getMessage());
    }

    return $fileinfo;
  }

  /**
   * Resolve the best available image driver and output format.
   * Filterable so other plugins can force a specific format.
   *
   * @return array{ImageManager, string, string} [manager, ext, mime]
   */
  private function resolveDriver(): array
  {
    $preferred = apply_filters('detailking/theme/media/format', 'auto');

    if ($preferred !== 'webp' && extension_loaded('imagick')) {
      $imagick = new \Imagick();
      if (!empty($imagick->queryFormats('AVIF'))) {
        return [new ImageManager(new ImagickDriver()), 'avif', 'image/avif'];
      }
    }

    if ($preferred !== 'webp') {
      $gdInfo = function_exists('gd_info') ? gd_info() : [];
      if (!empty($gdInfo['AVIF Support'])) {
        return [new ImageManager(new GdDriver()), 'avif', 'image/avif'];
      }
    }

    return [new ImageManager(new GdDriver()), 'webp', 'image/webp'];
  }

  public function deleteConvertedImages(int $id): void
  {
    $file = get_attached_file($id);
    if (!$file) {
      return;
    }

    $base = preg_replace('/\.(jpg|jpeg|png|gif|webp|avif)$/i', '', $file);
    if (!$base) {
      return;
    }

    foreach (['avif', 'webp'] as $ext) {
      $converted = $base . '.' . $ext;
      if (file_exists($converted) && !unlink($converted)) {
        error_log("MediaConverter: could not delete converted file: {$converted}");
      }
    }
  }
}
