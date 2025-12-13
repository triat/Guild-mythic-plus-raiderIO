## 1. Specs
- [x] 1.1 Add capability `gmpr-async-refresh` (requirements for SWR, endpoint behavior, rate limiting).
- [x] 1.2 Modify `guild-roster-shortcode` to specify non-blocking refresh semantics (stale rendering + async update).
- [x] 1.3 Modify `gmpr-roster-ui` to specify “updating”/loading states (minimal).

## 2. Implementation (apply stage)
- [x] 2.1 Add a background refresh job (WP-Cron) with lock/rate limit.
- [x] 2.2 Add an endpoint to return the cached roster payload for a given shortcode context.
- [x] 2.3 Add an endpoint/action to trigger refresh (rate-limited; no secrets).
- [x] 2.4 Update shortcode rendering to:
  - [x] render fresh cache when present
  - [x] render stale cache and trigger async refresh when stale
  - [x] render loading state when no cache exists and trigger async refresh
- [x] 2.5 Add JS to fetch updates and replace roster DOM after render.

## 3. Validation
- [x] 3.1 Manual checks:
  - [x] initial page load is fast even on cache miss
  - [x] roster updates after render
  - [x] refresh is rate-limited / no stampede
- [x] 3.2 `openspec validate add-async-roster-refresh --strict`


