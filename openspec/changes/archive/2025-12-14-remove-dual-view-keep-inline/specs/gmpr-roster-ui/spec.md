## REMOVED Requirements

### Requirement: Dual roster views
**Reason**: Simplifying to a single inline expandable view for better UX and reduced complexity.
**Migration**: Inline view becomes the only view; cards view is removed entirely.

### Requirement: Always-visible view toggle
**Reason**: No longer needed since only one view exists.
**Migration**: Toggle buttons are removed from the UI.

### Requirement: Persisted view preference
**Reason**: No view preference needed with single view.
**Migration**: localStorage key `gmpr_roster_view` is no longer used.

## ADDED Requirements

### Requirement: Single expandable inline view
The system SHALL provide a single inline roster view where each member row can expand to show detailed Mythic+ run information.

#### Scenario: Inline view renders member profiles
- **WHEN** `[gmpr_guild]` is rendered
- **THEN** the output displays members in a compact inline profile layout with avatar, name, class/spec, realm, M+ score, and Profile CTA

#### Scenario: Header row is clickable to expand
- **WHEN** a user clicks anywhere on a member's header row (except the Profile link)
- **THEN** the expandable details section toggles open/closed with a smooth CSS animation

#### Scenario: Expand animation uses max-height transition
- **WHEN** a profile expands or collapses
- **THEN** the expandable content animates using CSS `max-height` transition (0 to ~600px) over 0.4s ease

#### Scenario: Expand icon rotates on toggle
- **WHEN** a profile is expanded
- **THEN** the chevron icon rotates 180 degrees
- **WHEN** the profile is collapsed
- **THEN** the chevron icon returns to 0 degrees

#### Scenario: Profile CTA does not trigger expand
- **WHEN** a user clicks the "Profile" link/button
- **THEN** the link opens in a new tab and does NOT toggle the expand state

#### Scenario: Details section shows best runs grid
- **WHEN** a profile is expanded and member has best runs data
- **THEN** a grid of up to 8 dungeon run cards is displayed with dungeon image, key level, name, short name, and score

### Requirement: Mobile-responsive inline view
The system SHALL ensure the inline view remains fully usable on mobile devices.

#### Scenario: Pills hidden on narrow screens
- **WHEN** viewport width is 1000px or less
- **THEN** the dungeon pills in the header row are hidden

#### Scenario: Runs grid adapts to mobile
- **WHEN** viewport width is 768px or less
- **THEN** the best runs grid displays 2 columns instead of 4

#### Scenario: Header row wraps on small screens
- **WHEN** viewport width is 640px or less
- **THEN** the header row content wraps appropriately and the profile link becomes full-width

## MODIFIED Requirements

### Requirement: Progressive enhancement fallback
The system SHALL remain usable without JavaScript, displaying expanded content by default.

#### Scenario: JS disabled shows expanded content
- **WHEN** JavaScript is disabled
- **THEN** all profile expandable sections are visible (not collapsed) so content remains accessible

#### Scenario: JS enabled enables collapse
- **WHEN** JavaScript is enabled
- **THEN** profiles start collapsed and can be expanded via click

### Requirement: Accessible controls
The system SHALL provide accessible expand/collapse controls (keyboard-operable and screen-reader friendly).

#### Scenario: Keyboard navigation for expand
- **WHEN** a keyboard-only user focuses the header row and presses Enter or Space
- **THEN** the expandable section toggles open/closed

#### Scenario: Screen reader announces state
- **WHEN** a screen reader user interacts with the expand control
- **THEN** the current expanded/collapsed state is announced via `aria-expanded` attribute
