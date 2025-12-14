# Change: Remove dual view toggle, keep only inline expandable view

## Why
The current dual-view system (inline/cards toggle) adds unnecessary complexity. Users prefer a single cohesive experience with the inline expandable profile layout. The inline view should match EXACTLY the design and collapse animation from `eldrìlas-profile-compact.html` while remaining mobile-compatible.

## What Changes
- **BREAKING**: Remove the view toggle (inline/cards buttons)
- **BREAKING**: Remove the cards view entirely (HTML, CSS, JS)
- Update inline view to use CSS-animated expand/collapse (matching `eldrìlas-profile-compact.html`)
- Replace `<details>` element with a click-to-expand header pattern using CSS `max-height` transitions
- Remove localStorage view preference persistence (no longer needed)
- Simplify JS to only handle avatar fallbacks and async refresh
- Ensure mobile responsiveness for the single inline view

## Impact
- Affected specs: `gmpr-roster-ui`
- Affected code:
  - `includes/class-gmpr-renderer.php` - Remove cards rendering, update expand/collapse markup
  - `assets/gmpr.css` - Remove cards styles, add expand animation styles
  - `assets/gmpr.js` - Remove toggle logic, add expand/collapse click handler
  - `dist/staging/` - Sync updated files
