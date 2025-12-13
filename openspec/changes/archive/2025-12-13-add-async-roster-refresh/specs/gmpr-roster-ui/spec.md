## MODIFIED Requirements

### Requirement: Progressive enhancement fallback
The system SHALL remain usable without JavaScript, with a default server-rendered view.

#### Scenario: JS disabled with stale cache
- **WHEN** JavaScript is disabled and stale cache exists
- **THEN** the roster remains visible (stale) and readable without client-side updates

#### Scenario: JS enabled updates roster
- **WHEN** JavaScript is enabled and an async refresh completes
- **THEN** the roster UI updates to reflect the latest cached data without a full page reload


