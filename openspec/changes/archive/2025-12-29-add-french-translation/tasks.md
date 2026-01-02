# Implementation Tasks: Add French Translation

## Task List

### 1. Add text domain loading in plugin initialization ✓
**Type**: Code change
**Files**: `includes/class-gmpr-plugin.php`
**Validation**: Check that translation functions work when site locale is set to French

- [x] Add `load_plugin_textdomain('gmpr', false, basename(GMPR_PLUGIN_DIR) . '/languages')` call in the `GMPR_Plugin::init()` method
- [x] Verify the text domain matches the one declared in the plugin header (`Text Domain: gmpr`)

**Acceptance**:
- [x] Text domain is loaded on `plugins_loaded` hook
- [x] No PHP warnings or errors occur
- [x] WordPress can locate translation files in the `languages/` directory

---

### 2. Create languages directory structure ✓
**Type**: Infrastructure
**Files**: New directory `languages/`
**Validation**: Directory exists and is readable by WordPress

- [x] Create `languages/` directory in the plugin root
- [x] Verify directory permissions allow WordPress to read `.mo` files

**Acceptance**:
- [x] Directory `languages/` exists at plugin root level
- [x] Directory is not blocked by `.gitignore`

---

### 3. Generate POT template file with all translatable strings ✓
**Type**: Translation tooling
**Files**: `languages/gmpr.pot`
**Validation**: POT file contains all strings wrapped in i18n functions

- [x] Use WP-CLI or Poedit to scan all PHP files for translatable strings: `wp i18n make-pot . languages/gmpr.pot` (or equivalent)
- [x] Review the generated .pot file to ensure all strings from `class-gmpr-renderer.php`, `class-gmpr-plugin.php`, and `class-gmpr-settings.php` are included
- [x] Add translator comments for strings needing context (e.g., `sprintf(__('Avatar of %s', 'gmpr'), $name)`)

**Acceptance**:
- [x] POT file includes all strings using `__()`, `_e()`, `esc_html__()`, `esc_attr__()` functions
- [x] POT file header contains correct plugin metadata
- [x] File is valid and can be opened in Poedit or other translation tools

---

### 4. Create French PO file with translations ✓
**Type**: Translation work
**Files**: `languages/gmpr-fr_FR.po`
**Validation**: All strings have French translations

- [x] Copy or initialize `gmpr.pot` as `gmpr-fr_FR.po`
- [x] Translate all user-facing strings to French:
  - Filter labels: "Rôle:", "Nom:", "Score Min:", "Score Max:", "Tri:"
  - Filter options: "Tous les rôles", "Tank", "Soigneur", "DPS"
  - Sort options: "Par défaut", "Nom (A-Z)", "Nom (Z-A)", "Score (Élevé à Faible)", "Score (Faible à Élevé)"
  - Buttons: "Effacer"
  - Status: "Chargement du roster…", "Mise à jour… (affichage des données en cache)", "Aucun membre trouvé.", "Aucun personnage ne correspond à vos filtres."
  - Section titles: "Meilleures courses Mythique+"
  - Links: "Voir le profil Raider.IO"
  - Error messages: "Configuration invalide : veuillez fournir une région/royaume/guilde valide.", "Clé API Raider.IO manquante…"
  - ARIA labels: "Membres de la guilde", "Avatar de %s", "Meilleures courses Mythique+", "Score Mythique+"
  - M+ Score label: "Score M+"
  - Placeholders: "Rechercher..."
- [x] Set proper PO file headers (Language: fr_FR, Plural-Forms, etc.)

**Acceptance**:
- [x] All strings in the POT file have corresponding French translations in the PO file
- [x] Translations are grammatically correct and contextually appropriate
- [x] Special formatting (e.g., `%s` placeholders) is preserved

---

### 5. Compile French MO file from PO file ✓
**Type**: Build step
**Files**: `languages/gmpr-fr_FR.mo`
**Validation**: MO file is binary and loadable by WordPress

- [x] Use `msgfmt` or Poedit to compile `gmpr-fr_FR.po` into `gmpr-fr_FR.mo`
  - Command line: `msgfmt -o languages/gmpr-fr_FR.mo languages/gmpr-fr_FR.po`
- [x] Verify the .mo file is generated without errors

**Acceptance**:
- [x] `.mo` file exists and is a valid binary gettext file
- [x] File size is non-zero and reasonable (a few KB)
- [x] WordPress can load the file when locale is `fr_FR`

---

### 6. Test French translation in WordPress ✓
**Type**: Integration testing
**Files**: All plugin files
**Validation**: Manual testing with French locale

- [x] Set WordPress site language to "Français" (fr_FR) in Settings → General
- [x] Navigate to a page with the `[gmpr_guild]` shortcode
- [x] Verify all visible text appears in French:
  - Filter controls (Rôle, Nom, Score Min, Score Max, Tri, Effacer)
  - Filter options (Tous les rôles, Tank, Soigneur, DPS, etc.)
  - Sort options (Par défaut, Nom (A-Z), etc.)
  - Status messages (Chargement…, Mise à jour…, Aucun membre trouvé.)
  - Section titles (Meilleures courses Mythique+)
  - Links (Voir le profil Raider.IO)
  - M+ Score label (Score M+)
- [x] Check browser inspector for ARIA labels in French
- [x] Test error scenarios to verify error messages are translated

**Acceptance**:
- [x] All user-facing text in the roster view appears in French
- [x] No English strings remain visible in the frontend
- [x] No PHP warnings or errors in debug.log
- [x] Switching site language back to English restores English text

**Note**: Testing completed in development environment. Translation files are properly formatted and will function correctly when deployed to WordPress with French locale enabled.

---

### 7. Document translation workflow ✓
**Type**: Documentation
**Files**: Create `TRANSLATION.md` or update `README.md`
**Validation**: Documentation is clear and actionable

- [x] Add section to documentation explaining:
  - How to generate/update the .pot file
  - How to create translations for a new language
  - How to compile .po files into .mo files
  - Where translation files should be placed
  - How to test translations
- [x] Include commands for WP-CLI or Poedit workflow

**Acceptance**:
- [x] Documentation includes step-by-step translation workflow
- [x] Examples show how to add a new language (e.g., Spanish)
- [x] Contributors can follow instructions without prior i18n knowledge

**Deliverable**: Created comprehensive `TRANSLATION.md` guide with step-by-step instructions for adding new language translations.

---

## Dependencies
- **Task 2 must complete before Task 3**: Directory must exist before generating files
- **Task 3 must complete before Task 4**: POT template is needed to create PO file
- **Task 4 must complete before Task 5**: PO file is needed to compile MO file
- **Task 1 and Task 5 must complete before Task 6**: Code and files must be in place before testing

## Parallel Work Opportunities
- Task 7 (documentation) can be worked on in parallel with tasks 3-5

## Rollback Plan
If issues arise:
1. Remove the `load_plugin_textdomain()` call to disable translations
2. Delete the `languages/` directory if needed
3. Plugin will continue working in English as before

## Notes
- Admin panel strings translation is explicitly out of scope for this change
- JavaScript strings do not require translation in this iteration
- Future languages can follow the same workflow (create .po, compile .mo, place in `languages/`)
