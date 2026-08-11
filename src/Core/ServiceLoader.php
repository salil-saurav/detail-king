<?php

/**
 * SHARED CORE FRAMEWORK
 *
 * Recursively scans a directory and returns the fully-qualified class names
 * of every concrete class that implements ServiceInterface. Used by
 * src/Config/services.php to auto-discover services without a manual registry.
 */

namespace DetailKing\Theme\Core;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

defined('ABSPATH') || exit;

class ServiceLoader
{
   public static function load(string $basePath, string $baseNamespace): array
   {
      $services = [];

      if (!is_dir($basePath)) {
         return $services;
      }

      $iterator = new RecursiveIteratorIterator(
         new RecursiveDirectoryIterator($basePath)
      );

      foreach ($iterator as $file) {
         if (!$file->isFile() || $file->getExtension() !== 'php') continue;

         $relative  = substr($file->getPathname(), strlen($basePath) + 1);
         $relative  = str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
         $relative  = preg_replace('/\.php$/', '', $relative);
         $class     = $baseNamespace . '\\' . $relative;

         if (!class_exists($class)) continue;

         $ref = new ReflectionClass($class);
         if ($ref->implementsInterface(ServiceInterface::class) && !$ref->isAbstract()) {
            $services[] = $class;
         }
      }

      return $services;
   }
}
