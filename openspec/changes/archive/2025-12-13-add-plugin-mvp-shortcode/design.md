## Contexte
Ce change introduit le premier “slice” fonctionnel du plugin: un shortcode qui affiche le roster de guilde en interrogeant Raider.IO, avec cache et tolérance aux pannes.

## Objectifs / Non-Objectifs
- **Objectifs**:
  - Affichage front “server-rendered” via shortcode `[gmpr_guild]`.
  - Réduire drastiquement les appels externes grâce au cache (transients).
  - Gestion d’erreurs claire côté utilisateur et logs exploitables côté admin (si WP_DEBUG activé).
- **Non-Objectifs**:
  - UI admin de configuration.
  - Endpoints REST publics.

## Décisions
- **Configuration sans UI admin (v1)**:
  - **API key**: fournie côté serveur via `GMPR_RAIDERIO_API_KEY` (constante) **ou** via un filtre WordPress (ex: `gmpr_raiderio_api_key`) pour permettre une injection depuis un gestionnaire de secrets.
  - **Region/realm/guild**: fournis via attributs de shortcode (priorité) avec fallback sur constantes optionnelles (`GMPR_REGION`, `GMPR_REALM`, `GMPR_GUILD`) si définies.
  - **Justification**: évite de stocker un secret en DB sans UI; permet un MVP immédiatement utilisable et sécurisable.

- **Cache transients**:
  - La réponse normalisée (ou brute + mapping) est stockée dans un transient `gmpr_raiderio_guild_<hash>`.
  - Le hash inclut `region`, `realm_slug`, `guild_normalized` + éventuellement “fields” demandés.
  - TTL par défaut (ex: 10–30 min) + paramètre optionnel du shortcode (borné) pour faciliter le tuning.

- **Stale cache**:
  - Si le cache est expiré mais disponible (ex: en gardant un second transient “stale”), on peut afficher ces données avec un avertissement plutôt que de rendre une erreur bloquante.
  - **Justification**: meilleure UX lors d’indisponibilité Raider.IO.

## Alternatives considérées
- **Mettre la clé API dans les attributs du shortcode**:
  - Rejeté: risque d’exposition (contenu visible en DB, logs, HTML, etc.).
- **Créer directement une page Settings**:
  - Rejeté pour le MVP (option B), gardé pour un futur change.

## Risques / Trade-offs
- Sans UI admin, la configuration est moins “user-friendly”.
- Les attributs du shortcode doivent être strictement validés pour éviter des appels non désirés et des collisions de cache.

## Plan de migration
- Futur change: page settings (stockage via options WP) + migration possible des constantes/shortcode vers une config globale.

## Questions ouvertes
- Endpoint Raider.IO exact et “fields” nécessaires (à confirmer au démarrage de l’implémentation).
- Politique TTL cible (par défaut) selon le rate limiting observé.


