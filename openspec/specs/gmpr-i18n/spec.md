# gmpr-i18n Specification

## Purpose
TBD - created by archiving change add-french-translation. Update Purpose after archive.
## Requirements
### Requirement: Load text domain on plugin initialization
The plugin SHALL load its text domain during WordPress initialization to enable translation files to be applied.

#### Scenario: Text domain loaded on plugins_loaded hook
- **WHEN** the plugin initializes via the `plugins_loaded` action
- **THEN** `load_plugin_textdomain('gmpr', false, basename(GMPR_PLUGIN_DIR) . '/languages')` is called to load translation files from the `languages/` directory

#### Scenario: Text domain matches all i18n function calls
- **WHEN** any i18n function is called in the plugin code
- **THEN** the text domain parameter is consistently `'gmpr'` across all calls

### Requirement: Provide French translation files
The plugin SHALL include complete French translation files for all user-facing strings.

#### Scenario: POT template file exists
- **WHEN** a developer needs to create or update translations
- **THEN** a `.pot` template file exists at `languages/gmpr.pot` containing all translatable strings with proper context

#### Scenario: French PO file exists
- **WHEN** WordPress is set to French locale (fr_FR)
- **THEN** a `.po` file exists at `languages/gmpr-fr_FR.po` with French translations for all strings

#### Scenario: French MO file exists
- **WHEN** WordPress loads translations for the French locale
- **THEN** a compiled `.mo` file exists at `languages/gmpr-fr_FR.mo` (compiled from the .po file)

### Requirement: Translate all frontend user-facing strings
The plugin SHALL translate all user-facing strings visible to end users in the frontend roster display.

#### Scenario: Roster UI labels are translated
- **WHEN** the roster is rendered with `[gmpr_guild]` shortcode
- **THEN** all labels (Role, Name, Min Score, Max Score, Sort, Clear, M+ Score, etc.) appear in the site's configured language

#### Scenario: Filter options are translated
- **WHEN** filter dropdowns are rendered
- **THEN** options like "All Roles", "Tank", "Healer", "DPS", "Default", "Name (A-Z)", etc. appear in the site's language

#### Scenario: Status messages are translated
- **WHEN** status messages are shown (loading, updating, errors, empty states)
- **THEN** messages like "Loading roster…", "Updating… (showing cached data)", "No members found.", "No characters match your filters." appear in the site's language

#### Scenario: Accessibility labels are translated
- **WHEN** ARIA labels and alt text are rendered
- **THEN** strings like "Guild members", "Avatar of %s", "Best Mythic+ runs", "Mythic+ score" appear in the site's language

#### Scenario: Call-to-action links are translated
- **WHEN** the "View Raider.IO Profile" link is rendered
- **THEN** the link text appears in the site's language

### Requirement: Support locale-aware number formatting
The plugin SHALL use WordPress locale-aware number formatting for numeric displays.

#### Scenario: Scores use locale formatting
- **WHEN** displaying Mythic+ scores or run scores
- **THEN** numbers are formatted using `number_format_i18n()` to respect locale-specific thousand separators and decimal points

### Requirement: Maintain translation documentation
The plugin SHALL provide documentation for translators and future language additions.

#### Scenario: README includes translation instructions
- **WHEN** a contributor wants to add a new language
- **THEN** clear instructions exist for generating .pot files, creating .po files, and compiling .mo files

