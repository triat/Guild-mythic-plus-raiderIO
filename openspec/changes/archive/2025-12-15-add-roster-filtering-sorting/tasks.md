# Implementation Tasks: Roster Filtering and Sorting

## Implementation Notes

### Changes Made During Development
1. **Role Value Correction**: Changed healer filter value from "healer" to "healing" to match Raider.IO API response
2. **Removed Unknown Role**: Eliminated "unknown" role option - all characters should have a role from the API
3. **Clear Button Alignment**: Wrapped clear button in `.gmpr-filter-group` with invisible label (`&nbsp;`) for proper alignment
4. **Clear Button Fix**: Fixed clear button to properly restore original card DOM order when clearing sort (stores `originalOrder` array on init)

### Key Implementation Details
- Role values: `'tank'`, `'healing'`, `'dps'` (lowercase)
- Empty role defaults to empty string `''` (not 'unknown')
- Filter toolbar uses dark theme matching existing design
- Client-side filtering via data attributes (no server requests)
- Responsive design: filters stack vertically on mobile
- Results count updates dynamically: "Showing X of Y characters"
- Empty state message when no results match filters

### Files Modified
- `includes/class-gmpr-raiderio-client.php` - API request (already had `active_spec_role`)
- `includes/class-gmpr-plugin.php` - Extract and cache role data
- `includes/class-gmpr-async-refresh.php` - Extract and cache role data (async path)
- `includes/class-gmpr-renderer.php` - Filter toolbar HTML + data attributes
- `assets/gmpr.css` - Filter styling (lines 460-571)
- `assets/gmpr.js` - Filter/sort logic (lines 67-224)

## Phase 1: Data Layer (Backend) ✅

### 1. Update Raider.IO API Request ✅
- [x] Update `class-gmpr-raiderio-client.php::fetch_character_profile()`
  - Add `active_spec_role` to the `fields` parameter
  - Current: `mythic_plus_scores_by_season:current,mythic_plus_best_runs`
  - Updated: `mythic_plus_scores_by_season:current,mythic_plus_best_runs,active_spec_role`
  - **Note**: This field was already present in the API request (line 148)

### 2. Extract Role from API Response ✅
- [x] Update `class-gmpr-plugin.php::extract_character_meta()`
  - Add extraction of `active_spec_role` field
  - Normalize role to lowercase ('tank', 'healing', 'dps')
  - Default to empty string if missing (no 'unknown' state)
  - **Implemented**: Lines 367-369
- [x] Update `class-gmpr-async-refresh.php::extract_meta()`
  - Mirror the same role extraction logic
  - Ensure cache includes role data
  - **Implemented**: Same pattern as plugin.php

### 3. Store Role in Member Data ✅
- [x] Update `class-gmpr-plugin.php::hydrate_member_scores_and_avatars()`
  - Include `active_spec_role` in cached character data
  - Add to `$cache_value` array
  - **Implemented**: Lines 245, added 'active_spec_role' to cache array
- [x] Update `class-gmpr-async-refresh.php::hydrate_member_scores_and_avatars()`
  - Mirror the same cache storage logic
  - **Implemented**: Same pattern as plugin.php

### 4. Add Data Attributes to Rendered HTML ✅
- [x] Update `class-gmpr-renderer.php::render_guild_table()`
  - Extract role from member data
  - Add `data-role` attribute to `.gmpr-profile-card`
  - Add `data-name` attribute (lowercase name for searching)
  - Add `data-score` attribute (numeric score for filtering)
  - Example: `<div class="gmpr-profile-card" data-role="dps" data-name="eldrìlas" data-score="3555">`
  - **Implemented**: Lines 165-174

## Phase 2: UI Layer (Frontend) ✅

### 5. Add Filter Toolbar HTML ✅
- [x] Update `class-gmpr-renderer.php::render_guild_table()`
  - Add filter toolbar HTML before the roster list
  - Include:
    - Role dropdown (All, Tank, Healing, DPS) - **No 'Unknown' option per user request**
    - Name search input
    - Min/Max score number inputs
    - Sort dropdown (None, Name A-Z, Name Z-A, Score High-Low, Score Low-High)
    - Clear filters button (wrapped in filter-group with invisible label for alignment)
  - **Implemented**: Lines 80-127
  - Added results count display: `<div id="gmpr-results-count"></div>`
  - Added empty state message: `<div id="gmpr-filter-empty">` (shown when no results)

### 6. Style Filter Toolbar ✅
- [x] Update `assets/gmpr.css`
  - Add `.gmpr-filters` container styles
  - Add `.gmpr-filter-group` styles (label + input/select)
  - Make responsive (stack vertically on mobile)
  - Match existing dark theme design
  - Add clear button styles
  - Use `.gmpr.gmpr-wrap` prefix for specificity
  - **Implemented**: Lines 460-571
  - Added hover/focus states for inputs and clear button
  - Added `.gmpr-filter-empty` styles for no-results message

### 7. Implement Filter/Sort JavaScript ✅
- [x] Update `assets/gmpr.js`
  - Add `initFilters()` function to set up event listeners
  - Add `filterState` object to track current filters
  - Add `applyFilters()` function to show/hide cards
  - Add `applySorting()` function to re-order cards
  - Add `clearFilters()` function to reset all
  - Add `updateResultsCount()` to show "X of Y characters"
  - Call `initFilters()` from `initWrapper()`
  - **Implemented**: Lines 67-224
  - Added `originalOrder` array to store initial card positions (line 83)
  - Fixed clear button to restore original DOM order when sortBy is 'none' (lines 137-142)

### 8. Handle Async Data Updates ✅
- [x] Update `assets/gmpr.js::startAsyncRefresh()`
  - Re-apply filters after new HTML is injected
  - Preserve filter state during refresh
  - Call `initFilters()` on new wrapper
  - **Note**: Already handled by `initAll(document)` calls after HTML replacement (lines 388, 403)

## Phase 3: Testing & Polish ✅

### 9. Manual Testing
- [x] Test role filter with all options (All, Tank, Healing, DPS)
- [x] Test name search (case-insensitive, partial match)
- [x] Test score range filter (min only, max only, both)
- [x] Test sorting (all 5 options: None, Name A-Z, Name Z-A, Score High-Low, Score Low-High)
- [x] Test clear button resets all filters and restores original order
- [x] Test filters persist during async refresh
- [x] Test with 0 results (empty state message shows)
- [x] Test with missing role data (empty role shows with "All" filter only)
- [x] Test mobile layout (filter toolbar stacks properly)
- [x] Test theme CSS doesn't override filter styles

### 10. Edge Cases
- [x] Characters with score = 0
- [x] Characters with missing/empty role
- [x] Empty guild roster
- [x] Very large roster (100+ characters)
- [x] Rapid filter changes (currently no debounce)
- [x] Clicking expand while filters active
- [x] Clearing filters after multiple sort operations

### 11. Accessibility
- [x] All inputs have proper `<label>` elements
- [x] Keyboard navigation works
- [x] Screen reader announces filter results
- [x] Focus management (clear button returns focus)

## Phase 4: Documentation & Deployment

### 12. Update Documentation ✅
- [x] Add filter/sort usage to README.md
  - **Implemented**: Lines 49-80 in README.md
  - Documented all filter options (Role, Name Search, Score Range)
  - Documented all sorting options (None, Name A-Z/Z-A, Score High-Low/Low-High)
  - Listed all features (real-time filtering, results count, empty state, clear button, responsive design)
- [ ] Update screenshots if present
- [x] Document available filter options

### 13. Sync and Build ✅
- [x] Copy all updated files to `dist/staging/`
- [x] Rebuild plugin zip with `make zip`
- [x] Test installation on fresh WordPress

### 14. Archive OpenSpec Change
- [x] Run `openspec validate add-roster-filtering-sorting --strict`
- [x] Fix any validation errors
- [ ] Archive proposal with `openspec archive add-roster-filtering-sorting`

## Estimated Effort
- **Phase 1 (Backend)**: 1-2 hours
- **Phase 2 (Frontend)**: 2-3 hours
- **Phase 3 (Testing)**: 1-2 hours
- **Phase 4 (Docs)**: 30 minutes
- **Total**: ~5-8 hours

## Dependencies
- None (all changes are additive)

## Rollback Plan
If issues arise:
1. Remove filter toolbar HTML from renderer
2. Remove filter JavaScript from `gmpr.js`
3. Remove filter CSS from `gmpr.css`
4. Role data in character objects is harmless if unused
