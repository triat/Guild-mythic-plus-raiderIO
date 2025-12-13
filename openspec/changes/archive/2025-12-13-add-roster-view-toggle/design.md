## Context
We already render a roster server-side. We want to introduce an alternative cards view and a user-facing toggle, without breaking non-JS environments.

## Goals / Non-Goals
- **Goals**:
  - Always-visible toggle on the front-end.
  - Persist user choice across reloads.
  - Accessible controls (keyboard + screen readers).
  - Progressive enhancement (table remains the default fallback).
- **Non-Goals**:
  - Changing backend fetching/caching behavior.
  - Introducing new data sources.

## Decisions
- **Persistence mechanism**: `localStorage` (key: `gmpr_roster_view`) with values `inline|cards`.
- **Default**: `inline` if no preference is stored or JS is disabled.
- **Rendering strategy**:
  - Server renders both view containers (inline + cards) and the toggle controls.
  - CSS controls visibility; JS only switches state + persists preference.
- **Accessibility**:
  - Toggle implemented as buttons with `aria-pressed` and labels, or a radiogroup pattern.

## Risks / Trade-offs
- Rendering both views increases HTML size; acceptable for MVP since member count is capped by existing limits.


