# Add Roster Filtering and Sorting

## Change ID
`add-roster-filtering-sorting`

## Summary
Add client-side filtering and sorting capabilities to the guild roster display to help users navigate large guild rosters more effectively.

## Problem Statement
When a guild has many characters (20+ members), it becomes difficult to find specific characters or identify characters with certain characteristics (role, score range, etc.). The current implementation displays all characters in a fixed order without any filtering or sorting options.

## Proposed Solution
Add a filter/sort toolbar above the roster that allows users to:
1. **Filter by role** (Tank, Healer, DPS) using the `active_spec_role` field from Raider.IO API
2. **Filter by name** (text search)
3. **Filter by score range** (min/max Mythic+ score)
4. **Sort by name** (A-Z or Z-A)
5. **Sort by Raider.IO score** (ascending or descending)

The implementation will be **client-side** using JavaScript to:
- Avoid additional server requests
- Provide instant feedback
- Work with the existing cached data

## User Impact
- **Positive**: Users can quickly find characters by role, name, or score range
- **Positive**: Large guilds become more manageable
- **Positive**: Officers can easily identify undergeared members or specific roles
- **No breaking changes**: Existing shortcode behavior remains the same

## Technical Approach
1. **Fetch `active_spec_role` from Raider.IO API** by updating the character profile request
2. **Store role data** in member objects during hydration
3. **Add filter/sort toolbar** HTML before the roster list
4. **Implement JavaScript** to:
   - Show/hide cards based on filters
   - Re-order cards based on sort selection
   - Update UI to show active filters
   - Persist filter state in URL parameters (optional)

## Out of Scope
- Server-side filtering/sorting (not needed, adds complexity)
- Persistent filter preferences (can be added later)
- Advanced filters (ilvl, class, specific dungeons) - can be added incrementally

## Dependencies
- Requires updating the Raider.IO API request to include role information
- CSS updates for the filter toolbar styling
- JavaScript updates for filter/sort logic

## Risks and Mitigations
- **Risk**: Role data missing for some characters
  - **Mitigation**: Show "Unknown" role, allow filtering them separately
- **Risk**: Performance with very large rosters (100+ characters)
  - **Mitigation**: Client-side filtering is fast; if needed, add virtual scrolling later
- **Risk**: Theme CSS conflicts with filter toolbar
  - **Mitigation**: Use scoped `.gmpr` selectors with `!important` as needed

## Success Criteria
- Users can filter by role, name, and score range
- Users can sort by name or score
- Filters work instantly without page reload
- Mobile-friendly filter controls
- No performance degradation on large rosters (tested with 50+ members)
