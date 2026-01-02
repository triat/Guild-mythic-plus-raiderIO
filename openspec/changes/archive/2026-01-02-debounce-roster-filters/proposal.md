# Debounce Roster Filters

## Summary
Add input debouncing to search and numeric filter fields to improve perceived performance when filtering large guild rosters.

## Motivation
Currently, filter operations trigger immediately on every keystroke in the name search, min score, and max score inputs. With large rosters (50+ members), this can create a laggy typing experience as the filter/sort logic executes synchronously on every input event. By adding a short debounce delay (300ms), we allow users to finish typing before applying filters, reducing the number of filter calculations and improving the user experience.

## Scope
This change affects:
- Client-side filtering behavior in `assets/gmpr.js`
- User interaction patterns for text and numeric filter inputs

This change does NOT affect:
- Dropdown filters (role, sort) - these remain immediate as they are single-click selections
- Server-side rendering or API calls
- Filter state persistence logic
- The underlying filter/sort algorithms

## User Impact
- **Better typing experience**: Users can type naturally in search/score fields without UI lag
- **Smoother filtering**: Visual updates occur after a brief pause rather than on every keystroke
- **No behavioral change for dropdowns**: Role and sort filters remain instantly responsive
- **Configurable delay**: The debounce timeout can be adjusted if needed (default 300ms)

## Related Specs
This change modifies the existing `roster-filtering` spec by adding debounce behavior to specific filter inputs.

## Dependencies
None - this is a self-contained JavaScript enhancement.

## Risks and Mitigations
- **Risk**: Users might perceive a delay as lag
  - **Mitigation**: Use a short timeout (300ms) which is imperceptible during normal typing but effective for reducing calculations
- **Risk**: Debounce logic adds complexity
  - **Mitigation**: Use a simple timeout-based implementation; no external dependencies required

## Alternatives Considered
1. **Throttling instead of debouncing**: Would still execute during typing, just less frequently. Debouncing is better as it waits until the user finishes typing.
2. **Virtualization/pagination**: More complex solution that would require significant restructuring. Debouncing provides immediate value with minimal changes.
3. **Web Workers**: Overkill for this use case and adds significant complexity.

## Open Questions
None - the approach is straightforward and well-understood.
