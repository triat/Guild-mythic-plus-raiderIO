## MODIFIED Requirements

### Requirement: Cache via transients
The system SHALL cache Raider.IO results using WordPress transients to reduce external calls.

#### Scenario: Cache hit
- **WHEN** `[gmpr_guild]` is rendered and a valid transient exists for (region, realm, guild)
- **THEN** the plugin uses the cache and performs no external HTTP request

#### Scenario: Cache miss
- **WHEN** `[gmpr_guild]` is rendered and no valid cache exists
- **THEN** the plugin performs an external HTTP request, normalizes the response, and stores the result in cache

#### Scenario: Cache par personnage (enriched profile fields)
- **WHEN** the plugin hydrates per-character data via “character profile” calls (score, avatar, best runs, spec/class/faction)
- **THEN** the plugin caches these fields per character to avoid repeated calls


