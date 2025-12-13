## 1. Specs
- [x] 1.1 Modify `gmpr-roster-ui` to require avatars in inline + cards with a placeholder fallback.
- [x] 1.2 Modify `guild-roster-shortcode` to require exposing an optional `avatar_url` in the normalized member model.

## 2. Implementation (apply stage)
- [x] 2.1 Update Raider.IO normalization to include `avatar_url` when available (e.g. `thumbnail_url`).
- [x] 2.2 Update inline view markup to render the avatar next to the name.
- [x] 2.3 Update cards view markup to render the avatar (replace/augment initials bubble).
- [x] 2.4 Add placeholder rendering and image-failure fallback (stable layout).
- [x] 2.5 Update CSS for avatar sizing/alignment in both views.

## 3. Validation
- [x] 3.1 Manual checks:
  - [x] avatars show up for characters that have them
  - [x] placeholder shows for missing/broken avatars
  - [x] inline and cards layout remain stable
- [x] 3.2 `openspec validate add-character-avatars --strict`


