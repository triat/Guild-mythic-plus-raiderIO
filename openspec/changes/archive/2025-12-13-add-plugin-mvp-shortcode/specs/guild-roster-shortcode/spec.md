## ADDED Requirements

### Requirement: Shortcode roster de guilde
Le système SHALL fournir un shortcode WordPress `[gmpr_guild]` qui affiche une liste de membres d’une guilde World of Warcraft avec au minimum:
- le nom du personnage,
- le score Mythic+ (Raider.IO),
- un lien vers le profil Raider.IO.

#### Scenario: Rendu nominal
- **WHEN** un éditeur ajoute `[gmpr_guild]` dans une page et que la configuration est valide (region/realm/guild) et que Raider.IO répond avec succès
- **THEN** la page rend une table responsive contenant les membres et leurs champs minimums

#### Scenario: Paramètres invalides
- **WHEN** `[gmpr_guild]` est rendu avec une region/realm/guild invalides ou manquants
- **THEN** le plugin rend un message d’erreur non-technique et n’effectue pas d’appel externe

### Requirement: Configuration sécurisée de la clé API
Le système MUST lire la clé API Raider.IO uniquement côté serveur et MUST NOT exposer la clé API dans le HTML rendu, les attributs de shortcode, ni les URLs publiques.

#### Scenario: Clé API fournie via constante ou filtre
- **WHEN** la constante `GMPR_RAIDERIO_API_KEY` est définie (ou qu’un filtre fournit une clé)
- **THEN** le client Raider.IO utilise cette clé pour authentifier les requêtes sortantes sans la refléter dans la réponse HTML

#### Scenario: Clé API absente
- **WHEN** aucune clé API n’est disponible
- **THEN** le plugin rend un message d’erreur clair et n’effectue pas d’appel externe

### Requirement: Cache via transients
Le système SHALL mettre en cache les résultats de Raider.IO via des transients WordPress afin de réduire les appels externes.

#### Scenario: Cache hit
- **WHEN** `[gmpr_guild]` est rendu et qu’un transient valide existe pour (region, realm, guild)
- **THEN** le plugin utilise le cache et ne fait pas de requête HTTP externe

#### Scenario: Cache miss
- **WHEN** `[gmpr_guild]` est rendu et qu’aucun cache valide n’existe
- **THEN** le plugin effectue une requête HTTP externe, normalise la réponse, et stocke le résultat en cache

### Requirement: Tolérance aux pannes et stale cache
Le système SHALL gérer les erreurs réseau/HTTP Raider.IO et SHALL afficher un fallback basé sur un cache “stale” lorsque disponible.

#### Scenario: Raider.IO indisponible avec cache stale
- **WHEN** Raider.IO retourne une erreur (timeout, DNS, 5xx) et qu’un cache “stale” existe
- **THEN** le plugin affiche les données stale avec un avertissement discret

#### Scenario: Raider.IO indisponible sans cache
- **WHEN** Raider.IO retourne une erreur (timeout, DNS, 5xx) et qu’aucun cache n’existe
- **THEN** le plugin affiche un message d’erreur non-technique


