## MODIFIED Requirements

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

#### Scenario: Score Mythic+ best-effort
- **WHEN** le roster de guilde ne contient pas directement les scores Mythic+ par membre
- **THEN** le plugin tente de compléter ces scores via un endpoint “character profile” (best-effort) et laisse le score vide si introuvable

### Requirement: Cache via transients
Le système SHALL mettre en cache les résultats de Raider.IO via des transients WordPress afin de réduire les appels externes.

#### Scenario: Cache hit
- **WHEN** `[gmpr_guild]` est rendu et qu’un transient valide existe pour (region, realm, guild)
- **THEN** le plugin utilise le cache et ne fait pas de requête HTTP externe

#### Scenario: Cache miss
- **WHEN** `[gmpr_guild]` est rendu et qu’aucun cache valide n’existe
- **THEN** le plugin effectue une requête HTTP externe, normalise la réponse, et stocke le résultat en cache

#### Scenario: Cache par personnage (score)
- **WHEN** le plugin complète les scores via des appels “character profile”
- **THEN** le plugin met en cache les scores par personnage afin d’éviter des appels répétés

## ADDED Requirements

### Requirement: Refresh admin-only
Le système SHALL supporter un paramètre de shortcode `refresh` qui force un rechargement (bypass du cache) et MUST restreindre cette capacité aux utilisateurs disposant des droits d’administration.

#### Scenario: Refresh effectué par admin
- **WHEN** un administrateur connecté rend `[gmpr_guild refresh="1"]`
- **THEN** le plugin ignore le cache (guilde et personnages) et refait les appels externes

#### Scenario: Refresh ignoré pour non-admin
- **WHEN** un utilisateur non-admin rend `[gmpr_guild refresh="1"]`
- **THEN** le plugin se comporte comme si `refresh` était absent

### Requirement: Limite temporaire du nombre de membres
Le système SHALL limiter le nombre de membres affichés à une valeur par défaut de 20 afin de réduire les temps de réponse, et SHOULD permettre de surcharger cette limite via un filtre WordPress.

#### Scenario: Limite par défaut appliquée
- **WHEN** la guilde contient plus de 20 membres
- **THEN** le plugin affiche uniquement les 20 premiers membres selon l’ordre interne utilisé

#### Scenario: Limite surchargée via filtre
- **WHEN** un site définit un filtre `gmpr_member_limit` retournant une valeur N
- **THEN** le plugin limite l’affichage à N membres

### Requirement: Normalisation des identifiants personnage
Le système SHALL normaliser le nom de personnage issu du roster avant d’appeler l’endpoint “character profile”.

#### Scenario: Suppression suffixe technique
- **WHEN** un nom de personnage contient un suffixe technique de type `-<id>` (ex: `Cielã-267166348`)
- **THEN** le plugin utilise uniquement le nom (ex: `Cielã`) pour la requête “character profile”

### Requirement: Logging de debug sans secret
Le système SHOULD produire des logs de debug lorsque `WP_DEBUG` est actif, et MUST NOT inclure la clé API dans les logs.

#### Scenario: Erreur HTTP logguée
- **WHEN** un appel Raider.IO échoue
- **THEN** le plugin loggue le statut et un extrait de réponse sans exposer la clé API


