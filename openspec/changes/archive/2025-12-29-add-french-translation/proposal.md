# Add French Translation

## Summary
Add French language support to the Guild Mythic+ Raider.IO WordPress plugin to display all frontend text in French, following WordPress internationalization (i18n) best practices.

## Motivation
The plugin currently uses WordPress i18n functions (`__()`, `_e()`, `esc_html__()`, etc.) throughout the codebase with the text domain `gmpr`, but:

1. The plugin does not load the text domain, preventing translations from being applied
2. No French translation files exist in the project
3. French-speaking users see only English text in the frontend

Adding French translation will:
- Make the plugin accessible to French-speaking World of Warcraft communities
- Demonstrate proper WordPress i18n implementation for future translations
- Complete the i18n setup already partially implemented in the codebase

## Scope
This change will:
- **Add text domain loading** in the plugin initialization
- **Create French translation files** (.po/.pot/.mo) with complete frontend translations
- **Document the translation workflow** for future language additions

This change will NOT:
- Modify any existing functionality or UI layout
- Change the plugin's English text (source strings)
- Add translation capabilities for admin settings (admin remains English for now)
- Implement dynamic language switching (WordPress's site language setting controls the locale)

## Out of scope
- Translating admin panel strings (future enhancement)
- Adding other languages beyond French (can be added separately)
- Creating a UI for language selection (WordPress handles this via site settings)
- Translating strings from external sources (Raider.IO API returns data in its own format)

## Dependencies
None. This change is self-contained and uses WordPress core i18n functionality.

## Related changes
None. This is the first localization change for the plugin.

## Open questions
1. Should admin panel strings also be translated in this change, or deferred?
   - **Recommendation**: Defer admin translation to keep this change focused on user-facing content
2. Should we provide translation files for both frontend and JavaScript strings separately?
   - **Recommendation**: Start with PHP-based strings; JavaScript localization can be added if needed

## Risks & mitigations
| Risk | Mitigation |
|------|------------|
| Translation errors or missing context | Use clear translator comments and test all UI scenarios |
| Performance impact from loading translation files | WordPress caches translations; impact is negligible |
| Incorrect text domain usage | Audit all i18n function calls to ensure consistent `gmpr` domain |

## Success criteria
- [ ] WordPress site set to French locale displays all plugin strings in French
- [ ] English locale continues to display original English strings
- [ ] All user-facing text in the roster view is translated
- [ ] Translation files are properly formatted and loadable by WordPress
- [ ] No PHP warnings or errors related to i18n functions
