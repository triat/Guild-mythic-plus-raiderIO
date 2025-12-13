# Change: Add a frontend inline/cards view toggle with persistent preference

## Why
The current roster output is functional but not very UI-friendly. We want a nicer presentation option (cards) while keeping the existing inline/table view, and let users switch between them on the front-end.

## What Changes
- Add a **cards view** for guild members in addition to the **inline view**.
- Add an **always-visible view toggle** (inline ↔ cards) on the front-end.
- Persist the selected view (per browser) using **localStorage** so the choice survives reloads.
- Keep a progressive enhancement approach: default server-rendered view remains usable without JavaScript.

## Non-Goals
- No new data fields fetched from Raider.IO.
- No redesign of the admin settings page.
- No new public endpoints.

## Impact
- **Affected specs**:
  - `gmpr-roster-ui` (new capability)
  - `guild-roster-shortcode` (MODIFIED: rendering provides both views + toggle)
- **Affected code** (apply stage):
  - `includes/class-gmpr-renderer.php` (render both view containers + toggle controls)
  - `assets/gmpr.css` (cards layout + table styles refinements)
  - `assets/gmpr.js` (toggle + persistence)


