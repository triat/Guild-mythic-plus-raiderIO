# Change: Add character avatars to roster UI (inline + cards)

## Why
Avatars make the roster easier to scan and more visually engaging. We want to show each character's avatar both in inline and cards views, with a safe fallback when the avatar is unavailable.

## What Changes
- Fetch and normalize a character avatar URL from Raider.IO data (when available).
- Render the avatar in **both** roster views:
  - Inline view: small circular avatar next to the character name.
  - Cards view: avatar replaces/augments the current initials bubble.
- Use a **generic placeholder** when no avatar is available or when the image fails to load.
- Keep UX best practices: lazy-loading images and accessible alt text.

## Non-Goals
- No new admin settings.
- No change to caching strategy beyond storing the normalized avatar URL alongside existing cached payloads.
- No additional external data sources beyond Raider.IO.

## Impact
- **Affected specs**:
  - `gmpr-roster-ui` (MODIFIED: avatar rendering + fallback)
  - `guild-roster-shortcode` (MODIFIED: normalized member model includes avatar URL)
- **Affected code** (apply stage):
  - `includes/class-gmpr-raiderio-client.php` (include avatar URL in normalization; possibly request required fields)
  - `includes/class-gmpr-renderer.php` (render avatar in inline + cards, placeholder fallback)
  - `assets/gmpr.css` (avatar sizing/alignment)
  - `assets/gmpr.js` (optional: onerror swap to placeholder, if not done purely in markup)


