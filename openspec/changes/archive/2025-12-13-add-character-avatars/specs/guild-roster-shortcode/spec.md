## MODIFIED Requirements

### Requirement: Shortcode roster de guilde
The system SHALL provide a WordPress shortcode `[gmpr_guild]` that displays a list of World of Warcraft guild members with at least:
- the character name,
- the Mythic+ score (Raider.IO),
- a link to the Raider.IO profile.

#### Scenario: Rendu nominal
- **WHEN** `[gmpr_guild]` is rendered successfully
- **THEN** the roster is displayed

#### Scenario: Avatar URL is exposed when available
- **WHEN** Raider.IO provides an avatar/thumbnail URL for a character
- **THEN** the normalized member model includes an `avatar_url` field that can be used by the UI


