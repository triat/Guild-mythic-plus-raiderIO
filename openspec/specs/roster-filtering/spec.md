# roster-filtering Specification

## Purpose
TBD - created by archiving change add-roster-filtering-sorting. Update Purpose after archive.
## Requirements
### Requirement: Filter by Role
The roster display MUST provide a role filter allowing users to show only characters with specific active specialization roles.

#### Scenario: User filters by Tank role
**Given** a guild roster with characters of mixed roles (Tank, Healer, DPS)
**When** the user selects "Tank" from the role filter dropdown
**Then** only characters with `active_spec_role = "TANK"` are displayed
**And** other characters are hidden
**And** the displayed character count updates

#### Scenario: User selects "All Roles"
**Given** an active role filter (e.g., "Healer")
**When** the user selects "All Roles"
**Then** all characters are displayed regardless of role
**And** the filter is effectively cleared

#### Scenario: Character has unknown role
**Given** a character without `active_spec_role` data
**When** rendering the character card
**Then** it is assigned `data-role="unknown"`
**And** can be filtered by selecting "Unknown" in the role dropdown

### Requirement: Filter by Name
The roster display MUST provide a name search filter allowing users to find characters by partial name match, with debounced input handling.

#### Scenario: User searches for partial name with debounce
**Given** a guild roster with 50+ characters including "Eldrìlas", "Eldris", and "Aidz"
**When** the user types "eldr" in the name search input
**Then** the filter MUST NOT apply immediately on each keystroke
**And** the filter applies after 300ms of no typing activity
**And** only "Eldrìlas" and "Eldris" are displayed after the debounce period
**And** the search is case-insensitive
**And** special characters are matched correctly

#### Scenario: User continues typing before debounce fires
**Given** the user has typed "el" in the name search input
**When** the user types "d" within 300ms
**Then** the previous debounce timer is cancelled
**And** a new 300ms timer starts
**And** the filter only applies once the user stops typing for 300ms

### Requirement: Filter by Score Range
The roster display MUST provide min/max Mythic+ score filters allowing users to find characters within a specific score range, with debounced input handling.

#### Scenario: User sets minimum score with debounce
**Given** a guild roster with characters ranging from 0 to 3500 score
**When** the user types "3000" in the minimum score input
**Then** the filter MUST NOT apply on each digit entered
**And** the filter applies after 300ms of no typing activity
**And** only characters with `mplus_score >= 3000` are displayed after the debounce period

#### Scenario: User adjusts score filter rapidly
**Given** the user is typing in the minimum score field
**When** the user types multiple digits in quick succession (e.g., "3", "0", "0", "0")
**Then** only one filter operation executes after typing completes
**And** the final value "3000" is used for filtering

### Requirement: Sort by Name
The roster display MUST provide sorting options to order characters alphabetically by name.

#### Scenario: User sorts by name ascending
**Given** characters named "Zyra", "Aidz", "Eldrìlas"
**When** the user selects "Name (A-Z)" from the sort dropdown
**Then** characters are re-ordered to: "Aidz", "Eldrìlas", "Zyra"
**And** the visual order in the DOM updates

#### Scenario: User sorts by name descending
**Given** characters named "Zyra", "Aidz", "Eldrìlas"
**When** the user selects "Name (Z-A)" from the sort dropdown
**Then** characters are re-ordered to: "Zyra", "Eldrìlas", "Aidz"

### Requirement: Sort by Score
The roster display MUST provide sorting options to order characters by Mythic+ score, with score descending as the default sort order.

#### Scenario: Default sort is score descending
**Given** a guild roster with characters having scores 1200, 3500, 2800
**When** the roster is initially loaded
**Then** characters are automatically sorted by score descending
**And** the display order is: 3500, 2800, 1200
**And** the sort dropdown shows "Score (High to Low)" as selected

#### Scenario: Roster maintains score sort on data refresh
**Given** a roster with default score descending sort active
**When** async data refresh occurs and new HTML is injected
**Then** the roster is automatically re-sorted by score descending
**And** the sort dropdown shows "Score (High to Low)" as selected

### Requirement: Clear All Filters
The roster display MUST provide a "Clear" button to reset all filters and sorting to default state (score descending).

#### Scenario: User clears all filters and returns to default sort
**Given** active filters: Role="DPS", Name="aid", MinScore=2000, Sort="Name (A-Z)"
**When** the user clicks the "Clear" button
**Then** all filter inputs are reset to default values
**And** all characters are displayed
**And** characters are sorted by score descending (default sort)
**And** the sort dropdown shows "Score (High to Low)" as selected

### Requirement: Filter State Persistence During Refresh
Filter state MUST be preserved when async data refresh occurs, including debounce behavior.

#### Scenario: Debounce persists during async refresh
**Given** active filters with pending debounce timers
**When** async data refresh triggers and new HTML is injected
**Then** the debounce logic is re-initialized for the new DOM elements
**And** any in-flight debounce timers from the old DOM are cancelled
**And** new user input creates new debounce timers

### Requirement: Character Data Attributes
Each character card MUST include data attributes for client-side filtering.

#### Scenario: Character card has required data attributes
**Given** a character named "Eldrìlas" with role "DPS" and score 3555
**When** the character card is rendered
**Then** the card HTML includes `data-role="dps"`
**And** includes `data-name="eldrìlas"` (lowercase)
**And** includes `data-score="3555"`

### Requirement: Empty State Message
When no characters match the active filters, an empty state message MUST be displayed.

#### Scenario: No characters match filters
**Given** a guild with 50 characters
**When** the user sets filters that match 0 characters
**Then** a message "No characters match your filters" is displayed
**And** the roster list is visually empty
**And** the filter controls remain functional

### Requirement: Results Count Display
The filter toolbar MUST display the count of visible characters versus total characters.

#### Scenario: Results count updates with filters
**Given** a guild with 50 total characters
**When** the user applies filters showing 12 characters
**Then** the results count displays "Showing 12 of 50 characters"
**And** updates in real-time as filters change

### Requirement: Character Profile Role Data
The character hydration logic MUST extract and cache the `active_spec_role` field from the Raider.IO `/characters/profile` endpoint.

#### Scenario: Role data is cached
**Given** a character profile API response includes `"active_spec_role": "TANK"`
**When** the character data is hydrated
**Then** the role is normalized to lowercase: "tank"
**And** stored in the member data as `active_spec_role`
**And** cached for future requests

### Requirement: Raider.IO API Request with Role Field
The Raider.IO character profile API request MUST include `active_spec_role` in the fields parameter.

#### Scenario: API request includes role field
**Given** a request to fetch a character profile
**When** the API URL is constructed
**Then** the `fields` parameter includes `active_spec_role`
**And** the full fields value is `mythic_plus_scores_by_season:current,mythic_plus_best_runs,active_spec_role`

### Requirement: Debounced Text Input Filtering
Text and numeric filter inputs (name search, min score, max score) MUST use debounced input handling to improve performance with large rosters.

#### Scenario: Default debounce timeout
**Given** no custom configuration is provided
**When** the filter inputs are initialized
**Then** the debounce timeout is set to 300ms for text and numeric inputs

#### Scenario: Custom debounce timeout
**Given** a custom timeout value is defined in the initialization
**When** the filter inputs are initialized
**Then** the custom timeout value is used for debouncing text and numeric inputs
**And** the behavior remains consistent with the default implementation

### Requirement: Immediate Dropdown Filtering
Dropdown filters (role, sort) and buttons (clear) MUST apply changes immediately without debouncing.

#### Scenario: Role filter applies immediately
**Given** a guild roster with characters of mixed roles
**When** the user selects "Tank" from the role filter dropdown
**Then** the filter applies immediately without delay
**And** only Tank characters are displayed

#### Scenario: Sort applies immediately
**Given** a guild roster with unsorted characters
**When** the user selects "Score (High to Low)" from the sort dropdown
**Then** the sort applies immediately without delay
**And** characters are re-ordered instantly

