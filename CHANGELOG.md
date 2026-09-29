# Changelog

## [Non publié] - Dons, capacités, PV actuels et passage du code en français

### Ajouté

- **Dons et capacités de classe** : tables `dons` et `capacites` (reliées aux personnages par `personnage_don` / `personnage_capacite`), `DonSeeder` (19 dons) et `CapaciteSeeder` (88 capacités, niveaux 1 à 5). Les capacités sont synchronisées à la création, à la montée de niveau et à l'édition ; leur description s'affiche au survol.
- **PV actuels** (`point_de_vie_actuel`) : `point_de_vie` reste le maximum. La fiche affiche « actuels / maximum » et permet d'appliquer des dégâts ou des soins ; les potions agissent sur les PV actuels, bornés entre 0 et le maximum.
- `PersonnagePolicy` appliquée à toutes les routes `/personnages/{personnage}/…` par le middleware `can:gerer,personnage`.
- `ModifierPersonnageRequest` et `AjouterObjetPersonnaliseRequest` (validation sortie des contrôleurs), `SortService`, `CalculCreationService`.
- Messages de validation en français (`lang/fr`), limitation des tentatives de connexion et d'inscription.
- Test `CoherenceIdsHtmlTest` : tout identifiant HTML utilisé par le JavaScript doit exister.

### Modifié

- Le niveau et les caractéristiques ne changent plus depuis le formulaire d'édition : uniquement par l'écran de montée de niveau, qui exige l'expérience nécessaire et enchaîne si plusieurs niveaux sont disponibles.
- Colonnes des tables de magie en `snake_case` (`id_Personnage` → `personnage_id`, `SortConnue` → `sorts_prepares`, etc.) ; table `sousclasse_sorts` renommée `sous_classe_sorts`.
- Tout le code nommé en français : classes, méthodes, variables, JavaScript, routes non REST, sections Blade, ids HTML et classes CSS.
- Styles en ligne de la fiche, de la montée de niveau et de la page des traits déplacés dans `app.css`.
- `public/storage` n'est plus versionné (voir `storage:link` dans le README).

### Corrigé

- Changer de classe réinitialise les sorts du personnage.
- Les routes des sorts ne vérifiaient pas le propriétaire du personnage.
- Une potion modifiait le maximum de PV de façon permanente.
- Consommer un objet se fait désormais dans une transaction.
- Unicité des capacités par classe / sous-classe garantie en base.

### Retiré

- API `/api/personnages`, qui contournait les règles de création.
- Code mort : vue `sorts/index`, `FicheDePersonnageService::traits()`, requêtes « niveau seul » et « PV seuls », classes CSS inutilisées.

## [Non publié] - Correction de l'édition de personnage (sous-race, sous-classe, historique)

### Corrigé

- **Sous-race/sous-classe toujours effacées à la sauvegarde** : `PersonnageSheetController::update()` comparait `race_id`/`classe_id` avec `!==` (typage strict), alors que les requêtes HTTP envoient toujours ces valeurs en chaînes de caractères. Résultat : la comparaison échouait systématiquement et `sous_race_id`/`sous_classe_id` étaient remis à `null` à chaque sauvegarde, même sans changement de race/classe. Remplacé par des comparaisons `(int)`.
- **Toute sauvegarde du formulaire d'édition échouait** : le contrôleur écrivait encore dans les colonnes `outil` et `equipement` de la table `personnages`, supprimées par des migrations ultérieures (équipement déplacé vers `personnage_objets`). Chaque `PATCH /personnages/{id}` levait donc une erreur SQL. Retiré ces écritures du contrôleur et nettoyé `#[Fillable]`/`casts()` sur `App\Models\Personnage`.
- **Page d'édition inutilisable (erreur 500)** : des marqueurs de conflit Git non résolus (`<<<<<<< HEAD` / `=======` / `>>>>>>> develop`) subsistaient dans `edit.blade.php`, référençant une variable `$outils` inexistante. Conflit résolu en gardant la version qui délègue la gestion de l'équipement à l'inventaire.
- **Bouton "Sauvegarder" ne faisait rien** : un `<form>` imbriqué dans le formulaire principal (ancien champ "Niveau" en lecture seule, lui aussi un reliquat de fusion) fermait prématurément le formulaire principal côté navigateur ; tout ce qui suivait (race, sous-race, bouton Sauvegarder...) se retrouvait hors formulaire. Formulaire imbriqué supprimé.
- **Sous-race non affichée/modifiable à l'édition** : le select de sous-race dépendait uniquement d'un `fetch()` JS déclenché après le chargement de la page ; en cas d'échec réseau (ou avant même le premier essai), il restait vide malgré une valeur correcte en base. Les options sont maintenant aussi rendues côté serveur (comme pour la race), préselectionnées ; le JS ne recharge qu'au changement réel de race/classe.
- **Script JS entier silencieusement cassé** : `{{ json_encode(...) }}` dans un bloc `<script>` fait échapper les guillemets en HTML (`&quot;`), ce qui produit une erreur de syntaxe JS dès que la valeur est vide/nulle (`sous_race_id`/`sous_classe_id` à `null`) — et empêche alors l'exécution de **tout** le script de la page (dont le filtrage par historique). Remplacé par `@json(...)`.
- **Filtrage des listes liées à l'historique non fiable** : `option.hidden`/`option.disabled` ne masquent pas réellement les `<option>` dans un `<select>` natif sur tous les navigateurs (Firefox notamment les affiche quand même). Le filtrage reconstruit maintenant la liste d'options à partir d'une copie mémorisée au chargement, au lieu de simplement les masquer.
- Tests `finfo`/`GD`/`pdo_sqlite` : extensions PHP requises par la suite de tests activées localement (`php.ini`), sans quoi les tests d'upload de photo et toute la suite `RefreshDatabase` échouaient pour des raisons d'environnement plutôt que de code.

### Ajouté

- Blocage de la sous-classe si le niveau du personnage est insuffisant : select désactivé + message "Sous-classe disponible au niveau X" dans `edit.blade.php`, et rejet côté serveur avec une vraie erreur de validation (`sous_classe_id`) au lieu d'un effacement silencieux.
- Filtrage strict des champs Idéal/Défaut/Attachement/Trait de personnalité selon l'historique choisi (comme à la création du personnage) : ces selects sont désactivés tant qu'aucun historique n'est choisi, puis ne montrent que les options de l'historique sélectionné.
- Tests de régression dans `tests/Feature/PersonnageSheetTest.php` : persistance de la sous-race malgré un `race_id` de type chaîne, affichage/présélection de la sous-race à l'édition, blocage de la sous-classe par niveau, refus serveur d'une sous-classe trop haute pour le niveau.

### Modifié

- `niveau` accepte désormais le niveau actuel du personnage **ou** le niveau suivant (au lieu du niveau actuel uniquement), pour permettre une montée de niveau avec amélioration de caractéristiques (ASI) directement depuis le formulaire d'édition.
