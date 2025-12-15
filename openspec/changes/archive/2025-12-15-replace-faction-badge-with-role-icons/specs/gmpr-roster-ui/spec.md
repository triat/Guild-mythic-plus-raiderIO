# Spec Delta: Guild Roster UI - Role Badges

## MODIFIED Requirements

### Requirement: Character avatar display
**Modified from**: Displaying faction badge on avatar
**Now displays**: Role icon badge on avatar

Each character avatar MUST display a role icon badge instead of a faction badge, positioned at the bottom-right corner of the avatar.

#### Scenario: Tank character shows shield SVG icon
**Given** a character with `active_spec_role = "tank"`
**When** the character card is rendered
**Then** a shield SVG icon is displayed at the bottom-right of the avatar
**And** the badge has a gray/silver background (e.g., #6c757d)
**And** the badge includes `class="gmpr-role-badge gmpr-role-tank"`
**And** the badge includes `aria-label="Tank"`
**And** the SVG icon is white-colored with 12x12px dimensions

#### Scenario: Healer character shows cross SVG icon
**Given** a character with `active_spec_role = "healing"`
**When** the character card is rendered
**Then** a cross/plus SVG icon is displayed at the bottom-right of the avatar
**And** the badge has a green background (#28a745)
**And** the badge includes `class="gmpr-role-badge gmpr-role-healing"`
**And** the badge includes `aria-label="Healer"`
**And** the SVG icon is white-colored with 12x12px dimensions

#### Scenario: DPS character shows crossed swords SVG icon
**Given** a character with `active_spec_role = "dps"`
**When** the character card is rendered
**Then** a crossed swords SVG icon is displayed at the bottom-right of the avatar
**And** the badge has a red/orange background (#dc3545)
**And** the badge includes `class="gmpr-role-badge gmpr-role-dps"`
**And** the badge includes `aria-label="DPS"`
**And** the SVG icon is white-colored with 12x12px dimensions

#### Scenario: Character without role data shows no badge
**Given** a character with empty or missing `active_spec_role`
**When** the character card is rendered
**Then** no badge is displayed on the avatar
**And** the avatar-wrapper contains only the avatar image

#### Scenario: Badge positioning and styling
**Given** any character with a role badge
**When** the character card is rendered
**Then** the badge is positioned absolutely at `bottom: -4px, right: -4px`
**And** the badge is circular with `border-radius: 50%`
**And** the badge has dimensions `width: 20px, height: 20px`
**And** the badge has a border and shadow for visual separation
**And** the SVG icon is centered within the badge using flexbox
**And** the SVG icon has `width: 12px` and `height: 12px`
**And** the SVG fill color is white for sufficient contrast (WCAG AA minimum)

#### Scenario: SVG icons render cleanly
**Given** any character with a role badge
**When** the badge is displayed
**Then** the inline SVG renders without pixelation
**And** the SVG scales cleanly at different zoom levels
**And** the SVG is crisp on high-DPI displays (retina screens)

#### Scenario: Mobile responsive badge
**Given** a character with a role badge
**When** viewed on mobile screen
**Then** the badge scales proportionally with the avatar
**And** remains positioned at bottom-right
**And** remains readable and recognizable

## REMOVED Requirements

### Requirement: Faction badge display
**Removed**: The roster MUST no longer displays faction badges (Alliance/Horde indicators)

#### Scenario: No faction badge rendered (removed)
**Given** any character with faction data
**When** the character card is rendered
**Then** no faction badge is displayed
**And** the `.gmpr-faction-badge` class is no longer used

## Cross-References
- Related to: **Character Profile Data** (roster-filtering spec) - Uses existing `active_spec_role` field
- Related to: **Filter by Role** (roster-filtering spec) - Visual consistency with role filter
- Modifies: **Single expandable inline view** (gmpr-roster-ui spec) - Changes avatar badge display
