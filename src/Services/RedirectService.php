<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Simple config-driven URL redirects, matched against the request path.
 *
 * Define redirects via the 'detailking/theme/redirects' filter:
 *
 *   add_filter('detailking/theme/redirects', function (array $map): array {
 *      $map['/old-page'] = '/new-page';
 *      return $map;
 *   });
 */
class RedirectService extends Singleton implements ServiceInterface
{
   public function register(): void
   {
      add_action('template_redirect', [$this, 'handleRedirects']);
   }

   public function handleRedirects(): void
   {
      $redirects = (array) apply_filters('detailking/theme/redirects', []);

      if (empty($redirects)) {
         return;
      }

      $requestUri = rtrim((string) parse_url(wp_unslash($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');

      foreach ($redirects as $from => $to) {
         if ($requestUri === rtrim((string) $from, '/')) {
            wp_safe_redirect(home_url($to), 301);
            exit;
         }
      }
   }
}
