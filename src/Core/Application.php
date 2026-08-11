<?php

/**
 * SHARED CORE FRAMEWORK (Bootstrap)
 *
 * Entry point booted from functions.php. Pulls the service list from
 * src/Config/services.php and registers each one.
 */

namespace DetailKing\Theme\Core;

defined('ABSPATH') || exit;

class Application extends Singleton implements ServiceInterface
{
   public function register(): void
   {
      $this->boot();
   }

   public function boot(): void
   {
      /**
       * Fires before any service is registered.
       */
      do_action('detailking/theme/before_boot');

      $services = require __DIR__ . '/../Config/services.php';

      foreach ($services as $service) {
         $service::getInstance()->register();
      }

      /**
       * Fires after all services have been registered.
       *
       * @param string[] $services Fully-qualified class names that were booted.
       */
      do_action('detailking/theme/booted', $services);
   }
}
