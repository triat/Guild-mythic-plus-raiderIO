# Spec: Roster Filtering and Sorting

## ADDED Requirements

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
The roster display MUST provide a name search filter allowing users to find characters by partial name match.

#### Scenario: User searches for partial name
**Given** a guild roster with characters including "Eldrìlas", "Eldris", and "Aidz"
**When** the user types "eldr" in the name search input
**Then** only "Eldrìlas" and "Eldris" are displayed
**And** the search is case-insensitive
**And** special characters are matched correctly

#### Scenario: User clears name search
**Given** an active name filter showing 3 of 20 characters
**When** the user clears the name search input
**Then** all 20 characters are displayed (subject to other active filters)

### Requirement: Filter by Score Range
The roster display MUST provide min/max Mythic+ score filters allowing users to find characters within a specific score range.

#### Scenario: User sets minimum score
**Given** a guild roster with characters ranging from 0 to 3500 score
**When** the user sets minimum score to 3000
**Then** only characters with `mplus_score >= 3000` are displayed
**And** characters with score < 3000 are hidden

#### Scenario: User sets both min and max score
**Given** a guild roster with characters ranging from 0 to 3500 score
**When** the user sets minimum score to 2000 and maximum score to 3000
**Then** only characters with `2000 <= mplus_score <= 3000` are displayed

#### Scenario: User sets only maximum score
**Given** a guild roster with characters ranging from 0 to 3500 score
**When** the user sets maximum score to 1000
**Then** only characters with `mplus_score <= 1000` are displayed

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
The roster display MUST provide sorting options to order characters by Mythic+ score.

#### Scenario: User sorts by score descending
**Given** characters with scores 3500, 1200, 2800
**When** the user selects "Score (High to Low)" from the sort dropdown
**Then** characters are re-ordered to: 3500, 2800, 1200

#### Scenario: User sorts by score ascending
**Given** characters with scores 3500, 1200, 2800
**When** the user selects "Score (Low to High)" from the sort dropdown
**Then** characters are re-ordered to: 1200, 2800, 3500

#### Scenario: Sorting respects active filters
**Given** 10 characters, filtered to show 5 DPS characters
**When** the user sorts by score descending
**Then** only the 5 visible DPS characters are re-ordered
**And** hidden characters remain hidden

### Requirement: Clear All Filters
The roster display MUST provide a "Clear" button to reset all filters and sorting to default state.

#### Scenario: User clears all filters
**Given** active filters: Role="DPS", Name="aid", MinScore=2000, Sort="Score Desc"
**When** the user clicks the "Clear" button
**Then** all filter inputs are reset to default values
**And** all characters are displayed
**And** characters return to default order

### Requirement: Filter State Persistence During Refresh
Filter state MUST be preserved when async data refresh occurs.

#### Scenario: Filters persist during async refresh
**Given** active filters showing 10 of 50 characters
**When** async data refresh triggers and new HTML is injected
**Then** the same filters are automatically re-applied
**And** the character count may update based on new data
**And** the user's filter selection remains active

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

## Cross-References
- Related to existing capability: **Roster Display** (character cards, async refresh)
- Depends on: **Character Profile Hydration** (must have role data to filter by it)
- Enhances: **User Experience** (finding specific characters in large guilds)
