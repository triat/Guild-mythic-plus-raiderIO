## 1. Spécification
- [x] 1.1 Mettre à jour `specs/guild-roster-shortcode/spec.md` via deltas pour couvrir les comportements manquants (scores par personnage, refresh, limite).
- [x] 1.2 Vérifier que chaque requirement modifiée a au moins un `#### Scenario:`.

## 2. Validation OpenSpec
- [x] 2.1 Valider le change: `openspec validate update-guild-roster-shortcode-behavior --strict`
- [x] 2.2 Vérifier le delta parsé: `openspec show update-guild-roster-shortcode-behavior --json --deltas-only`


