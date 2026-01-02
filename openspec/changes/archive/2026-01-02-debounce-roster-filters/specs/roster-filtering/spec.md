# roster-filtering Specification Delta

This delta adds debounced input handling to the existing roster filtering requirements.

## MODIFIED Requirements

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

### Requirement: Filter State Persistence During Refresh
Filter state MUST be preserved when async data refresh occurs, including debounce behavior.

#### Scenario: Debounce persists during async refresh
**Given** active filters with pending debounce timers
**When** async data refresh triggers and new HTML is injected
**Then** the debounce logic is re-initialized for the new DOM elements
**And** any in-flight debounce timers from the old DOM are cancelled
**And** new user input creates new debounce timers

## ADDED Requirements

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
