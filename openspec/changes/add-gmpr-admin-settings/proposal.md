# Change: Add an admin settings page (“Settings → GMPR”) to configure the plugin

## Why
The current MVP requires configuring the plugin via constants/filters and shortcode attributes. An admin settings page simplifies installation and makes configuration accessible without code changes.

## What Changes
- Add a **Settings → GMPR** page (WordPress Settings) accessible to admins (`manage_options`).
- Store plugin configuration in WordPress options (e.g. `gmpr_settings`) with validation/sanitization.
- Define a **configuration precedence**:
  - shortcode attributes override admin settings,
  - admin settings provide defaults (e.g. region/realm/guild/ttl/limits),
  - optionally keep compatibility with constants/filters as a fallback.
- Provide sensible defaults (TTL, member_limit, etc.).

## Non-Goals
- No front-end UI redesign (table/cards).
- No new public endpoints.
- No multi-guild support (stay with a single global configuration).

## Impact
- **Affected specs**:
  - `gmpr-admin-settings` (new capability)
  - `guild-roster-shortcode` (MODIFIED: configuration sources and precedence)
- **Affected code** (apply stage):
  - Add a settings class + `admin_menu`/`admin_init` hooks
  - Adjust shortcode config resolution (atts > options > fallback)

## Open Questions
- API key handling in the UI: propose a “password” field, masked value, and allow leaving it empty to keep the existing key.


