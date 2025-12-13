# Change: Use compact profile template for inline roster items

## Why
The current inline roster view is functional but looks like a generic list. We want each member to render with a richer, more user-friendly “compact profile” layout inspired by `eldrìlas-profile-compact.html`.

## What Changes
- Update the **inline view** item markup to follow the compact profile header structure:
  - avatar (with placeholder fallback),
  - character name,
  - realm,
  - Mythic+ score,
  - Raider.IO profile CTA button only (row itself is not clickable).
- Add optional **inline-only** “best runs” decorations when available from the character profile:
  - compact “dungeon pills” (e.g., top 4 best runs)
  - expandable details section (best runs list/grid)
- Add scoped CSS (under `.gmpr`) derived from the template to style the inline roster items.
- Keep **cards view unchanged**.

## Constraints / Assumptions
- This change may enrich the per-character data model using `characters/profile` fields such as `mythic_plus_best_runs` and spec/class/faction metadata.
- No external font loading (e.g., Google Fonts) is introduced by default.
- The compact inline layout uses a **dark theme** with an accent color aligned to the site palette (accent: `#DCA54A`).

## Impact
- **Affected specs**:
  - `gmpr-roster-ui` (MODIFIED: inline view item layout + best runs decorations)
  - `guild-roster-shortcode` (MODIFIED: normalized member model may include best runs + spec/class/faction fields)
- **Affected code (apply stage)**:
  - `includes/class-gmpr-renderer.php` (inline view markup)
  - `includes/class-gmpr-raiderio-client.php` (character profile fields)
  - `includes/class-gmpr-plugin.php` / `includes/class-gmpr-async-refresh.php` (hydrate + cache best runs per character)
  - `assets/gmpr.css` (new scoped styles for the compact inline item)

## Open Questions
- (Resolved) The inline row is **not** clickable; only a “Profile” CTA is used.
- (Resolved) The inline compact theme is **dark** with accent `#DCA54A`.


