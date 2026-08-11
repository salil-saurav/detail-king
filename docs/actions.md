# Actions

## Custom action hooks

StackPress fires two action hooks during boot (from `Core\Application::boot()`,
which runs on WordPress's `after_setup_theme`). Use them to register your own
hooks at a predictable point in the theme lifecycle.

| Action | When | Args |
|--------|------|------|
| `detailking/theme/before_boot` | before any service is registered | — |
| `detailking/theme/booted` | after all services are registered | `string[] $services` |

### `detailking/theme/before_boot`

Runs before the service loader registers anything — a good place for setup that
services may depend on.

```php
add_action('detailking/theme/before_boot', function () {
    // e.g. define constants, load a project config, etc.
});
```

### `detailking/theme/booted`

Runs once every service has registered. Receives the list of booted service
class names.

```php
add_action('detailking/theme/booted', function (array $services) {
    // All theme services are now live; safe to wire project-specific glue.
    error_log('StackPress booted ' . count($services) . ' services.');
});
```

---

## WordPress actions the theme hooks into

Reference of the core action hooks the bundled services attach to (and at what
point each runs), so you can predict ordering or hook the same points yourself.

| Core action | Service · callback | Purpose |
|-------------|--------------------|---------|
| `after_setup_theme` | `functions.php` → `Application::boot()` | Boot the framework |
| `init` | `ThemeService::addThemeSupport` | `add_theme_support()` declarations |
| `init` | `Content\PostTypeService::registerAll` | Register CPTs & taxonomies |
| `wp_enqueue_scripts` | `AssetsService::enqueueAssets` | Enqueue front-end CSS/JS (incl. Bootstrap) |
| `wp_enqueue_scripts` | `DebloaterService::cleanFrontendAssets` (prio 100) | Dequeue bloat styles/scripts |
| `wp_enqueue_scripts` | `DebloaterService::removeJquery` (prio 100) | Deregister front-end jQuery |
| `admin_enqueue_scripts` | `AdminCustomizations::enqueueAdminAssets` | Enqueue admin CSS/JS |
| `admin_enqueue_scripts` | `DebloaterService::cleanAdminAssets` (prio 100) | Trim admin assets |
| `send_headers` | `SecurityService::sendSecurityHeaders` | Emit security headers |
| `template_redirect` | `SecurityService::blockAuthorEnumeration` | Block `?author=N` |
| `template_redirect` | `RedirectService::handleRedirects` | Apply configured redirects |
| `admin_bar_menu` | `Admin\ShowCurrentTemplate::showTemplatePath` (prio 100) | Show current template (admins) |
| `admin_bar_menu` | `DebloaterService::cleanAdminBar` (prio 999) | Remove admin-bar nodes |
| `wp_dashboard_setup` | `DebloaterService::removeDashboardWidgets` | Remove dashboard widgets |
| `admin_menu` | `DebloaterService::removeCommentsMenu` (prio 999) | Hide the Comments menu |
| `admin_head` | `DebloaterService::removeContextualHelp` | Remove contextual help tabs |
| `wp_before_admin_bar_render` | `DebloaterService::removeAdminBarComments` | Remove admin-bar comments |
| `delete_attachment` | `Media\MediaConverter::deleteConvertedImages` | Clean up converted files |

> Comment-related callbacks (`comments_open`, `pings_open`, etc.) are registered
> conditionally and are filters, not actions — see [filter.md](filter.md) and
> `DebloaterService` for the full list.
