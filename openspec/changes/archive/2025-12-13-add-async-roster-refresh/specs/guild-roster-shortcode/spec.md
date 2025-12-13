## MODIFIED Requirements

### Requirement: Cache via transients
The system SHALL cache Raider.IO results using WordPress transients to reduce external calls.

#### Scenario: Cache hit (fresh)
- **WHEN** `[gmpr_guild]` is rendered and a fresh cached roster exists for the roster context
- **THEN** the plugin renders without making external HTTP calls during that request

#### Scenario: Cache hit (stale)
- **WHEN** `[gmpr_guild]` is rendered and only a stale cache exists for the roster context
- **THEN** the plugin renders using stale data and triggers an asynchronous refresh

#### Scenario: Cache miss (cold start)
- **WHEN** `[gmpr_guild]` is rendered and no cache exists for the roster context
- **THEN** the plugin renders a lightweight loading state and triggers an asynchronous refresh


