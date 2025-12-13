# Change: Ajouter un MVP de plugin WordPress avec shortcode, fetch Raider.IO et cache

## Why
Permettre un premier usage “end-to-end” : afficher sur une page WordPress la liste des membres d’une guilde World of Warcraft avec leur score Raider.IO, de façon simple et performante (cache), sans configuration admin dans un premier temps.

## What Changes
- Ajouter un **bootstrap de plugin WordPress** (préfixe `gmpr_`, hooks de base).
- Ajouter un **shortcode** `[gmpr_guild]` qui rend une **table responsive** des membres de guilde.
- Intégrer un **client HTTP Raider.IO** via WordPress HTTP API et une couche de **normalisation** des données utiles au rendu.
- Ajouter un **cache via transients WordPress** (TTL configurable) pour réduire les appels externes.
- Ajouter une gestion d’erreurs robuste (timeout, HTTP non-200, guilde introuvable) avec **fallback “stale cache”** quand possible.

## Non-Goals (pour ce change)
- Pas de page **Réglages / Admin UI** (Settings API).
- Pas de filtre “score minimum” (reporté à un futur change).
- Pas de tri/recherche/pagination avancés (progressive enhancement reportée).
- Pas d’internationalisation exhaustive (i18n) au-delà des chaînes critiques (peut venir ensuite).

## Impact
- **Affected specs**:
  - `guild-roster-shortcode` (nouvelle capability)
- **Affected code** (prévisionnel, lors de l’étape “apply”):
  - Nouveau dossier plugin (fichier principal + classes: client Raider.IO, cache, renderer)
  - Assets front minimal (CSS responsive) si nécessaire

## Open Questions
- Stockage de la **clé API Raider.IO** sans admin UI:
  - Proposition: clé fournie via constante `GMPR_RAIDERIO_API_KEY` dans `wp-config.php` (ou via filtre WordPress) et **jamais** via attribut de shortcode.
  - Les paramètres `region/realm/guild` peuvent être fournis par attributs du shortcode (ou constantes), et sont normalisés côté serveur.


