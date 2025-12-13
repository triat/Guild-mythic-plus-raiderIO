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
- **THEN** the output includes markup that can render members in both inline and cards form

#### Scenario: Inline view uses compact profile layout
- **WHEN** the inline view is rendered for a member
- **THEN** the member row uses a compact “profile header” layout inspired by `eldrìlas-profile-compact.html` including:
  - avatar (with placeholder fallback),
  - character name,
  - realm,
  - Mythic+ score,
  - a clear Raider.IO “Profile” CTA button/link (the row itself is not clickable)

#### Scenario: Inline compact theme is dark with site accent
- **WHEN** the inline compact layout is rendered
- **THEN** the component uses a dark theme with an accent color aligned to the site palette (accent: `#DCA54A`) without applying global page styles

#### Scenario: Inline view shows best runs pills when available
- **WHEN** member data includes best runs
- **THEN** the inline view renders a compact set of “dungeon pills” (e.g., top 4) showing dungeon short name and Mythic level

#### Scenario: Inline view shows expandable best runs details when available
- **WHEN** member data includes best runs
- **THEN** the inline view provides an expandable section with best runs details (dungeon name, key level, score, and optional background image)

#### Scenario: Cards view remains available
- **WHEN** the cards view is selected
- **THEN** the roster still renders the existing cards presentation (unchanged by this change)

#### Scenario: Avatars in both views
- **WHEN** member data includes an avatar URL
- **THEN** the inline view and the cards view render the character avatar

#### Scenario: Avatar placeholder fallback
- **WHEN** member data does not include an avatar URL (or the image fails to load)
- **THEN** the UI renders a generic placeholder avatar and the layout remains stable

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

#### Scenario: JS disabled with stale cache
- **WHEN** JavaScript is disabled and stale cache exists
- **THEN** the roster remains visible (stale) and readable without client-side updates

#### Scenario: JS enabled updates roster
- **WHEN** JavaScript is enabled and an async refresh completes
- **THEN** the roster UI updates to reflect the latest cached data without a full page reload

### Requirement: Accessible controls
The system SHALL provide accessible toggle controls (keyboard-operable and screen-reader friendly).

#### Scenario: Keyboard navigation
- **WHEN** a keyboard-only user navigates to the toggle
- **THEN** they can switch views using keyboard interactions

