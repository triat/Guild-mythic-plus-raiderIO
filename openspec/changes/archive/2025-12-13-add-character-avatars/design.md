## Context
We currently render roster entries using name/score/profile link. We also render an initials bubble in cards. Raider.IO can provide avatar/thumbnail URLs for characters, but availability is not guaranteed.

## Goals / Non-Goals
- **Goals**:
  - Display character avatar in both inline and cards views.
  - Provide a generic placeholder when the avatar is missing or fails to load.
  - Keep accessibility (alt text) and performance (lazy-loading).
- **Non-Goals**:
  - No user-uploaded avatars.
  - No new admin controls.

## Decisions
- **Data source**: use a character thumbnail/portrait URL from Raider.IO character objects when present (e.g. `thumbnail_url`).
- **Normalized model**: extend member entries with `avatar_url` (string, optional).
- **Fallback**:
  - If `avatar_url` is empty: render a placeholder avatar.
  - If the image fails to load: swap to placeholder (either via `onerror` or a tiny JS handler).
- **Caching**: avatar URLs are cached as part of the same cached payload already used for roster rendering.

## Risks / Trade-offs
- Avatar URLs may be missing, stale, or blocked; the placeholder must keep the UI stable.


