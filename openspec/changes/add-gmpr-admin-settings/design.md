## Context
The plugin is currently configurable via constants/filters and shortcode attributes. We want a “Settings → GMPR” admin page using the WordPress Settings API.

## Goals / Non-Goals
- **Goals**:
  - Let an admin configure: region/realm/guild + API key + defaults (TTL, member limit).
  - Keep the rule: **shortcode atts > admin config**.
  - Secure storage: sanitization + capabilities + nonces (handled by Settings API).
- **Non-Goals**:
  - Multi-guild or multiple profiles.
  - Complex UI (wizard).

## Decisions
- **Location**: WordPress submenu **Settings → GMPR** (`options-general.php?page=gmpr`).
- **Capability**: `manage_options`.
- **Storage**: a single option `gmpr_settings` (array) to reduce fragmentation.
  - Proposed keys: `api_key`, `region`, `realm`, `guild`, `ttl_seconds`, `member_limit`.
- **API key security**:
  - Password field, never shown in plaintext.
  - If the field is empty on save: keep the previously stored key.
- **Resolution precedence**:
  - Shortcode: valid atts → else `gmpr_settings` → (optional) constants/filters fallback for compatibility/migration.

## Alternatives considered
- One option per field (`gmpr_region`, `gmpr_realm`, …): rejected (more verbose to maintain).
- Encrypted storage in DB: rejected for MVP (complexity + key management).

## Risks / trade-offs
- An API key stored in a WP option depends on server/DB security. Mitigation: never expose it and restrict access to admins.

## Migration / compatibility
- Keep constants/filters compatibility as a fallback (eases transition).
- Eventually, document the admin UI as the recommended path.


