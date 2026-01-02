# Translation Guide

This document explains how to translate the Guild Mythic+ Raider.IO plugin into other languages.

## Overview

The plugin uses WordPress's internationalization (i18n) system to support multiple languages. Translation files are stored in the `languages/` directory.

## File Types

- **`.pot` (Template)**: The master template containing all translatable strings
- **`.po` (Translation)**: Human-readable translation file for a specific language
- **`.mo` (Compiled)**: Binary file that WordPress loads at runtime

## Current Translations

- **French (fr_FR)**: Complete frontend translation ✓
- **English**: Default language (source strings)

## Adding a New Language

### Prerequisites

You'll need one of these tools:
- **msgfmt** (command-line): Usually part of the `gettext` package
- **Poedit** (GUI): Free translation editor available at https://poedit.net/

### Step 1: Create a PO file

Copy the template file and rename it with your language code:

```bash
cp languages/gmpr.pot languages/gmpr-{LOCALE}.po
```

**Common locale codes:**
- Spanish (Spain): `es_ES`
- German: `de_DE`
- Italian: `it_IT`
- Portuguese (Brazil): `pt_BR`
- Japanese: `ja`

Example for Spanish:
```bash
cp languages/gmpr.pot languages/gmpr-es_ES.po
```

### Step 2: Edit the PO file headers

Open the `.po` file and update the header section:

```po
"Project-Id-Version: Guild Mythic+ Raider.IO 0.1.3\n"
"PO-Revision-Date: 2025-12-29 12:00+0000\n"
"Last-Translator: Your Name <your.email@example.com>\n"
"Language-Team: Spanish\n"
"Language: es_ES\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Plural-Forms: nplurals=2; plural=(n != 1);\n"
```

**Important**: Update the `Plural-Forms` line according to your language's rules. See https://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html

### Step 3: Translate the strings

#### Using Poedit (Recommended for beginners)

1. Install Poedit from https://poedit.net/
2. Open your `.po` file in Poedit
3. Translate each string in the GUI
4. Save the file (Poedit will automatically compile the `.mo` file)

#### Using a text editor

Edit the `.po` file and fill in the `msgstr` values:

```po
#: includes/class-gmpr-renderer.php:84
msgid "Role:"
msgstr "Rol:"

#: includes/class-gmpr-renderer.php:86
msgid "All Roles"
msgstr "Todos los roles"
```

**Translation tips:**
- Preserve placeholders like `%s` exactly as they appear
- Keep HTML entities unchanged (e.g., `&hellip;` for `…`)
- Match punctuation style (colons, periods) to your language's conventions
- Test your translations in context when possible

### Step 4: Compile the MO file

If using a text editor, compile the `.mo` file:

```bash
msgfmt -o languages/gmpr-{LOCALE}.mo languages/gmpr-{LOCALE}.po
```

Example:
```bash
msgfmt -o languages/gmpr-es_ES.mo languages/gmpr-es_ES.po
```

### Step 5: Test your translation

1. Copy the plugin to your WordPress installation
2. Go to **Settings → General** in WordPress admin
3. Change **Site Language** to your language
4. Navigate to a page with the `[gmpr_guild]` shortcode
5. Verify all text appears in your language

## Updating Translations

When the plugin is updated with new strings:

### Step 1: Update the POT file

If you have WP-CLI installed:

```bash
wp i18n make-pot . languages/gmpr.pot
```

Or manually extract strings using `xgettext` or similar tools.

### Step 2: Merge new strings into PO files

```bash
msgmerge --update languages/gmpr-fr_FR.po languages/gmpr.pot
msgmerge --update languages/gmpr-es_ES.po languages/gmpr.pot
# ... for each language
```

### Step 3: Translate new strings

Open each `.po` file and translate any new strings (marked as "fuzzy" or untranslated).

### Step 4: Recompile MO files

```bash
msgfmt -o languages/gmpr-fr_FR.mo languages/gmpr-fr_FR.po
msgfmt -o languages/gmpr-es_ES.mo languages/gmpr-es_ES.po
```

## Translation Coverage

### User-facing (translated in all languages)

- Filter controls (Role, Name, Score filters)
- Filter options (All Roles, Tank, Healer, DPS)
- Sort options (Default, Name A-Z/Z-A, Score High/Low)
- Status messages (Loading, Updating, No members found)
- Section titles (Best Mythic+ Runs)
- Call-to-action links (View Raider.IO Profile)
- Error messages
- ARIA labels for accessibility

### Admin panel (optional, not yet fully translated)

Admin settings page strings are wrapped in i18n functions but are intentionally not translated in the initial French release. Future translations may include these strings.

## Contributing Translations

If you'd like to contribute a translation:

1. Follow the steps above to create a complete translation
2. Test it thoroughly in WordPress
3. Submit a pull request with:
   - The `.po` file
   - The compiled `.mo` file
   - Updated README listing the new language

## Translation Tools

- **Poedit**: https://poedit.net/ (GUI, free)
- **Lokalize**: https://apps.kde.org/lokalize/ (GUI, Linux)
- **GlotPress**: https://wordpress.org/plugins/glotpress/ (WordPress plugin for collaborative translation)
- **msgfmt/msginit/msgmerge**: Command-line tools from GNU gettext

## Language Resources

- **WordPress Polyglots**: https://make.wordpress.org/polyglots/
- **Plural Forms**: https://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
- **Locale Codes**: https://wpastra.com/docs/complete-list-wordpress-locale-codes/

## Questions?

If you have questions about translation, please open an issue on GitHub: https://github.com/anthropics/guild-mythic-plus-raiderio/issues
