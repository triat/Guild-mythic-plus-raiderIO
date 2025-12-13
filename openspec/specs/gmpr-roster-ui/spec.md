# gmpr-roster-ui Specification

## Purpose
TBD - created by archiving change add-roster-view-toggle. Update Purpose after archive.
## Requirements
### Requirement: Dual roster views
The system SHALL provide two front-end roster presentations:
- **Inline view**
- **Cards view**

#### Scenario: Both views are available
- **WHEN** `[gmpr_guild]` is rendered
- **THEN** the output includes markup that can render members in both table and cards form

### Requirement: Always-visible view toggle
The system SHALL render an always-visible front-end control that lets the user switch between inline and cards view.

#### Scenario: Toggle is visible
- **WHEN** a user loads a page containing `[gmpr_guild]`
- **THEN** a view toggle control is visible near the roster

#### Scenario: Toggle switches the view
- **WHEN** a user selects “Cards”
- **THEN** the roster switches from inline presentation to cards presentation

### Requirement: Persisted view preference
The system SHALL persist the selected view across reloads using browser storage (e.g. localStorage).

#### Scenario: Preference persists after reload
- **WHEN** a user selects “Cards” and reloads the page
- **THEN** the roster renders in cards view by default

### Requirement: Progressive enhancement fallback
The system SHALL remain usable without JavaScript, with a default server-rendered view.

#### Scenario: JS disabled
- **WHEN** JavaScript is disabled
- **THEN** the roster remains visible and readable (at least the inline view)

### Requirement: Accessible controls
The system SHALL provide accessible toggle controls (keyboard-operable and screen-reader friendly).

#### Scenario: Keyboard navigation
- **WHEN** a keyboard-only user navigates to the toggle
- **THEN** they can switch views using keyboard interactions

