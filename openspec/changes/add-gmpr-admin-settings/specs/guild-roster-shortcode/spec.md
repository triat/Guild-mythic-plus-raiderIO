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

#### Scenario: Use admin settings as defaults
- **WHEN** an admin has configured defaults in Settings → GMPR and the shortcode does not provide attributes for region/realm/guild
- **THEN** the plugin uses those defaults to determine which guild to display

#### Scenario: Shortcode attributes take precedence
- **WHEN** `region/realm/guild` attributes are provided in the shortcode and are valid
- **THEN** those attributes override the values configured in Settings → GMPR

### Requirement: Configuration sécurisée de la clé API
The system MUST read the Raider.IO API key server-side only and MUST NOT expose the API key in rendered HTML, shortcode attributes, or public URLs.

#### Scenario: API key from admin settings
- **WHEN** an API key is configured via Settings → GMPR
- **THEN** the Raider.IO client uses that key server-side without reflecting it in HTML

#### Scenario: Clé API absente
- **WHEN** no API key is available (neither admin settings nor server-side fallback)
- **THEN** the plugin renders a clear error and performs no external call


