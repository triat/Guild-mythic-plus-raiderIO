# Guild-mythic-plus-raiderIO
WordPress plugin that displays World of Warcraft guild members with their **Raider.IO** (Mythic+) score using a shortcode.

<img width="1233" height="980" alt="image" src="https://github.com/user-attachments/assets/dd60aa94-6cd6-42ce-8b6b-31efc2c8c51c" />

## Installation
- Copy this repository (or its contents) into `wp-content/plugins/guild-mythic-plus-raiderio/`
- Activate **Guild Mythic+ Raider.IO** in the WordPress admin

## Configuration

### Admin Settings (Recommended)
Navigate to **Settings → GMPR** in the WordPress admin dashboard to configure:

- **Raider.IO API Key**: Your API key (stored securely, never shown in plaintext)
- **Region**: Default region (EU, US, KR, TW, CN)
- **Realm**: Default realm slug or name
- **Guild Name**: Default guild name
- **Cache TTL**: Cache time-to-live in seconds (min: 60, max: 6 hours, default: 15 minutes)
- **Member Limit**: Maximum number of members to display (0-500, where 0 disables the limit, default: 20)

**Note**: Shortcode attributes will override these admin defaults for individual pages.

### Alternative Configuration Methods

#### Option 1 — Constants in `wp-config.php`
For security-focused setups, you can define constants instead:

```php
define('GMPR_RAIDERIO_API_KEY', 'your_api_key');

// Optional defaults if you don't want to use the admin UI
define('GMPR_REGION', 'eu');   // eu|us|kr|tw|cn
define('GMPR_REALM', 'dalaran'); // realm slug
define('GMPR_GUILD', 'Guild Name'); // guild name
```

Constants take precedence over admin settings.

#### Option 2 — WordPress filter (for secret managers)
You can inject the key via the `gmpr_raiderio_api_key` filter:

```php
add_filter('gmpr_raiderio_api_key', function ($key) {
  return 'your_api_key';
});
```

This filter takes precedence over both constants and admin settings.

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

### Filtering and Sorting
The roster includes a filter toolbar that allows you to search, filter, and sort guild members:

#### Available Filters
- **Role Filter**: Filter characters by their active spec role
  - Options: All Roles, Tank, Healer, DPS
  - Characters are filtered based on their current active specialization role from Raider.IO

- **Name Search**: Search for characters by name (case-insensitive, partial matching)
  - Example: Typing "eld" will match "Eldrìlas"

- **Score Range**: Filter by Mythic+ score
  - **Min Score**: Show only characters with score ≥ this value
  - **Max Score**: Show only characters with score ≤ this value
  - Can use either limit independently or combine both for a range

#### Sorting Options
- **None**: Default order (as returned by the API)
- **Name A-Z**: Alphabetical order by character name (ascending)
- **Name Z-A**: Reverse alphabetical order (descending)
- **Score: High to Low**: Sort by Mythic+ score (descending)
- **Score: Low to High**: Sort by Mythic+ score (ascending)

#### Features
- **Real-time Filtering**: Results update instantly as you type or change filters
- **Results Count**: Shows "Showing X of Y characters" to track filtered results
- **Empty State**: Displays a message when no characters match your filters
- **Clear Button**: Resets all filters and sorting to default state
- **Responsive Design**: Filter toolbar adapts to mobile screens (stacks vertically)
- **Persistent During Refresh**: Filters remain active during async data updates

All filtering and sorting happens client-side (no server requests), ensuring fast and responsive user experience.

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

