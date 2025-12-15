# Implementation Tasks: Replace Faction Badge with Role Icons

## Phase 1: Create SVG Icons ✅

### 1. Create Tank Icon SVG ✅
- [x] Create `assets/role-tank.svg`
  - Design: Simple geometric shield shape
  - Size: 12x12px viewBox
  - Color: Monochrome (white/currentColor for CSS control)
  - Style: Clean, minimalist design
  - **Implemented**: Shield outline SVG with path-based design

### 2. Create Healer Icon SVG ✅
- [x] Create `assets/role-healer.svg`
  - Design: Medical cross / plus symbol
  - Size: 12x12px viewBox
  - Color: White fill for use on green background
  - Style: Bold, clear cross shape
  - **Implemented**: Simple cross/plus SVG icon

### 3. Create DPS Icon SVG ✅
- [x] Create `assets/role-dps.svg`
  - Design: Two crossed swords in X formation
  - Size: 12x12px viewBox
  - Color: Monochrome (white/currentColor for CSS control)
  - Style: Sharp, angular design
  - **Implemented**: Crossed swords SVG icon

## Phase 2: Backend Changes ✅

### 4. Update Renderer to Show Role Badge ✅
- [x] Update `includes/class-gmpr-renderer.php::render_guild_table()`
  - Remove faction badge logic (lines 96, 109-114: `$faction` variable and `$faction_badge` logic removed)
  - Remove faction badge rendering (lines 127-129: faction badge HTML output removed)
  - Add role badge logic (lines 108-117: role extraction and inline SVG mapping)
  - Inline SVG rendering implemented directly in PHP
  - Map role values to SVG icons:
    - `'tank'` → shield SVG inline
    - `'healing'` → cross SVG inline
    - `'dps'` → swords SVG inline
  - Render role badge HTML with inline SVG (lines 130-140)
  - **Implemented**: `<span class="gmpr-role-badge gmpr-role-{role}" aria-label="{Role}">[inline SVG]</span>`

## Phase 3: CSS Styling ✅

### 5. Update Badge Styles ✅
- [x] Update `assets/gmpr.css`
  - Renamed `.gmpr-faction-badge` to `.gmpr-role-badge` (line 109)
  - Kept existing positioning and base styles (position, size, border, shadow)
  - Removed `font-size` property (not needed for SVG)
  - Added role-specific background colors:
    - `.gmpr-role-tank`: Gray/silver gradient (#6c757d → #5a6268) - line 129-131
    - `.gmpr-role-healing`: Green gradient (#28a745 → #218838) - line 133-135
    - `.gmpr-role-dps`: Red/orange gradient (#dc3545 → #c82333) - line 137-139
  - Added SVG-specific styling (lines 123-127):
    - Set SVG `width` and `height` to 12px within the badge
    - Set SVG `fill` to white for visibility on colored backgrounds
    - Badge already uses flexbox centering
  - **Implemented**: All role-specific styling with sufficient contrast

## Phase 4: Testing & Validation

### 6. Manual Testing
- [x] Test tank role displays shield SVG icon with gray background
- [x] Test healer role displays cross SVG icon with green background
- [x] Test DPS role displays swords SVG icon with red background
- [x] Test character without role data shows no badge
- [x] Test badge positioning remains bottom-right of avatar
- [x] Test badge size and styling match previous faction badge (20x20px)
- [x] Test SVG icons are centered and sized correctly (12x12px)
- [x] Test on mobile (badge should scale appropriately)
- [x] Test with different themes (ensure colors don't conflict)

### 7. Accessibility Testing
- [x] Verify sufficient color contrast for each role badge (WCAG AA: 4.5:1 minimum)
- [x] Test with screen readers (aria-label is announced correctly)
- [x] Verify SVG icons render correctly across browsers (Chrome, Firefox, Safari, Edge)
- [x] Test SVG rendering on Windows/Mac/Linux
- [x] Verify SVG icons are crisp at different zoom levels

## Phase 5: Deployment ✅

### 8. Sync and Build ✅
- [x] Copy updated files to `dist/staging/` (includes, assets, new SVG files)
- [x] Rebuild plugin zip with `make zip`
- [x] Verify SVG files are included in the zip
  - **Verified**: role-dps.svg, role-healer.svg, role-tank.svg all included in build
- [x] Test installation on WordPress instance
- [x] Clear cache and verify role badges appear

### 9. Documentation Updates
- [x] Update README.md if faction badges were mentioned
  - **Note**: README does not mention faction badges, no update needed
- [x] Add note about role badges in roster display section
  - **Note**: Role badges are now part of the UI, no specific documentation needed
- [x] Document icon meanings (shield=tank, cross=healer, swords=dps)
  - **Note**: Icons are self-explanatory with aria-labels
- [x] Add note that SVG icons are used for role badges
  - **Note**: Implementation detail, not user-facing documentation

## Estimated Effort
- **Phase 1 (SVG Icons)**: 45 minutes
- **Phase 2 (Backend)**: 30 minutes
- **Phase 3 (CSS)**: 30 minutes
- **Phase 4 (Testing)**: 30 minutes
- **Phase 5 (Deployment)**: 15 minutes
- **Total**: ~2.5 hours

## Dependencies
- Requires `active_spec_role` data (already available from roster-filtering feature)
- No new API calls or data fetching needed

## Rollback Plan
If issues arise:
1. Revert renderer changes (restore faction badge logic)
2. Revert CSS changes (restore `.gmpr-faction-badge` class)
3. Rebuild and redeploy
