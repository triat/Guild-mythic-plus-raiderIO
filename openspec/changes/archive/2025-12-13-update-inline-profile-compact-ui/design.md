## Context
We have a reference UI (`eldrìlas-profile-compact.html`) that contains both a compact header layout and an expanded section with dungeon/run details. The plugin roster inline view currently renders simple list rows.

## Goals / Non-Goals
- **Goals**:
  - Reuse the **compact header** pattern for each inline roster member.
  - Use character profile data to render **best runs** decorations (pills + optional expandable section) in inline view.
  - Keep styles scoped to the plugin container to avoid impacting themes.
  - Preserve accessibility (focusable link/button; meaningful alt text; no inline JS handlers).
  - Keep cards view unchanged.
- **Non-Goals**:
  - Loading external fonts (Google Fonts).
  - Making the whole row clickable (CTA only).

## Decisions
- **CSS scoping**: All new rules are prefixed under `.gmpr` and new class names are `gmpr-inline-profile-*` (no global `:root`/`body` selectors).
- **Theme**: Use a dark compact theme (inspired by the reference) scoped to the roster component, with an accent color aligned to the site palette: `#DCA54A`.
- **Theme compatibility**: Keep styles scoped and avoid global page background changes (no `body` rules).
- **Data mapping**:
  - `character-name` → member `name`
  - `realm-info` → member `realm` + region (if available)
  - `score-value` → member `mplus_score`
  - avatar image → member `avatar_url` with placeholder fallback
  - profile link → member `profile_url`
- **Best runs mapping** (from `characters/profile?fields=mythic_plus_best_runs`):
  - pills: top N runs → `short_name` + `mythic_level`
  - details: each run may include `background_image_url`, `dungeon`, `score`, `mythic_level`, `url`
- **Interaction**: The row is not clickable; provide an explicit “Profile” CTA button/link only.
  - Expand/collapse is implemented with semantic HTML where possible (e.g., `<details>`), or unobtrusive JS without inline handlers.

## Data / caching impact
- Extend per-character cache payload to include best runs + spec/class/faction fields (server-side only).
- Ensure the async refresh job hydrates these fields too, so cold starts converge without blocking page render.

## Future Extension (not included)
If we later want the expandable “Best Mythic+ Runs” and dungeon pills, we will need to extend the data model and caching to include best runs per character.


