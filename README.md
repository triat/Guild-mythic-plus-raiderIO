# Guild-mythic-plus-raiderIO
WordPress plugin that displays World of Warcraft guild members with their **Raider.IO** (Mythic+) score using a shortcode.

## Installation
- Copy this repository (or its contents) into `wp-content/plugins/guild-mythic-plus-raiderio/`
- Activate **Guild Mythic+ Raider.IO** in the WordPress admin

## Configuration (MVP without an admin UI)
The API key is **never** passed as a shortcode attribute.

### Option 1 — Constants in `wp-config.php` (recommended)
Add to `wp-config.php`:

```php
define('GMPR_RAIDERIO_API_KEY', 'your_api_key');

// Optional defaults if you don't want to pass them in the shortcode
define('GMPR_REGION', 'eu');   // eu|us|kr|tw|cn
define('GMPR_REALM', 'dalaran'); // realm slug
define('GMPR_GUILD', 'Guild Name'); // guild name
```

### Option 2 — WordPress filter (secret manager)
You can inject the key via the `gmpr_raiderio_api_key` filter:

```php
add_filter('gmpr_raiderio_api_key', function ($key) {
  return 'your_api_key';
});
```

## Usage
In a page / post:

```text
[gmpr_guild region="eu" realm="dalaran" guild="Guild Name"]
```

### Parameters
- **region**: `eu|us|kr|tw|cn`
- **realm**: realm slug
- **guild**: guild name
- **ttl** (optional): cache TTL in seconds (min 60, max 6h, default ~15min)
- **refresh** (optional): `1|true|yes` to **force a refresh** (bypass cache). **Admin-only** (security).

### UI
The roster includes an always-visible **Inline / Cards** toggle. The selected view is persisted in the browser via `localStorage` (key: `gmpr_roster_view`).

## Troubleshooting (view errors)
The shortcode intentionally shows a user-friendly error. To see details:

1) In `wp-config.php`, enable:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

2) Reload the page containing `[gmpr_guild ...]`

3) Check:
- **WordPress debug log**: `wp-content/debug.log`
- **Or** your server PHP logs (Apache/Nginx/PHP-FPM depending on hosting)

The plugin logs Raider.IO errors prefixed with: `[GMPR] Raider.IO: ...` (it never logs the API key).

### Raider.IO auth note
The plugin sends the key via the `Authorization: Bearer <key>` header (not in the query string).

