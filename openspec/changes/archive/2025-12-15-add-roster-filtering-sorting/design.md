# Design: Roster Filtering and Sorting

## Architecture Overview

This feature adds **client-side** filtering and sorting to the guild roster without requiring server-side changes beyond fetching additional API data.

```
┌─────────────────────────────────────┐
│   Filter/Sort Toolbar (HTML)       │
│   [Role] [Name] [Score] [Sort]     │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│   JavaScript Filter/Sort Engine     │
│   - Apply filters to cards          │
│   - Re-order cards                  │
│   - Update visibility               │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│   Roster Cards (existing)           │
│   data-role, data-name, data-score  │
└─────────────────────────────────────┘
```

## Data Flow

### 1. Server-Side (PHP)
**Raider.IO API Request**:
- **Current**: `fields=mythic_plus_scores_by_season:current,mythic_plus_best_runs`

**Hydration** (`class-gmpr-plugin.php` and `class-gmpr-async-refresh.php`):
```php
// Extract role from API response
if (isset($char['active_spec_role']) && is_string($char['active_spec_role'])) {
    $members[$i]['active_spec_role'] = strtolower(trim($char['active_spec_role']));
}
```

**Rendering** (`class-gmpr-renderer.php`):
```php
// Add data attributes to each card
$role = isset($m['active_spec_role']) ? strtolower((string) $m['active_spec_role']) : 'unknown';
$out .= '<div class="gmpr-profile-card"
         data-role="' . esc_attr($role) . '"
         data-name="' . esc_attr(strtolower($name)) . '"
         data-score="' . esc_attr($score) . '">';
```

### 2. Client-Side (JavaScript)

**Filter State**:
```javascript
var filterState = {
  role: 'all',        // 'all', 'tank', 'healer', 'dps', 'unknown'
  nameSearch: '',     // text search
  scoreMin: 0,        // minimum score
  scoreMax: 9999,     // maximum score
  sortBy: 'none',     // 'none', 'name-asc', 'name-desc', 'score-asc', 'score-desc'
};
```

**Filter Logic**:
```javascript
function applyFilters() {
  var cards = document.querySelectorAll('.gmpr-profile-card');

  cards.forEach(function(card) {
    var role = card.getAttribute('data-role') || 'unknown';
    var name = card.getAttribute('data-name') || '';
    var score = parseInt(card.getAttribute('data-score') || '0');

    var visible = true;

    // Role filter
    if (filterState.role !== 'all' && role !== filterState.role) {
      visible = false;
    }

    // Name search
    if (filterState.nameSearch && !name.includes(filterState.nameSearch.toLowerCase())) {
      visible = false;
    }

    // Score range
    if (score < filterState.scoreMin || score > filterState.scoreMax) {
      visible = false;
    }

    card.style.display = visible ? '' : 'none';
  });

  applySorting();
}
```

**Sort Logic**:
```javascript
function applySorting() {
  if (filterState.sortBy === 'none') return;

  var container = document.querySelector('.gmpr-roster-list');
  var cards = Array.from(container.querySelectorAll('.gmpr-profile-card:not([style*="display: none"])'));

  cards.sort(function(a, b) {
    if (filterState.sortBy.startsWith('name-')) {
      var nameA = a.getAttribute('data-name') || '';
      var nameB = b.getAttribute('data-name') || '';
      return filterState.sortBy === 'name-asc'
        ? nameA.localeCompare(nameB)
        : nameB.localeCompare(nameA);
    }

    if (filterState.sortBy.startsWith('score-')) {
      var scoreA = parseInt(a.getAttribute('data-score') || '0');
      var scoreB = parseInt(b.getAttribute('data-score') || '0');
      return filterState.sortBy === 'score-asc'
        ? scoreA - scoreB
        : scoreB - scoreA;
    }
  });

  cards.forEach(function(card) {
    container.appendChild(card); // Re-append in sorted order
  });
}
```

## UI Components

### Filter Toolbar HTML
```html
<div class="gmpr-filters">
  <div class="gmpr-filter-group">
    <label>Role:</label>
    <select id="gmpr-filter-role">
      <option value="all">All Roles</option>
      <option value="tank">Tank</option>
      <option value="healer">Healer</option>
      <option value="dps">DPS</option>
      <option value="unknown">Unknown</option>
    </select>
  </div>

  <div class="gmpr-filter-group">
    <label>Name:</label>
    <input type="text" id="gmpr-filter-name" placeholder="Search...">
  </div>

  <div class="gmpr-filter-group">
    <label>Min Score:</label>
    <input type="number" id="gmpr-filter-score-min" placeholder="0" min="0" step="100">
  </div>

  <div class="gmpr-filter-group">
    <label>Max Score:</label>
    <input type="number" id="gmpr-filter-score-max" placeholder="9999" min="0" step="100">
  </div>

  <div class="gmpr-filter-group">
    <label>Sort:</label>
    <select id="gmpr-sort-by">
      <option value="none">Default</option>
      <option value="name-asc">Name (A-Z)</option>
      <option value="name-desc">Name (Z-A)</option>
      <option value="score-desc">Score (High to Low)</option>
      <option value="score-asc">Score (Low to High)</option>
    </select>
  </div>

  <button id="gmpr-clear-filters">Clear</button>
</div>
```

### CSS Styling
- Toolbar positioned above the roster
- Responsive: stacks vertically on mobile
- Dark theme matching existing design
- Clear button to reset all filters

## Edge Cases

1. **Missing role data**: Display as "Unknown" role, filterable separately
2. **Missing score**: Treat as 0 for sorting/filtering
3. **No matches**: Show "No characters match your filters" message
4. **All cards filtered out**: Show empty state
5. **Async data refresh**: Re-apply filters after new data loads

## Performance Considerations

- **Initial render**: No performance impact (data attributes are lightweight)
- **Filtering**: O(n) where n = number of cards (fast even for 100+ cards)
- **Sorting**: O(n log n) using native array sort (acceptable for typical guild sizes)
- **DOM manipulation**: Use `display: none` instead of removing elements (preserves event listeners)

## Mobile Responsiveness

- Filter toolbar stacks vertically on small screens
- Inputs/selects use 100% width on mobile
- Clear button remains accessible
- No horizontal scrolling

## Accessibility

- Proper `<label>` associations
- Keyboard navigation for all controls
- ARIA labels where needed
- Screen reader announcements for filter results count

## Future Enhancements (Out of Scope)

- Persist filters in URL query parameters
- Save filter presets
- Filter by class
- Filter by specific dungeons completed
- Multi-select role filter
- Advanced score filters (tyrannical/fortified split)
