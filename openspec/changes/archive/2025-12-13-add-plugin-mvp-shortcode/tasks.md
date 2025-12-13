## 1. Implémentation (apply stage)
- [x] 1.1 Créer le squelette du plugin WordPress (fichier principal, préfixes `gmpr_`, activation sans erreurs)
- [x] 1.2 Ajouter le shortcode `[gmpr_guild]` avec parsing/validation stricte des attributs (region/realm/guild + options de rendu)
- [x] 1.3 Implémenter un client Raider.IO via WordPress HTTP API (timeouts, erreurs, codes HTTP)
- [x] 1.4 Normaliser la réponse en un modèle interne minimal (nom, score M+, url Raider.IO, champs optionnels)
- [x] 1.5 Ajouter le cache via transients (clé stable + TTL) et stratégie “stale cache”
- [x] 1.6 Rendu HTML server-side (table responsive) + échappement systématique
- [x] 1.7 Ajouter un CSS minimal pour la table responsive (si nécessaire) + enqueue conditionnel

## 2. Validation
- [x] 2.1 Vérifier manuellement sur un site WordPress local:
  - [x] Page contenant `[gmpr_guild]` affiche la table avec données nominales
  - [x] Cache hit: rechargement ne refait pas d’appel externe
  - [x] Erreur Raider.IO: message clair et fallback stale si possible
  - [x] La clé API n’apparaît nulle part dans le HTML/JS
- [x] 2.2 Documenter l’installation/config sans UI admin (constantes WP / filtre)


