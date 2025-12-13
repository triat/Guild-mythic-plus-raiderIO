# guild-roster-shortcode Specification

## Purpose
Display a World of Warcraft guild roster via a WordPress shortcode, backed by Raider.IO data and WordPress caching.
## Requirements
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

#### Scenario: Score Mythic+ best-effort
- **WHEN** the guild roster does not directly include per-member Mythic+ scores
- **THEN** the plugin attempts to hydrate these scores via a “character profile” endpoint (best-effort) and leaves the score empty if not found

### Requirement: Configuration sécurisée de la clé API
The system MUST read the Raider.IO API key server-side only and MUST NOT expose the API key in rendered HTML, shortcode attributes, or public URLs.

#### Scenario: Clé API fournie via constante ou filtre
- **WHEN** `GMPR_RAIDERIO_API_KEY` is defined (or a filter provides a key)
- **THEN** the Raider.IO client uses that key to authenticate outbound requests without reflecting it in HTML

#### Scenario: Clé API absente
- **WHEN** no API key is available
- **THEN** the plugin renders a clear error and performs no external call

### Requirement: Cache via transients
The system SHALL cache Raider.IO results using WordPress transients to reduce external calls.

#### Scenario: Cache hit
- **WHEN** `[gmpr_guild]` is rendered and a valid transient exists for (region, realm, guild)
- **THEN** the plugin uses the cache and performs no external HTTP request

#### Scenario: Cache miss
- **WHEN** `[gmpr_guild]` is rendered and no valid cache exists
- **THEN** the plugin performs an external HTTP request, normalizes the response, and stores the result in cache

#### Scenario: Cache par personnage (score)
- **WHEN** the plugin hydrates scores via “character profile” calls
- **THEN** the plugin caches scores per character to avoid repeated calls

### Requirement: Tolérance aux pannes et stale cache
The system SHALL handle Raider.IO network/HTTP errors and SHALL render a fallback based on “stale cache” when available.

#### Scenario: Raider.IO indisponible avec cache stale
- **WHEN** Raider.IO returns an error (timeout, DNS, 5xx) and a “stale cache” exists
- **THEN** the plugin renders stale data with a subtle warning

#### Scenario: Raider.IO indisponible sans cache
- **WHEN** Raider.IO returns an error (timeout, DNS, 5xx) and no cache exists
- **THEN** the plugin renders a user-friendly error

### Requirement: Refresh admin-only
The system SHALL support a `refresh` shortcode parameter that forces a refresh (bypassing cache) and MUST restrict this capability to administrators.

#### Scenario: Refresh effectué par admin
- **WHEN** a logged-in admin renders `[gmpr_guild refresh="1"]`
- **THEN** the plugin ignores cache (guild and characters) and redoes external calls

#### Scenario: Refresh ignoré pour non-admin
- **WHEN** a non-admin user renders `[gmpr_guild refresh="1"]`
- **THEN** the plugin behaves as if `refresh` was absent

### Requirement: Limite temporaire du nombre de membres
The system SHALL limit the number of displayed members to a default value of 20 to reduce response times, and SHOULD allow overriding this limit via a WordPress filter.

#### Scenario: Limite par défaut appliquée
- **WHEN** the guild contains more than 20 members
- **THEN** the plugin displays only the first 20 members according to its internal ordering

#### Scenario: Limite surchargée via filtre
- **WHEN** a site defines a `gmpr_member_limit` filter returning a value N
- **THEN** the plugin limits output to N members

### Requirement: Normalisation des identifiants personnage
The system SHALL normalize roster character names before calling the “character profile” endpoint.

#### Scenario: Suppression suffixe technique
- **WHEN** a character name includes a technical suffix like `-<id>` (e.g. `Cielã-267166348`)
- **THEN** the plugin uses only the base name (e.g. `Cielã`) for the “character profile” request

### Requirement: Logging de debug sans secret
The system SHOULD emit debug logs when `WP_DEBUG` is enabled, and MUST NOT include the API key in logs.

#### Scenario: Erreur HTTP logguée
- **WHEN** a Raider.IO call fails
- **THEN** the plugin logs the status and a response excerpt without exposing the API key

