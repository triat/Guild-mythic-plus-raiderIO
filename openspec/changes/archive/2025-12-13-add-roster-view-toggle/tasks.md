## 1. Specs
- [x] 1.1 Add a new capability spec `gmpr-roster-ui` describing table/cards rendering + toggle + persistence.
- [x] 1.2 Modify `guild-roster-shortcode` to require exposing both views and an always-visible toggle (progressive enhancement).

## 2. Implementation (apply stage)
- [x] 2.1 Update renderer to output a view toggle and markup for both views (inline + cards).
- [x] 2.2 Add/extend CSS for cards layout and responsive behavior.
- [x] 2.3 Add a small JS file to handle toggling and persistence via localStorage.
- [x] 2.4 Ensure accessibility: keyboard navigation and ARIA attributes.

## 3. Validation
- [x] 3.1 Manual checks:
  - [x] toggle is visible and works (inline ↔ cards)
  - [x] selection persists across reloads
  - [x] page remains usable with JS disabled (inline visible)
- [x] 3.2 `openspec validate add-roster-view-toggle --strict`


