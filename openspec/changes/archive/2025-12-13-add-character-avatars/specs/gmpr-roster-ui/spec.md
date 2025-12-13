## MODIFIED Requirements

### Requirement: Dual roster views
The system SHALL provide two front-end roster presentations:
- **Inline view**
- **Cards view**

#### Scenario: Both views are available
- **WHEN** `[gmpr_guild]` is rendered
- **THEN** the output includes markup that can render members in both inline and cards form

#### Scenario: Avatars in both views
- **WHEN** member data includes an avatar URL
- **THEN** the inline view and the cards view render the character avatar

#### Scenario: Avatar placeholder fallback
- **WHEN** member data does not include an avatar URL (or the image fails to load)
- **THEN** the UI renders a generic placeholder avatar and the layout remains stable


