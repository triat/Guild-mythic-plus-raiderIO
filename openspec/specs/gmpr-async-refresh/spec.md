# gmpr-async-refresh Specification

## Purpose
TBD - created by archiving change add-async-roster-refresh. Update Purpose after archive.
## Requirements
### Requirement: Stale-while-revalidate roster refresh
The system SHALL implement a stale-while-revalidate strategy for guild roster data.

#### Scenario: Fresh cache
- **WHEN** a fresh cached roster exists
- **THEN** the shortcode renders using the fresh cache without calling Raider.IO during render

#### Scenario: Stale cache
- **WHEN** only a stale cached roster exists
- **THEN** the shortcode renders using the stale cache and triggers an asynchronous refresh

#### Scenario: Cold start
- **WHEN** no cached roster exists
- **THEN** the shortcode renders a lightweight loading state and triggers an asynchronous refresh

### Requirement: Async refresh stampede protection
The system SHALL prevent refresh stampedes using a lock and rate limiting per roster context.

#### Scenario: Multiple visitors
- **WHEN** multiple visitors load the page while data is stale/missing
- **THEN** only one refresh job is scheduled/executed within the configured window

### Requirement: Public cached roster endpoint
The system SHALL provide an endpoint that returns the latest cached roster payload for a given roster context.

#### Scenario: Fetch latest cached payload
- **WHEN** the frontend requests the endpoint for the roster context
- **THEN** the endpoint returns the latest cached roster payload (or an explicit “not ready” response)

### Requirement: Security (no secrets)
The system MUST NOT expose the Raider.IO API key via any async endpoint, logs, or client-side code.

#### Scenario: Endpoint payload safety
- **WHEN** the endpoint returns roster data
- **THEN** it contains only public roster fields and never contains the API key

