## 1. Specs
- [x] 1.1 Create the `gmpr-admin-settings` capability (ADDED requirements + scenarios)
- [x] 1.2 Modify `guild-roster-shortcode` to formalize precedence atts > admin settings > fallback (MODIFIED requirements + scenarios)

## 2. Implementation (apply stage)
- [x] 2.1 Add a “Settings → GMPR” page (Settings API) restricted to `manage_options`
- [x] 2.2 Register the `gmpr_settings` option + sanitization callback (region/realm/guild/ttl/member_limit)
- [x] 2.3 Handle API key as a password field (masked) + keep previous value if the field is empty
- [x] 2.4 Update shortcode config resolution (atts > options > fallback)
- [x] 2.5 Add a “Help” section on the page (e.g. shortcode example, cache/refresh notes)

## 3. Validation
- [x] 3.1 Manual checks:
  - [x] an admin can save config and the shortcode works without attributes
  - [x] shortcode attributes do override admin config
  - [x] the API key is never displayed in plaintext
- [x] 3.2 `openspec validate add-gmpr-admin-settings --strict`


