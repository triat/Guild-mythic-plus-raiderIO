# Change: Add asynchronous roster refresh (stale-while-revalidate)

## Why
Refreshing data can currently make page loads slow because Raider.IO calls happen during server rendering. We want the page to render quickly and refresh data asynchronously afterwards.

## What Changes
- Adopt a **stale-while-revalidate** approach for roster rendering:
  - Render cached data immediately (even if stale) when available.
  - Trigger background refresh when data is stale or missing.
- Add a lightweight **frontend async refresh** mechanism:
  - The page can request the latest cached roster via a WordPress endpoint.
  - The UI updates after the initial render without a full page reload.
- Add basic stampede protection (lock/rate-limit) so multiple visitors don’t trigger repeated refreshes.

## Non-Goals
- No new data fields from Raider.IO.
- No UI redesign (only loading/updating states if needed).

## Impact
- **Affected specs**:
  - `gmpr-async-refresh` (new capability)
  - `guild-roster-shortcode` (MODIFIED: non-blocking refresh behavior)
  - `gmpr-roster-ui` (MODIFIED: optional “updating” indicator)
- **Affected code** (apply stage):
  - Add an async endpoint (REST API or admin-ajax) to serve cached roster and optionally trigger refresh.
  - Update caching flow to schedule refresh jobs (WP-Cron or immediate non-blocking trigger).
  - Add minimal JS to fetch updated data and update the DOM.


