# Default Sort by Score

## Summary
Set the default sort order for the guild roster to display members sorted by Raider.IO score (high to low), showing the highest-scoring players first.

## Motivation
Currently, the roster displays members in the order they are received from the Raider.IO API (which appears to be unsorted or sorted by some internal Raider.IO logic). For a Mythic+ focused guild roster display, the most relevant default view is to show members sorted by their Raider.IO score from highest to lowest. This allows users to immediately see the top performers without needing to manually select the "Score (High to Low)" sort option.

## Scope
This change affects:
- Client-side default sort state in `assets/gmpr.js`
- Initial sort application on page load
- Clear filters behavior (returns to score-desc, not "none")

This change does NOT affect:
- Available sort options (all existing options remain)
- User's ability to change sort order
- Server-side rendering or API calls
- Filter persistence logic

## User Impact
- **Better default view**: Users see the highest-scoring players first by default
- **Consistent with purpose**: Aligns with the plugin's focus on displaying Raider.IO data
- **No loss of functionality**: Users can still sort by name or other options
- **Clear button behavior**: "Clear" now returns to score-desc instead of unsorted

## Related Specs
This change modifies the existing `roster-filtering` spec by changing the default sort order.

## Dependencies
None - this is a self-contained JavaScript change.

## Risks and Mitigations
- **Risk**: Users who expect unsorted/API order by default might be surprised
  - **Mitigation**: The sort order is clearly visible in the dropdown (will show "Score (High to Low)") and can be easily changed
- **Risk**: "Clear" button behavior changes
  - **Mitigation**: This is actually more intuitive - clearing filters should return to the default view (score-sorted), not an arbitrary unsorted state

## Alternatives Considered
1. **Server-side sorting**: Could sort members array in PHP before rendering. Decided against because client-side sorting is already implemented and working well.
2. **Different default sort**: Could default to name-asc. Rejected because score is more relevant for a Raider.IO focused plugin.
3. **Configurable default**: Could make default sort an admin setting. Decided against to keep things simple - can add later if requested.

## Open Questions
None - the approach is straightforward.
