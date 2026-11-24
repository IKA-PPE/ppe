La page Roulette utilise **5 tables SQL**, toutes en lecture seule (`SELECT`). Elle n'écrit jamais dans la base.

| Table | Où | Pourquoi |
|---|---|---|
| `utilisateur` | [RouletteModel.php:36](model/RouletteModel.php#L36) et [ExplorerModel.php:14](model/ExplorerModel.php#L14) | Lire le `budget_mensuel` et l'`id_etablissement` de l'utilisateur connecté |
| `etablissement` | [ExplorerModel.php:14](model/ExplorerModel.php#L14) (`campusDe`) | Trouver le nom, la ville, la latitude et la longitude du campus, pour centrer la recherche des restos |
| `suivre` | [RouletteModel.php:22](model/RouletteModel.php#L22) (`regimesDe`) | Table de liaison : quels régimes l'utilisateur suit |
| `regime` | [RouletteModel.php:22](model/RouletteModel.php#L22) (`regimesDe`) | Le nom du régime (`libelle_regime`, par exemple Halal ou Végétarien) pour filtrer le mode aléatoire |
| `depense` | [RouletteModel.php:45](model/RouletteModel.php#L45) (`budgetParJour`) | Additionner les dépenses du mois en cours, pour calculer le budget qui reste par jour |

Les données qui ne viennent pas de la base :
- **Les restaurants** ne sont pas dans une table SQL. Ils viennent de l'API **OpenStreetMap (Overpass)** dans `ExplorerModel::autour()` ([ExplorerModel.php:28](model/ExplorerModel.php#L28)), puis ils sont gardés en session (`$_SESSION['pres_<id campus>']`).
- **La sélection de la roue** (les restos ajoutés avec le bouton +) est stockée en **session** (`$_SESSION['roulette']`), pas en base. Retirer un resto ou vider la roue ne fait donc aucun `DELETE` en SQL. Le commentaire `// DELETE` au-dessus de `retirer()` dans le modèle peut prêter à confusion pour cette raison.
