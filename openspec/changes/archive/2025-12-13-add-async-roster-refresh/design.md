## Context
The roster is rendered server-side via shortcode and populated from Raider.IO with transient caching. When cache is missing/expired, server-side fetching increases TTFB.

## Goals / Non-Goals
- **Goals**:
  - Keep initial render fast by avoiding blocking Raider.IO calls during page rendering whenever possible.
  - Refresh data asynchronously and update the UI after render.
  - Prevent refresh stampedes (rate limit + lock).
  - Do not expose the Raider.IO API key.
- **Non-Goals**:
  - Replacing WordPress transients with an external cache.
  - Adding complex background job infrastructure beyond WordPress core.

## Decisions
- **Refresh strategy**: stale-while-revalidate.
  - If fresh cache exists: render it.
  - If only stale cache exists: render it + trigger background refresh.
  - If no cache exists: render a lightweight “loading” state + trigger background refresh.
- **Background execution**: WP-Cron event (single scheduled) to do the refresh outside the request.
- **Endpoints**:
  - A public endpoint to return the latest cached roster payload for a given shortcode context.
  - A refresh trigger that is rate-limited and uses a lock transient to avoid duplication.
- **UI update**: JS fetches updated roster JSON and swaps the roster contents; local view preference (inline/cards) remains respected.

## Risks / Trade-offs
- WP-Cron depends on traffic; on low-traffic sites the refresh may be delayed.
  - Mitigation: allow an immediate refresh trigger endpoint (still rate-limited).


