<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * Lazily-built lookup of every default declared by every DefaultsProvider in
 * this directory.
 *
 * Deliberately NOT a ServiceInterface: it must be readable from ACF field
 * construction, from templates and from the seeder, all of which can run before
 * or after service boot. A pure static store with lazy discovery has no ordering
 * problem to get wrong.
 *
 * Names are namespaced by provider so two pages can both declare `hero_heading`
 * without colliding — see the note in FieldBuilderTrait about ACF field keys.
 */
final class DefaultsRegistry
{
   /** @var array<string,array<string,mixed>>|null provider slug => defaults */
   private static ?array $byProvider = null;

   /** @var array<string,DefaultsProvider>|null provider slug => instance */
   private static ?array $providers = null;

   /**
    * All providers, keyed by their short class name lowercased minus "defaults"
    * — HomepageDefaults becomes "homepage".
    *
    * @return array<string,DefaultsProvider>
    */
   public static function providers(): array
   {
      if (self::$providers !== null) {
         return self::$providers;
      }

      self::$providers = [];

      foreach (glob(__DIR__ . '/*Defaults.php') ?: [] as $file) {
         $short = basename($file, '.php');
         $class = __NAMESPACE__ . '\\' . $short;

         if (!class_exists($class)) {
            continue;
         }

         $ref = new \ReflectionClass($class);
         if ($ref->isAbstract() || !$ref->implementsInterface(DefaultsProvider::class)) {
            continue;
         }

         $slug = strtolower(preg_replace('/Defaults$/', '', $short) ?? $short);
         self::$providers[$slug] = $ref->newInstance();
      }

      return self::$providers;
   }

   /**
    * Defaults for one provider slug, e.g. 'homepage'.
    *
    * @return array<string,mixed>
    */
   public static function forProvider(string $slug): array
   {
      if (self::$byProvider === null) {
         self::$byProvider = [];
         foreach (self::providers() as $providerSlug => $provider) {
            self::$byProvider[$providerSlug] = $provider->defaults();
         }
      }

      return self::$byProvider[$slug] ?? [];
   }

   /**
    * A single default. Scoped to one provider when $slug is given; otherwise the
    * first provider that declares the name wins, which is only appropriate for
    * genuinely global values.
    *
    * @return mixed
    */
   public static function get(string $name, ?string $slug = null, mixed $fallback = ''): mixed
   {
      if ($slug !== null) {
         $value = self::forProvider($slug)[$name] ?? null;
         return $value === null ? $fallback : $value;
      }

      foreach (array_keys(self::providers()) as $providerSlug) {
         $values = self::forProvider($providerSlug);
         if (array_key_exists($name, $values)) {
            return $values[$name];
         }
      }

      return $fallback;
   }

   /** Test seam — forget the cached discovery. */
   public static function flush(): void
   {
      self::$providers  = null;
      self::$byProvider = null;
   }
}
