# Project Context

## Purpose
Ce dépôt a pour objectif de développer une **extension WordPress** (“plugin”) qui affiche les membres d’une guilde World of Warcraft avec leurs informations **Raider.IO** (notamment le score Mythic+).

- **Objectif principal**: fournir un affichage simple (front) d’une liste de membres (tableau/grille), exploitable sur une page WordPress (ex: via shortcode).
- **Objectifs secondaires**:
  - Réduire les appels externes via cache côté WordPress.
  - Être robuste aux erreurs (API indisponible, guilde introuvable, latence).
  - Respecter les bonnes pratiques WordPress (sécurité, échappement, perf).
  - Permettre (optionnellement) un **filtre de score Raider.IO minimum** pour limiter les membres affichés.

> Note: à ce stade, le dépôt contient surtout de la documentation (pas encore de code applicatif).

## Tech Stack
- **Plateforme**: WordPress (plugin)
- **Langage principal**: PHP (WordPress Core APIs)
- **Frontend (optionnel)**: HTML/CSS + JavaScript “vanilla” (si tri/recherche côté client)
- **HTTP**: WordPress HTTP API (`wp_remote_get`, `wp_remote_retrieve_body`, etc.)
- **Cache**: Transients WordPress (ex: `set_transient`, `get_transient`)
- **Licence**: GPLv3 (voir `LICENSE`)
- **Dépendance externe**: API publique Raider.IO (voir section “External Dependencies”)
- **Secret**: clé API Raider.IO (stockée côté serveur via options WordPress; jamais exposée au front)

## Project Conventions

### Code Style
- **PHP / WordPress**:
  - Suivre les conventions WordPress (WordPress Coding Standards) autant que possible.
  - Préfixer tout (fonctions, options, handles de scripts/styles) pour éviter les collisions, ex: `gmpr_...`.
  - Échapper systématiquement à l’output (`esc_html`, `esc_attr`, `esc_url`) et sanitiser à l’entrée (`sanitize_text_field`, `sanitize_key`, etc.).
  - Ne jamais faire confiance aux paramètres utilisateurs; valider et normaliser.
- **Nommage**:
  - Options WP: `gmpr_settings`, `gmpr_region`, etc. (préfixe unique).
  - Transients: `gmpr_raiderio_guild_<hash>` (inclure région/royaume/guilde).
- **Normalisation des identifiants**:
  - `region`: valeurs acceptées: `eu`, `us`, `kr`, `tw`, `cn`.
  - `realm`: accepter **slug** ou **titre**; normaliser en slug interne (ex: via `sanitize_title`).
  - `guild`: **insensible à la casse** tout en conservant les accents; normaliser pour les clés/cache avec une baisse de casse UTF-8 (ex: `mb_strtolower(..., 'UTF-8')`) + trimming.
- **Internationalisation (recommandé)**:
  - Prévoir i18n WordPress (`__`, `_e`, domaine texte du plugin) si le plugin est public.

### Architecture Patterns
- **Plugin WordPress “class-first”**:
  - Un fichier principal de plugin qui bootstrap (hooks `init`, `admin_menu`, `wp_enqueue_scripts`).
  - Logique découpée en classes/fichiers (ex: `RaiderIoClient`, `Renderer`, `Settings`).
- **Rendu**:
  - Affichage via **shortcode**: `[gmpr_guild]`.
  - **Guilde unique** configurée globalement (admin): région/realm/guild + clé API + options d’affichage.
  - **Bascule d’affichage**: tableau ↔ cartes, avec rendu **optimisé mobile** (responsive).
- **Données**:
  - Fetch Raider.IO → normalisation → cache → rendu HTML.
  - Éviter les appels multiples: mettre en cache par guilde + TTL.
- **Tolérance aux pannes**:
  - Si l’API est indisponible: afficher un message propre + fallback sur cache existant si possible.
  - “Stale cache” accepté: si le cache est expiré mais disponible, l’afficher avec avertissement plutôt que de ne rien afficher.
 - **Tri / recherche / pagination**:
  - Approche “best practice” WordPress: **rendu serveur** (SEO/accès) + **progressive enhancement JS**.
  - Pagination toujours disponible (utile pour grands rosters).
  - Tri/recherche: contrôles accessibles; JS peut améliorer la réactivité, tout en gardant un fallback serveur.

### Testing Strategy
- **État actuel**: pas de stratégie de test outillée dans le repo (pas de code).
- **Recommandé quand le code arrive**:
  - **Tests unitaires** (PHPUnit) pour le mapping/normalisation de la réponse Raider.IO.
  - **Tests d’intégration** avec la suite de tests WordPress (optionnel).
  - À minima: scripts de validation manuelle + checklist de régression (shortcode, cache, settings).

### Git Workflow
- **Branches**: `main` + branches courtes par feature/fix (`feat/...`, `fix/...`).
- **Commits**: Conventional Commits recommandés (`feat:`, `fix:`, `docs:`, etc.).
- **PR**:
  - Petites PRs, descriptives.
  - Lier aux changements OpenSpec quand pertinent (`openspec/changes/<change-id>/`).

## Domain Context
Ce projet manipule les concepts suivants:
- **Guilde**: identifiée typiquement par `region`, `realm`, `guild name` (selon conventions Raider.IO).
- **Raider.IO**: service fournissant des données publiques (scores, roster, etc.) via endpoints HTTP.
- **Mythic+**:
  - Score/Ratings utilisés pour classer les joueurs.
  - Le rendu inclut a minima: pseudo, score Mythic+, classe/spé, rôle, lien Raider.IO.
- **Raid**:
  - Affichage de la progression raid (format exact à déterminer selon l’endpoint Raider.IO).
- **Item level (ilvl)**:
  - Affichage de l’ilvl si disponible via les données Raider.IO choisies.

Hypothèse: l’objectif est de **lister les membres** et d’afficher au moins un champ de score (Mythic+). Les champs exacts seront confirmés quand on choisira l’endpoint Raider.IO.

## Important Constraints
- **Performance**: ne pas appeler Raider.IO à chaque page view; utiliser cache/TTL.
- **Disponibilité**: gérer timeouts, erreurs HTTP, données partielles.
- **Sécurité WordPress**:
  - Sanitisation/escaping systématique.
  - Nonces + capabilities pour toute action admin.
  - Ne jamais exposer la **clé API** (pas de rendu HTML, pas de JS inline, pas d’endpoint public sans auth qui la reflète).
- **Compatibilité**:
  - **Cibles minimales**: viser les **dernières versions majeures**.
  - **WordPress**: minimum **6.9+** (aligné avec l’environnement actuel du projet).
  - **PHP**: minimum **8.x** (à préciser si besoin, ex: 8.2+).
- **Licence**:
  - Le projet est sous **GPLv3**; éviter des dépendances incompatibles si on ajoute des libs.

## External Dependencies
- **WordPress Core APIs** (HTTP API, Settings API, Shortcodes, Transients, etc.)
- **Raider.IO API**:
  - Utilisée pour récupérer les informations de guilde/joueurs.
  - **Contraintes**: latence, disponibilité, éventuel rate limiting (prévoir backoff + cache).
  - **Authentification**: nécessite une **clé API** (stockée côté serveur).

## Open Questions (à clarifier)
- **PHP min exact**: quelle version 8.x (ex: 8.2+ vs 8.3+) ?
- **Progression raid**: sur le **dernier raid**, afficher la **difficulté la plus haute réalisée** (format à caler sur les champs Raider.IO).
- **Filtre score minimum**: **global (admin) uniquement** pour rester simple.
