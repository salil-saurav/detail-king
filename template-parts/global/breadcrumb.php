<?php

/**
 * Renders the breadcrumb trail via ThemeHelper.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;

use DetailKing\Theme\Helpers\ThemeHelper;

echo ThemeHelper::getInstance()->get_breadcrumbs();
