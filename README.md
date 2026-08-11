# StackPress

A lightweight, OOP WordPress theme **boilerplate**. It provides a clean,
service-based architecture you can drop into any new project and start building
on immediately. It ships **blank** — no demo content, no design — just the
framework and a minimal set of templates.

## Features

- **PSR-4 autoloading** via Composer (`DetailKing\Theme\` → `src/`).
- **Auto-discovered services** — drop a class implementing `ServiceInterface`
  anywhere under `src/Services/`, `src/Helpers/`, or `src/Meta/` and it is
  registered on boot. No manual wiring.
- **Bootstrap 5.3.8, self-hosted** — bundled in `assets/lib/bootstrap/` and
  enqueued by default (the JS bundle includes Popper). **No jQuery** — it is
  deregistered on the front end by `DebloaterService`.
- **Configuration-driven `AssetsService`** — register scripts/styles with
  conditional loading, automatic cache-busting (`filemtime` versioning), ES
  module support, and a critical-CSS preload pattern.
- **`DebloaterService`** — removes WordPress bloat (emojis, head meta, block
  library CSS, comments, XML-RPC, front-end jQuery, dashboard widgets) and
  enforces the Classic Editor. Filterable via `detailking/theme/debloater/config`.
- **`SecurityService`** — adds security response headers, removes the
  `X-Pingback` header, blocks `?author=N` username enumeration, hides the REST
  users endpoints from anonymous requests, and returns generic login errors.
- **Custom forms + leads** — `FormService` handles spam-hardened submissions
  over the REST API (nonce, honeypot, signed time-trap, per-IP throttling),
  stores each as a read-only **Lead** post (`LeadPostType`), and emails a
  notification. Forms are declared in `FormRegistry` / the `detailking/theme/forms`
  filter; the front-end handler is `assets/js/forms.js` (`<form data-sp-form="…">`).
- **AJAX live search** — `SearchService` registers a `stackpress_live_search`
  endpoint and renders a header search overlay; `assets/js/global.js` queries it.
- **`MediaConverter` / `MediaUploader`** — convert uploads to AVIF/WebP and
  import media from form/URL/base64/path (with SSRF and content-type guards).
- **`AdminCustomizations`** — enqueues admin assets, customizes the admin footer
  text (`detailking/theme/admin/footer_text`), removes the welcome panel.
- **`ShowCurrentTemplate`** — shows the rendered template file in the front-end
  admin bar for administrators (a quick "which template is this?" dev aid).
- **`PostTypeService`** — config-driven custom post type + taxonomy registrar
  with auto-generated labels, via the `detailking/theme/post_types` filter.
- **`RedirectService`** — simple path-based 301 redirect map via the
  `detailking/theme/redirects` filter.
- **Optional ACF layer** — `MetaHelper` (a safe `get_field()` accessor with
  fallbacks), `AbstractPageMeta` (per-page field groups), `FieldBuilderTrait`,
  a "Site Content" options menu (`SiteContentMenu`) and a minimal "Global
  Options" page (`GlobalFields`). All degrade gracefully when ACF is inactive.
- **Helpers** — responsive images (`MediaHelper`), nav menus (`MenuHelper`),
  and a context-aware breadcrumb builder (`ThemeHelper`).

## Setup

```bash
# From the theme directory
composer install        # or: composer dump-autoload
```

The bootstrap loads `vendor/autoload.php` if present. `vendor/` is gitignored;
run the command above after cloning. (The Advanced Custom Fields plugin is
optional — the ACF layer is only active when ACF is installed.)

## Architecture

```
functions.php
  └── DetailKing\Theme\Core\Application::boot()
        └── reads src/Config/services.php
              └── ServiceLoader scans src/Services, src/Helpers, src/Meta
                    └── calls register() on every ServiceInterface
```

| Path                      | Responsibility                                                             |
| ------------------------- | -------------------------------------------------------------------------- |
| `src/Core/`               | Framework: `Singleton`, `ServiceInterface`, `ServiceLoader`, `Application` |
| `src/Config/services.php` | Directories scanned for services                                           |
| `src/Services/`           | Feature services (assets, debloater, security, forms, media, search…)      |
| `src/Helpers/`            | Reusable helper singletons                                                 |
| `src/Meta/`               | Optional ACF accessor + field-group scaffolding                            |
| `template-parts/`         | Reusable template fragments (header, footer, breadcrumb, page banner)      |
| `assets/`                 | CSS / JS, auto-versioned by `AssetsService`                                |

## Where to start on a new project

1. Set the brand in `style.css` (Theme Name / Description) and a `screenshot.png`.
2. Register your post types and forms in `functions.php` (see `docs/filter.md`).
3. Build your design in `assets/css/global.css` and the templates; register
   page-specific assets in `AssetsService::registerPageAssets()`.
4. Add content fields by extending `AbstractPageMeta` or expanding `GlobalFields`.

## Creating a new service

```php
namespace DetailKing\Theme\Services;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;

defined('ABSPATH') || exit;

class MyService extends Singleton implements ServiceInterface
{
   public function register(): void
   {
      // add_action(...) / add_filter(...)
   }
}
```

That's it — it is auto-registered on the next request.

## Child themes

StackPress is **child-theme safe**: the framework locates its own services and
assets via the *template* (parent) directory, so it boots and enqueues normally
even when a child theme is active.

## Renaming the namespace for a new project

This boilerplate ships with the `DetailKing\Theme` namespace and `stackpress`
text domain. To rebrand for a project, search-and-replace:

- `DetailKing\Theme` → `YourNamespace\Theme` (PHP namespaces & `use` statements)
- `detailking/theme/` → `yourns/theme/` (filter/hook prefixes)
- `stackpress` → `your-text-domain` (i18n text domain)
- the `psr-4` map in `composer.json`, then run `composer dump-autoload`
- the `Theme Name` / `Text Domain` in `style.css`

## Hooks reference

See [`docs/actions.md`](docs/actions.md) and [`docs/filter.md`](docs/filter.md).
