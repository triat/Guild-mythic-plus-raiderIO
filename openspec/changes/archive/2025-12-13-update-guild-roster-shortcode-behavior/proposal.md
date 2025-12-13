# Change: Mettre à jour la spec du shortcode pour refléter le comportement réel (scores, refresh, limite)

## Why
La capability `guild-roster-shortcode` a été créée lors de l’archivage du MVP, mais le code en production a évolué (récupération des scores via endpoint character, paramètre `refresh`, limite temporaire à 20 membres, logging debug). La spec doit redevenir la source de vérité.

## What Changes
- Mettre à jour la spec `guild-roster-shortcode` pour couvrir:
  - la récupération du **score Mythic+** par membre via un endpoint “character profile” (best-effort + cache),
  - le paramètre de shortcode `refresh` (bypass cache) **réservé admin**,
  - la **limite temporaire** (20 membres) et la possibilité de la surcharger via filtre,
  - le comportement de normalisation des identifiants (ex: suppression suffixe `-<id>` dans certains noms).
- Clarifier le “debugging” (logs en mode `WP_DEBUG` sans exposition de secret).

## Non-Goals
- Modifier l’UI/rendu HTML.
- Ajouter une page Settings admin (ce sera un change séparé).

## Impact
- **Affected specs**:
  - `guild-roster-shortcode` (MODIFIED/ADDED requirements)
- **Affected code**: aucun (ce change est un alignement documentaire / spec).


