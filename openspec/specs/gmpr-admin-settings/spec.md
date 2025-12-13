# gmpr-admin-settings Specification

## Purpose
TBD - created by archiving change add-gmpr-admin-settings. Update Purpose after archive.
## Requirements
### Requirement: GMPR settings page
The system SHALL provide a WordPress admin page accessible via **Settings → GMPR** to configure the plugin.

#### Scenario: Admin-only access
- **WHEN** a user without the `manage_options` capability attempts to access the page
- **THEN** access is denied

#### Scenario: Admin access allowed
- **WHEN** an admin accesses Settings → GMPR
- **THEN** the page displays a configuration form

### Requirement: Settings storage
The system SHALL store GMPR configuration in a WordPress option (e.g. `gmpr_settings`) and MUST sanitize/validate all inputs.

#### Scenario: Saving valid values
- **WHEN** an admin saves valid values (region/realm/guild/ttl/member_limit)
- **THEN** the option is saved and used as the plugin defaults

#### Scenario: Rejecting invalid values
- **WHEN** an admin submits an invalid region or out-of-range values (e.g. negative TTL)
- **THEN** the value is rejected or normalized to a safe value

### Requirement: Secure API key handling
The system MUST allow configuring a Raider.IO API key in the admin UI and MUST NOT display the key in plaintext.

#### Scenario: Password field
- **WHEN** an admin opens the GMPR settings page
- **THEN** the API key is displayed as a password field (masked)

#### Scenario: Keep previous key when left empty
- **WHEN** an admin saves settings with the API key field left empty
- **THEN** the previously saved key is kept

