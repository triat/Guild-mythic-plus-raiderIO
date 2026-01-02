# roster-filtering Specification Delta

This delta changes the default sort order to display members sorted by Raider.IO score (high to low).

## MODIFIED Requirements

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
