## 1. Specs
- [ ] 1.1 Create the `gmpr-admin-settings` capability (ADDED requirements + scenarios)
- [ ] 1.2 Modify `guild-roster-shortcode` to formalize precedence atts > admin settings > fallback (MODIFIED requirements + scenarios)

## 2. Implementation (apply stage)
- [ ] 2.1 Add a “Settings → GMPR” page (Settings API) restricted to `manage_options`
- [ ] 2.2 Register the `gmpr_settings` option + sanitization callback (region/realm/guild/ttl/member_limit)
- [ ] 2.3 Handle API key as a password field (masked) + keep previous value if the field is empty
- [ ] 2.4 Update shortcode config resolution (atts > options > fallback)
- [ ] 2.5 Add a “Help” section on the page (e.g. shortcode example, cache/refresh notes)

## 3. Validation
- [ ] 3.1 Manual checks:
  - [ ] an admin can save config and the shortcode works without attributes
  - [ ] shortcode attributes do override admin config
  - [ ] the API key is never displayed in plaintext
- [ ] 3.2 `openspec validate add-gmpr-admin-settings --strict`


