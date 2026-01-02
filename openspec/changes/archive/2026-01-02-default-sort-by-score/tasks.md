# Tasks for default-sort-by-score

## Implementation Tasks

### 1. Update default filterState.sortBy in gmpr.js
- [x] Change `filterState.sortBy` initialization from `'none'` to `'score-desc'`
- [x] Located in the `initFilters()` function around line 103
- **Validation**: Verify filterState object has sortBy: 'score-desc'
- **Dependencies**: None

### 2. Update clearFilters to reset to score-desc
- [x] Change `filterState.sortBy = 'none'` to `filterState.sortBy = 'score-desc'` in clearFilters()
- [x] Change `sortSelect.value = 'none'` to `sortSelect.value = 'score-desc'`
- [x] Located around line 203
- **Validation**: Verify Clear button resets to score-desc sort
- **Dependencies**: None
- **Can run in parallel with**: Task 1

### 3. Apply initial sort on page load
- [x] Add call to `applyFilters()` at the end of initFilters() to apply the default sort
- [x] This ensures the initial display is sorted by score
- [x] Located after the results count initialization (after line 244)
- **Validation**: Page loads with roster sorted by score descending
- **Dependencies**: Task 1

### 4. Update sort dropdown default selection in PHP
- [x] Update the `render_guild_table()` function in class-gmpr-renderer.php
- [x] Find the sort dropdown rendering (around line 110)
- [x] Change the "Score (High to Low)" option to have `selected="selected"` attribute
- **Validation**: Dropdown shows correct initial selection in HTML
- **Dependencies**: None
- **Can run in parallel with**: Tasks 1, 2, 3

### 5. Test initial page load
- [x] Load roster page and verify members are sorted by score (high to low)
- [x] Verify sort dropdown shows "Score (High to Low)" selected
- **Validation**: Initial display is sorted correctly
- **Dependencies**: Tasks 1, 3, 4

### 6. Test Clear button
- [x] Apply various filters and sort options
- [x] Click Clear button
- [x] Verify roster returns to score-desc sort (not unsorted)
- **Validation**: Clear button behavior is correct
- **Dependencies**: Task 2, 5

### 7. Test async refresh
- [x] Load roster with default sort active
- [x] Trigger async refresh
- [x] Verify roster re-sorts to score-desc after refresh
- **Validation**: Async refresh maintains default sort
- **Dependencies**: Tasks 1, 3, 5

### 8. Test user sort changes
- [x] Verify users can still change sort to other options
- [x] Verify changing to "Default" or other sorts works correctly
- [x] Ensure no regressions in existing sort functionality
- **Validation**: All sort options still work
- **Dependencies**: Tasks 5, 6, 7

## Notes
- All changes are minimal - just changing default values
- No new functionality added, just changing what "default" means
- Score-desc is already implemented and working, we're just making it the default
- The "none" sort option might still exist in the dropdown, but score-desc is the new default
