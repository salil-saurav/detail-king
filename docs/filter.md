# Filters

Extension points exposed by StackPress via `apply_filters()`. Add your callbacks
from `functions.php` (or any file loaded before the relevant hook fires).

| Filter | Default | Fired by |
|--------|---------|----------|
| `detailking/theme/post_types` | `[]` | `Content\PostTypeService` |
| `detailking/theme/redirects` | `[]` | `RedirectService` |
| `detailking/theme/debloater/config` | config array | `DebloaterService` |
| `detailking/theme/security/headers` | header array | `SecurityService` |
| `detailking/theme/security/block_author_enumeration` | `true` | `SecurityService` |
| `detailking/theme/security/restrict_rest_users` | `true` | `SecurityService` |
| `detailking/theme/media/format` | `'auto'` | `Media\MediaConverter` |
| `detailking/theme/admin/footer_text` | linked site name | `Admin\AdminCustomizations` |

---

## `detailking/theme/post_types`

Register custom post types and taxonomies. Returns an array keyed by post-type
slug; labels are generated automatically and `args` is merged over sensible
defaults. Re-save **Settings → Permalinks** once after adding a type.

```php
add_filter('detailking/theme/post_types', function (array $types): array {
    $types['testimonial'] = [
        'singular'   => 'Testimonial',
        'plural'     => 'Testimonials',
        'args'       => [
            'menu_icon' => 'dashicons-testimonial',
            'supports'  => ['title', 'editor', 'thumbnail', 'excerpt'],
        ],
        'taxonomies' => [
            'testimonial_cat' => ['singular' => 'Category', 'plural' => 'Categories'],
        ],
    ];
    return $types;
});
```

---

## `detailking/theme/redirects`

Map of request paths to redirect targets (301). Matched against the request
path, trailing slashes ignored.

```php
add_filter('detailking/theme/redirects', function (array $map): array {
    $map['/old-page']  = '/new-page';
    $map['/promo']     = 'https://example.com/landing';
    return $map;
});
```

---

## `detailking/theme/debloater/config`

Tune what `DebloaterService` removes. The value is a nested config array with
`admin_bar_nodes`, `dashboard_widgets`, `frontend_styles`, `frontend_scripts`,
`head_cleanup`, and a `features` map.

```php
add_filter('detailking/theme/debloater/config', function (array $config): array {
    // Keep jQuery on the front end.
    $config['features']['disable_jquery'] = false;

    // Keep comments enabled.
    $config['features']['disable_comments'] = false;

    // Stop stripping a specific core style.
    $config['frontend_styles'] = array_diff($config['frontend_styles'], ['classic-theme-styles']);

    return $config;
});
```

Available `features` flags (all default `true`): `disable_emojis`,
`disable_comments`, `disable_xmlrpc`, `disable_global_styles`, `disable_jquery`.

---

## `detailking/theme/security/headers`

Associative array of security response headers. Set a value to `''` to omit it,
or add your own.

```php
add_filter('detailking/theme/security/headers', function (array $headers): array {
    $headers['Permissions-Policy'] = 'geolocation=(), camera=()';
    $headers['X-Frame-Options']    = 'DENY';   // override the SAMEORIGIN default
    unset($headers['X-XSS-Protection']);        // drop a header entirely
    return $headers;
});
```

---

## `detailking/theme/security/block_author_enumeration`

Return `false` to allow the `?author=N` query (e.g. if you rely on author
archives by ID).

```php
add_filter('detailking/theme/security/block_author_enumeration', '__return_false');
```

---

## `detailking/theme/security/restrict_rest_users`

Return `false` to expose the `/wp/v2/users` REST endpoints to anonymous
requests again.

```php
add_filter('detailking/theme/security/restrict_rest_users', '__return_false');
```

---

## `detailking/theme/media/format`

Force the output format used by `MediaConverter`. Defaults to `'auto'` (AVIF
when the server supports it, otherwise WebP). Return `'webp'` to always use
WebP.

```php
add_filter('detailking/theme/media/format', fn() => 'webp');
```

---

## `detailking/theme/admin/footer_text`

Replace the wp-admin footer text.

```php
add_filter('detailking/theme/admin/footer_text', function (string $text): string {
    return 'Maintained by Acme Co. — support@acme.test';
});
```
