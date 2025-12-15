# Replace Faction Badge with Role Icons

## Why

The current roster display shows a faction badge (⚔ for Alliance, ☠ for Horde) overlaid on each character's avatar. However, this is not a relevant information on this display. In contrast, character roles (Tank, Healer, DPS) vary within the guild and are critical for roster composition and planning.

Displaying role icons instead of faction badges will:
- Provide immediately actionable information (role is more useful than faction for guild management)
- Improve visual scanning for specific roles
- Leverage the already-available `active_spec_role` data from the Raider.IO API
- Create visual consistency with the role filter that was recently added

## What Changes

Replace the faction badge display with role-specific icons:

### Visual Changes
- **Tank role**: Shield SVG icon
- **Healer role**: White cross SVG icon on green background
- **DPS role**: Crossed swords SVG icon

### Implementation Approach
1. **Create SVG icon files** in `assets/` directory:
   - `role-tank.svg`: Shield icon (simple geometric shield shape)
   - `role-healer.svg`: Cross/plus symbol (medical cross style)
   - `role-dps.svg`: Crossed swords icon (two swords in X formation)
2. **Remove faction badge rendering** from `class-gmpr-renderer.php` (lines 157-162, 184-186)
3. **Add role badge rendering** using the existing `$role` variable (already extracted on line 165)
   - Render inline SVG or reference SVG file based on role
4. **Update CSS** to rename `.gmpr-faction-badge` to `.gmpr-role-badge` and add role-specific styling:
   - Tank: neutral/gray background with shield icon
   - Healer: green background (#28a745) with white cross icon
   - DPS: red/orange background (#dc3545) with swords icon
5. **Handle missing role data**: Show no badge if role is empty/unknown

### Technical Details
- The `active_spec_role` field is already fetched from Raider.IO API
- Role values are normalized to lowercase: 'tank', 'healing', 'dps'
- Badge positioning remains the same (bottom-right of avatar)
- SVG icons will be inline for simplicity and control over styling
- Icon size: 12x12px within the 20x20px badge container
- No JavaScript changes needed (purely server-side rendering)

## User Impact

**Positive:**
- Guild officers can quickly identify role distribution at a glance
- Consistent with the role filter UI (users already familiar with role icons)
- More useful information displayed in limited space

**Neutral:**
- Faction information is no longer visible on the roster (already known from guild context)
- Characters without role data will show no badge (acceptable fallback)

**No Breaking Changes:**
- This is a visual change only
- No API changes, data structure changes, or behavior changes
- Existing cached data already includes role information

## Out of Scope

- Animated or interactive role badges
- Displaying both faction AND role (space constraints)
- Admin setting to toggle between faction/role display
- Complex multi-color SVG icons (keeping designs simple and monochrome)

## Dependencies

- Requires the `active_spec_role` data (already implemented in roster-filtering change)
- No new API requests or data fetching needed

## Risks and Mitigations

**Risk**: Characters with missing role data show no badge
- **Mitigation**: This is acceptable - most active characters will have role data from Raider.IO

**Risk**: Users may expect to see faction information
- **Mitigation**: Faction is implicit (all guild members share same faction), role is more valuable

**Risk**: SVG icons may increase HTML payload size
- **Mitigation**: Inline SVGs are small (<200 bytes each), minimal impact on page size

## Success Criteria

- All characters with role data display appropriate role icon
- Icons are visually distinct and recognizable
- No visual regressions (badge positioning, size, styling)
- Badge colors are accessible (sufficient contrast)
- Empty role data shows no badge (clean fallback)
