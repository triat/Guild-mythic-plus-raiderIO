## MODIFIED Requirements

### Requirement: Shortcode roster de guilde
The system SHALL provide a WordPress shortcode `[gmpr_guild]` that displays a list of World of Warcraft guild members with at least:
- the character name,
- the Mythic+ score (Raider.IO),
- a link to the Raider.IO profile.

#### Scenario: Rendu nominal
- **WHEN** an editor adds `[gmpr_guild]` to a page and the configuration is valid (region/realm/guild) and Raider.IO responds successfully
- **THEN** the page renders a responsive table containing members and the minimum fields

#### Scenario: Paramètres invalides
- **WHEN** `[gmpr_guild]` is rendered with invalid or missing region/realm/guild
- **THEN** the plugin renders a user-friendly error and performs no external call

#### Scenario: Always-visible view toggle and cards view
- **WHEN** `[gmpr_guild]` is rendered
- **THEN** the output provides both inline and cards presentations and an always-visible toggle that lets the user switch views

#### Scenario: Persisted view preference
- **WHEN** a user selects a view and reloads the page
- **THEN** the previously selected view is restored


