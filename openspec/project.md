# Project Context

## Purpose
This repository builds a **WordPress plugin** that displays World of Warcraft guild members along with their **Raider.IO** data (notably the Mythic+ score).

- **Primary goal**: provide a simple front-end display of a guild roster (table/grid), embeddable in a WordPress page (e.g. via shortcode).
- **Secondary goals**:
  - Reduce external calls using WordPress-side caching.
  - Be resilient to failures (API down, guild not found, latency).
  - Follow WordPress best practices (security, escaping, performance).
  - Optionally support a **minimum Raider.IO score filter** to limit displayed members.

> Note: this repo started as documentation-first; it now contains an initial working MVP.

## Tech Stack
- **Platform**: WordPress (plugin)
- **Primary language**: PHP (WordPress Core APIs)
- **Frontend (optional)**: HTML/CSS + vanilla JavaScript (for client-side sorting/search)
- **HTTP**: WordPress HTTP API (`wp_remote_get`, `wp_remote_retrieve_body`, etc.)
- **Cache**: Transients WordPress (ex: `set_transient`, `get_transient`)
- **License**: GPLv3 (see `LICENSE`)
- **External dependency**: Raider.IO public API (see “External Dependencies”)
- **Secret**: Raider.IO API key (stored server-side via WordPress options; never exposed to the front-end)

## Project Conventions

### Code Style
- **PHP / WordPress**:
  - Follow WordPress conventions (WordPress Coding Standards) as much as possible.
  - Prefix everything (functions, options, script/style handles) to avoid collisions, e.g. `gmpr_...`.
  - Always escape output (`esc_html`, `esc_attr`, `esc_url`) and sanitize input (`sanitize_text_field`, `sanitize_key`, etc.).
  - Never trust user parameters; validate and normalize.
- **Naming**:
  - WP options: `gmpr_settings`, `gmpr_region`, etc. (unique prefix).
  - Transients: `gmpr_raiderio_guild_<hash>` (include region/realm/guild).
- **Identifier normalization**:
  - `region`: accepted values: `eu`, `us`, `kr`, `tw`, `cn`.
  - `realm`: accept slug or title; normalize internally (be careful with accents depending on Raider.IO expectations).
  - `guild`: case-insensitive while preserving accents; normalize for cache keys with UTF-8 lowercasing (e.g. `mb_strtolower(..., 'UTF-8')`) + trimming.
- **Internationalization (recommended)**:
  - Use WordPress i18n (`__`, `_e`, plugin text domain) if the plugin is meant to be public.

### Architecture Patterns
- **Class-first WordPress plugin**:
  - One main plugin file to bootstrap (hooks `init`, `admin_menu`, `wp_enqueue_scripts`).
  - Split logic into classes/files (e.g. `RaiderIoClient`, `Renderer`, `Settings`).
- **Rendering**:
  - Display via shortcode: `[gmpr_guild]`.
  - Single globally configured guild (admin): region/realm/guild + API key + display options.
  - Optional view toggle: table ↔ cards, with responsive/mobile-friendly rendering.
- **Data**:
  - Fetch Raider.IO → normalize → cache → render HTML.
  - Avoid repeated calls: cache per guild + TTL.
- **Failure tolerance**:
  - If the API is down: show a clean message + fall back to existing cache when possible.
  - “Stale cache” is acceptable: if expired but available, render it with a warning rather than failing hard.
- **Sort / search / pagination**:
  - WordPress best practice: server rendering (SEO/accessibility) + progressive JS enhancement.
  - Pagination always available (large rosters).
  - Sort/search: accessible controls; JS can improve responsiveness, with server fallback.

### Testing Strategy
- **Current state**: no automated testing setup yet.
- **Recommended once the code grows**:
  - Unit tests (PHPUnit) for mapping/normalization of Raider.IO responses.
  - Integration tests with the WordPress test suite (optional).
  - At minimum: manual validation scripts + regression checklist (shortcode, cache, settings).

### Git Workflow
- **Branches**: `main` + short feature/fix branches (`feat/...`, `fix/...`).
- **Commits**: Conventional Commits recommended (`feat:`, `fix:`, `docs:`, etc.).
- **PRs**:
  - Small, descriptive PRs.
  - Link OpenSpec changes when relevant (`openspec/changes/<change-id>/`).

## Domain Context
This project manipulates the following concepts:
- **Guild**: typically identified by `region`, `realm`, `guild name` (per Raider.IO conventions).
- **Raider.IO**: service providing public data (scores, roster, etc.) via HTTP endpoints.
- **Mythic+**:
  - Score/ratings used to rank players.
  - Rendering includes at minimum: character name, Mythic+ score, and Raider.IO profile link.
- **Raid**:
  - Display raid progression (exact format depends on the chosen endpoint).
- **Item level (ilvl)**:
  - Display ilvl if available from the chosen Raider.IO data.

Assumption: the primary goal is to list members and show at least one score field (Mythic+). Exact fields are confirmed when finalizing endpoints.

## Important Constraints
- **Performance**: do not call Raider.IO on every page view; use cache/TTL.
- **Availability**: handle timeouts, HTTP errors, partial data.
- **WordPress security**:
  - Always sanitize/escape.
  - Nonces + capabilities for any admin action.
  - Never expose the **API key** (not in HTML, not in JS, not in public endpoints).
- **Compatibility**:
  - Minimum targets: latest major versions.
  - WordPress: minimum **6.9+** (aligned with current project environment).
  - PHP: minimum **8.x** (to be confirmed if needed, e.g. 8.2+).
- **License**:
  - GPLv3; avoid incompatible dependencies.

## External Dependencies
- **WordPress Core APIs** (HTTP API, Settings API, Shortcodes, Transients, etc.)
- **Raider.IO API**:
  - Used to retrieve guild/player information.
  - Constraints: latency, availability, potential rate limiting (plan backoff + cache).
  - Auth: requires an **API key** (stored server-side).

## Open Questions (à clarifier)
- **Exact PHP minimum**: which 8.x version (e.g. 8.2+ vs 8.3+)?
- **Raid progression**: for the latest raid, show the highest difficulty achieved (format depends on Raider.IO fields).
- **Minimum score filter**: keep it global (admin-only) for simplicity.
