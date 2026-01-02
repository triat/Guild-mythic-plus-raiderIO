# Tasks for debounce-roster-filters

## Implementation Tasks

### 1. Add debounce helper function to gmpr.js
- [x] Add a reusable `debounce()` utility function at the top of the IIFE
- [x] Function should accept a callback and timeout parameter
- [x] Return a debounced version that cancels previous timers
- **Validation**: Function returns a callable that delays execution
- **Dependencies**: None

### 2. Define configurable debounce timeout constant
- [x] Add `DEFAULT_DEBOUNCE_MS` constant (300ms) alongside existing constants
- [x] Position near other configuration constants (DEFAULT_POLL_INTERVAL, etc.)
- **Validation**: Constant is accessible in initFilters function
- **Dependencies**: None
- **Can run in parallel with**: Task 1

### 3. Wrap name input handler with debounce
- [x] Modify the `nameInput.addEventListener('input', ...)` handler in initFilters()
- [x] Wrap the handler callback with the debounce utility
- [x] Use DEFAULT_DEBOUNCE_MS as the timeout
- **Validation**: Manual test - typing in name field updates results after 300ms pause
- **Dependencies**: Tasks 1, 2

### 4. Wrap score min input handler with debounce
- [x] Modify the `scoreMinInput.addEventListener('input', ...)` handler in initFilters()
- [x] Wrap the handler callback with the debounce utility
- [x] Use DEFAULT_DEBOUNCE_MS as the timeout
- **Validation**: Manual test - typing in min score field updates results after 300ms pause
- **Dependencies**: Tasks 1, 2
- **Can run in parallel with**: Task 3

### 5. Wrap score max input handler with debounce
- [x] Modify the `scoreMaxInput.addEventListener('input', ...)` handler in initFilters()
- [x] Wrap the handler callback with the debounce utility
- [x] Use DEFAULT_DEBOUNCE_MS as the timeout
- **Validation**: Manual test - typing in max score field updates results after 300ms pause
- **Dependencies**: Tasks 1, 2
- **Can run in parallel with**: Tasks 3, 4

### 6. Verify dropdown filters remain immediate
- [x] Manually test role dropdown to ensure no delay in filtering
- [x] Manually test sort dropdown to ensure immediate re-ordering
- [x] Confirm these handlers are NOT wrapped with debounce
- **Validation**: Dropdowns respond instantly on change
- **Dependencies**: Tasks 3, 4, 5

### 7. Test with large roster
- [x] Test with 50+ member roster to verify improved typing experience
- [x] Verify filter state updates correctly after debounce period
- [x] Test rapid typing and backspacing to ensure timers cancel properly
- **Validation**: Smooth typing experience, filters apply correctly
- **Dependencies**: Tasks 3, 4, 5, 6

### 8. Test async refresh compatibility
- [x] Apply filters with debounced inputs
- [x] Trigger async refresh while debounce timer is active
- [x] Verify new DOM reinitializes debounce correctly
- [x] Confirm no JavaScript errors or orphaned timers
- **Validation**: Filters work correctly after async refresh
- **Dependencies**: Task 7

## Notes
- All tasks modify only `assets/gmpr.js`
- No changes to PHP, HTML rendering, or CSS required
- No external dependencies needed
- Debounce timeout can be adjusted in future if 300ms proves too long/short
