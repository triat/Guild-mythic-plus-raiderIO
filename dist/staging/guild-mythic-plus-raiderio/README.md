# Guild-mythic-plus-raiderIO
Extension WordPress (plugin) qui affiche les membres d’une guilde World of Warcraft avec leur score **Raider.IO** (Mythic+), via un shortcode.

## Installation
- Copier ce dépôt (ou son contenu) dans `wp-content/plugins/guild-mythic-plus-raiderio/`
- Activer le plugin **Guild Mythic+ Raider.IO** dans l’admin WordPress

## Configuration (MVP sans UI admin)
La clé API n’est **jamais** passée en attribut de shortcode.

### Option 1 — Constantes dans `wp-config.php` (recommandé)
Ajouter dans `wp-config.php`:

```php
define('GMPR_RAIDERIO_API_KEY', 'votre_cle_api');

// Optionnel: valeurs par défaut si vous ne voulez pas les passer dans le shortcode
define('GMPR_REGION', 'eu');   // eu|us|kr|tw|cn
define('GMPR_REALM', 'dalaran'); // slug
define('GMPR_GUILD', 'Nom de Guilde'); // nom affiché
```

### Option 2 — Filtre WordPress (gestionnaire de secrets)
Vous pouvez injecter la clé via le filtre `gmpr_raiderio_api_key`:

```php
add_filter('gmpr_raiderio_api_key', function ($key) {
  return 'votre_cle_api';
});
```

## Utilisation
Dans une page / un article:

```text
[gmpr_guild region="eu" realm="dalaran" guild="Nom de Guilde"]
```

### Paramètres
- **region**: `eu|us|kr|tw|cn`
- **realm**: royaume (slug)
- **guild**: nom de guilde
- **ttl** (optionnel): TTL du cache en secondes (min 60, max 6h, défaut ~15min)
- **refresh** (optionnel): `1|true|yes` pour **forcer un refresh** (bypass du cache). **Actif uniquement pour un admin connecté** (sécurité).

## Dépannage (voir les erreurs)
Le shortcode affiche volontairement un message “propre” côté utilisateur. Pour voir le détail:

1) Dans `wp-config.php`, activer:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

2) Recharger la page contenant `[gmpr_guild ...]`

3) Consulter:
- **WordPress debug log**: `wp-content/debug.log`
- **Ou** les logs PHP du serveur (selon l’hébergeur: error log Apache/Nginx/PHP-FPM)

Le plugin loggue les erreurs Raider.IO en préfixant les lignes par: `[GMPR] Raider.IO: ...` (sans jamais écrire la clé API).

### Note sur l’auth Raider.IO
Le plugin envoie la clé via le header `Authorization: Bearer <key>` (et non en query string).

