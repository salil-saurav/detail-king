<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

/**
 * Hardens common WordPress attack surfaces:
 *  - adds security response headers
 *  - removes the X-Pingback header (pingbacks are an SSRF/DDoS vector)
 *  - blocks ?author=N username enumeration
 *  - hides the REST users endpoints from unauthenticated requests
 *  - returns generic login errors so valid usernames are not confirmed
 *
 * Every behaviour is filterable so projects can adjust per environment.
 */
class SecurityService extends Singleton implements ServiceInterface
{
   public function register(): void
   {
      add_filter('wp_headers', [$this, 'removePingbackHeader']);
      add_action('send_headers', [$this, 'sendSecurityHeaders']);
      add_action('template_redirect', [$this, 'blockAuthorEnumeration']);
      add_filter('rest_endpoints', [$this, 'restrictUserEndpoints']);
      add_filter('login_errors', [$this, 'genericLoginError']);
   }

   /**
    * Default security headers. Filterable; set a value to '' to omit it.
    *
    * @return array<string, string>
    */
   private function headers(): array
   {
      return apply_filters('detailking/theme/security/headers', [
         'X-Content-Type-Options' => 'nosniff',
         'X-Frame-Options'        => 'SAMEORIGIN',
         'Referrer-Policy'        => 'strict-origin-when-cross-origin',
         'X-XSS-Protection'       => '1; mode=block',
      ]);
   }

   public function sendSecurityHeaders(): void
   {
      if (headers_sent()) {
         return;
      }

      foreach ($this->headers() as $name => $value) {
         if ($value !== '' && $value !== null) {
            header(sprintf('%s: %s', $name, $value));
         }
      }
   }

   /**
    * @param array<string, string> $headers
    * @return array<string, string>
    */
   public function removePingbackHeader(array $headers): array
   {
      unset($headers['X-Pingback']);
      return $headers;
   }

   /**
    * Block the classic ?author=N enumeration vector for visitors, which would
    * otherwise 301-redirect to /author/{username}/ and leak the login slug.
    */
   public function blockAuthorEnumeration(): void
   {
      if (is_admin() || is_user_logged_in()) {
         return;
      }

      if (!apply_filters('detailking/theme/security/block_author_enumeration', true)) {
         return;
      }

      // Only the raw numeric ?author=<id> query is an enumeration vector.
      if (isset($_GET['author']) && (int) $_GET['author'] > 0) {
         wp_safe_redirect(home_url('/'), 301);
         exit;
      }
   }

   /**
    * Hide the REST users endpoints from unauthenticated requests so usernames
    * cannot be harvested via /wp-json/wp/v2/users.
    *
    * @param array<string, mixed> $endpoints
    * @return array<string, mixed>
    */
   public function restrictUserEndpoints(array $endpoints): array
   {
      if (is_user_logged_in()) {
         return $endpoints;
      }

      if (!apply_filters('detailking/theme/security/restrict_rest_users', true)) {
         return $endpoints;
      }

      foreach (['/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)'] as $route) {
         unset($endpoints[$route]);
      }

      return $endpoints;
   }

   /**
    * Return a generic login error to avoid confirming valid usernames.
    */
   public function genericLoginError(string $error): string
   {
      return __('Invalid login credentials.');
   }
}
