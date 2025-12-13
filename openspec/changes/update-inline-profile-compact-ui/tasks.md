## 1. Specs
- [x] 1.1 Modify `gmpr-roster-ui` to require a compact inline roster item layout based on the reference template (including best runs decorations when available).
- [x] 1.2 Modify `guild-roster-shortcode` to allow exposing best runs + class/spec/faction fields in the normalized member model (when available from character profile).

## 2. Implementation (apply stage)
- [x] 2.1 Update `GMPR_Renderer` inline view markup to render “compact profile” rows.
- [x] 2.2 Add scoped CSS rules to `assets/gmpr.css` for the compact inline rows (dark theme + accent `#DCA54A`; no global `body` styles).
- [x] 2.3 Update Raider.IO character profile calls to request best runs (`fields=...mythic_plus_best_runs`) and map them into the cached per-character payload.
- [x] 2.4 Extend async refresh job to hydrate and cache best runs too.
- [x] 2.3 Ensure avatar placeholder fallback remains stable and accessible.
- [x] 2.4 Ensure the view toggle + cards view remain unchanged.

## 3. Validation
- [x] 3.1 Manual checks:
  - [x] Inline view renders compact profile items on desktop and mobile.
  - [x] Inline view displays dungeon pills and expandable best-runs section when best runs are available.
  - [x] Cards view remains unchanged.
  - [x] Avatar failures use placeholder and layout stays stable.
- [x] 3.2 `openspec validate update-inline-profile-compact-ui --strict`


