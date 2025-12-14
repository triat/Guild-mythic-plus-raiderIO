## 1. PHP Renderer Updates
- [x] 1.1 Remove view toggle buttons from `render_guild_table()` in `class-gmpr-renderer.php`
- [x] 1.2 Remove cards view HTML generation (entire cards foreach loop)
- [x] 1.3 Replace `<details>` element with expandable div structure matching reference
- [x] 1.4 Add expand icon (chevron SVG) to header row
- [x] 1.5 Remove view toggle from `render_loading()` method
- [x] 1.6 Update data attributes (remove `data-gmpr-view`)

## 2. CSS Updates
- [x] 2.1 Remove `.gmpr-view-toggle` and `.gmpr-toggle-btn` styles
- [x] 2.2 Remove all `.gmpr-cards`, `.gmpr-card-*` styles
- [x] 2.3 Remove `.gmpr-view-cards` and view switching CSS
- [x] 2.4 Add `.gmpr-profile-card.expanded` state styles
- [x] 2.5 Add `max-height` transition for `.gmpr-expandable-content` (0 → 600px)
- [x] 2.6 Add expand icon styles with rotation animation
- [x] 2.7 Verify mobile responsive styles remain intact

## 3. JavaScript Updates
- [x] 3.1 Remove `STORAGE_KEY` and localStorage functions (`getStoredView`, `setStoredView`)
- [x] 3.2 Remove `setView()` function
- [x] 3.3 Remove view toggle button click handlers
- [x] 3.4 Add click handler for `.gmpr-profile-header` to toggle `.expanded` class
- [x] 3.5 Add `stopPropagation` on `.gmpr-profile-link` click
- [x] 3.6 Keep avatar fallback and async refresh logic unchanged

## 4. Sync and Validation
- [x] 4.1 Copy updated CSS to `dist/staging/guild-mythic-plus-raiderio/assets/gmpr.css`
- [x] 4.2 Copy updated JS to `dist/staging/guild-mythic-plus-raiderio/assets/gmpr.js`
- [x] 4.3 Copy updated PHP to `dist/staging/guild-mythic-plus-raiderio/includes/class-gmpr-renderer.php`
- [x] 4.4 Test expand/collapse animation in browser
- [x] 4.5 Test mobile responsiveness (viewport widths: 320px, 640px, 768px, 1000px)
- [x] 4.6 Test with JS disabled (expandable should be visible by default)
