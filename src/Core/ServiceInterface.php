<?php

/**
 * SHARED CORE FRAMEWORK
 *
 * Contract every auto-loaded service must implement. The ServiceLoader scans
 * the configured directories and registers any concrete class implementing
 * this interface.
 */

namespace DetailKing\Theme\Core;

defined('ABSPATH') || exit;

interface ServiceInterface
{
   public function register(): void;
}
