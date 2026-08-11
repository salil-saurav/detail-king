<?php

/**
 * SHARED CORE FRAMEWORK
 *
 * This class is intended to be identical across any project (theme/plugin)
 * built on this boilerplate. If you modify it, keep all copies synchronized.
 */

namespace DetailKing\Theme\Core;

defined('ABSPATH') || exit;

abstract class Singleton
{
   private static array $instances = [];

   protected function __construct() {}
   private function __clone() {}

   public static function getInstance(): static
   {
      $class = static::class;
      if (!isset(self::$instances[$class])) {
         self::$instances[$class] = new static();
      }
      return self::$instances[$class];
   }
}
