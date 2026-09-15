<?php

namespace App\Http\Controllers;

use App\Models\Attachement;
use App\Models\Classe;
use App\Models\Defaut;
use App\Models\Historique;
use App\Models\Ideal;
use App\Models\Personnalite;
use App\Models\Race;
use App\Models\SousClasse;
use App\Models\SousRace;
use App\Services\PersonnageCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CreationPersonnageController extends Controller
{
    protected const SESSION_KEY = 'personnage_creation';

    protected const TOTAL_STEPS = 6;

    protected function getData(): array
    {
        $defaults = [
            'race_id' => null,
            'sous_race_id' => null,
            'classe_id' => null,
            'sous_classe_id' => null,
            'methode' => null,
            'abilites' => [
                'force' => 8,
                'dexterite' => 8,
                'constitution' => 8,
                'intelligence' => 8,
                'sagesse' => 8,
                'charisme' => 8,
            ],
            'dice_pool' => null,
            // Certaines races (Humain Variant, Demi-elfe) accordent des
            // bonus "au choix" plutôt que fixes : le joueur décide lui-même
            // à quelle(s) caractéristique(s) les appliquer. Structure :
            // ['race' => ['force', 'charisme'], 'classe' => [...]].
            'bonus_choix' => [],
            'competences' => [],
            'historique_id' => null,
            'ideal_id' => null,
            'defaut_id' => null,
            'attachement_id' => null,
            'personnalite_id' => null,
            'outil' => null,
            'langues' => [],
            'equipement' => [],
            'nom' => '',
            'alignement' => null,
            'description' => '',
        ];

        return array_merge($defaults, session(static::SESSION_KEY, []));
    }

    protected function saveData(array $data): void
    {
        $current = $this->getData();
        session([static::SESSION_KEY => array_merge($current, $data)]);
    }

    protected function getProgress(): int
    {
        $data = $this->getData();
        $step = 0;

        if (! empty($data['race_id'])) {
            $step = 1;
        }
        if (! empty($data['classe_id'])) {
            $step = 2;
        }
        if (! empty($data['methode'])) {
            $step = 3;
        }
        if ($this->abilitesComplete($data['abilites'] ?? [])) {
            $step = 4;
        }
        if (! empty($data['competences'])) {
            $step = 5;
        }

        return $step;
    }

    protected function abilitesComplete(array $abilites): bool
    {
        return ! empty($abilites) &&
            ($abilites['force'] ?? 0) > 0 &&
            ($abilites['dexterite'] ?? 0) > 0 &&
            ($abilites['constitution'] ?? 0) > 0 &&
            ($abilites['intelligence'] ?? 0) > 0 &&
            ($abilites['sagesse'] ?? 0) > 0 &&
            ($abilites['charisme'] ?? 0) > 0;
    }

    public function stepRace(): View
    {
        $races = Race::with('sousRaces')->get();
        $data = $this->getData();

        return view('personnages.creation.race', [
            'races' => $races,
            'selectedRace' => $data['race_id'],
            'selectedSousRace' => $data['sous_race_id'],
            'currentStep' => 1,
            'progress' => $this->getProgress(),
        ]);
    }

    public function saveRace(Request $request): RedirectResponse
    {
        // Une sous-race ne peut pas rester "optionnelle" quand la race en
        // propose : le joueur doit alors obligatoirement en choisir une.
        $race = Race::with('sousRaces')->find($request->input('race_id'));
        $sousRaceRequise = $race && $race->sousRaces->isNotEmpty();

        $validated = $request->validate([
            'race_id' => 'required|integer|exists:races,id',
            'sous_race_id' => [
                $sousRaceRequise ? 'required' : 'nullable',
                'integer',
                Rule::exists('sous_races', 'id')->where(function ($query) use ($request) {
                    return $query->where('race_id', $request->input('race_id'));
                }),
            ],
        ], [
            'race_id.required' => 'Vous devez choisir une race avant de continuer.',
            'sous_race_id.required' => 'Vous devez choisir une sous-race avant de continuer.',
        ]);

        // $validated vient d'une requête HTTP : ce sont des chaînes, même
        // pour les champs validés par la règle "integer". On les convertit
        // pour stocker des id cohérents en session (comparés ailleurs à des
        // id entiers renvoyés par Eloquent).
        //
        // La règle "exists" ci-dessus vérifie déjà, via une requête SQL
        // scopée sur race_id, que sous_race_id appartient bien à la race
        // envoyée : il n'y a donc rien à revalider ici.
        $newRaceId = (int) $validated['race_id'];
        $newSousRaceId = isset($validated['sous_race_id']) ? (int) $validated['sous_race_id'] : null;

        $this->saveData([
            'race_id' => $newRaceId,
            'sous_race_id' => $newSousRaceId,
        ]);

        return redirect()->route('creation.classe');
    }

    public function stepClasse(): View|RedirectResponse
    {
        $classes = Classe::with('sousClasses')->get();
        $data = $this->getData();

        if (empty($data['race_id'])) {
            return redirect()->route('creation.race');
        }

        return view('personnages.creation.classe', [
            'classes' => $classes,
            'selectedClasse' => $data['classe_id'],
            'selectedSousClasse' => $data['sous_classe_id'],
            'currentStep' => 2,
            'progress' => $this->getProgress(),
        ]);
    }

    public function saveClasse(Request $request): RedirectResponse
    {
        // Une sous-classe ne peut pas rester "optionnelle" quand elle est
        // disponible dès le niveau 1 (niveau auquel un personnage est
        // toujours créé) : le joueur doit alors obligatoirement en choisir
        // une.
        $classe = Classe::with('sousClasses')->find($request->input('classe_id'));
        $sousClasseRequise = $classe
            && $classe->niveau_sous_classe <= 1
            && $classe->sousClasses->isNotEmpty();

        $validated = $request->validate([
            'classe_id' => 'required|integer|exists:classes,id',
            'sous_classe_id' => [
                $sousClasseRequise ? 'required' : 'nullable',
                'integer',
                Rule::exists('sous_classes', 'id')->where(function ($query) use ($request) {
                    return $query->where('classe_id', $request->input('classe_id'));
                }),
            ],
        ], [
            'classe_id.required' => 'Vous devez choisir une classe avant de continuer.',
            'sous_classe_id.required' => 'Vous devez choisir une sous-classe avant de continuer.',
        ]);

        // Mêmes précautions que dans saveRace() : $validated contient des
        // chaînes issues de la requête HTTP, à convertir avant tout usage
        // comme id entier. La règle "exists" ci-dessus garantit déjà que
        // sous_classe_id appartient à la classe envoyée : rien à revalider
        // ici.
        $newClasseId = (int) $validated['classe_id'];
        $newSousClasseId = isset($validated['sous_classe_id']) ? (int) $validated['sous_classe_id'] : null;

        $this->saveData([
            'classe_id' => $newClasseId,
            'sous_classe_id' => $newSousClasseId,
        ]);

        return redirect()->route('creation.methode');
    }

    public function stepMethode(): View|RedirectResponse
    {
        if (empty($this->getData()['classe_id'])) {
            return redirect()->route('creation.classe');
        }

        return view('personnages.creation.methode', [
            'currentStep' => 3,
            'progress' => $this->getProgress(),
        ]);
    }

    public function saveMethode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'methode' => 'required|in:points,standard,dice',
        ]);

        $abilites = match ($validated['methode']) {
            'points' => [
                'force' => 8,
                'dexterite' => 8,
                'constitution' => 8,
                'intelligence' => 8,
                'sagesse' => 8,
                'charisme' => 8,
            ],
            'standard' => [
                'force' => 0,
                'dexterite' => 0,
                'constitution' => 0,
                'intelligence' => 0,
                'sagesse' => 0,
                'charisme' => 0,
            ],
            'dice' => [
                'force' => 0,
                'dexterite' => 0,
                'constitution' => 0,
                'intelligence' => 0,
                'sagesse' => 0,
                'charisme' => 0,
            ],
        };

        $this->saveData([
            'methode' => $validated['methode'],
            'abilites' => $abilites,
            'dice_pool' => null,
        ]);

        return redirect()->route('creation.caracteristiques');
    }

    protected const STANDARD_ARRAY = [15, 14, 13, 12, 10, 8];

    protected const ABILITES_KEYS = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];

    /**
     * Repère, parmi la race, la sous-race et la classe du personnage, celles
     * qui accordent un bonus "au choix" (ex. Humain Variant : 2 bonus de +1
     * à répartir librement) plutôt qu'un bonus fixe. Retourne, pour chaque
     * source concernée, sa configuration ['nombre' => int, 'bonus' => int].
     */
    protected function bonusAuChoixDisponibles(array $data): array
    {
        $race = Race::find($data['race_id']);
        $sousRace = ! empty($data['sous_race_id']) ? SousRace::find($data['sous_race_id']) : null;
        $classe = Classe::find($data['classe_id']);

        $sources = [];
        foreach (['race' => $race, 'sous_race' => $sousRace, 'classe' => $classe] as $cle => $modele) {
            $auChoix = data_get($modele, 'bonus_aptitudes.au_choix');
            if (is_array($auChoix) && ! empty($auChoix['nombre']) && ! empty($auChoix['bonus'])) {
                $sources[$cle] = $auChoix;
            }
        }

        return $sources;
    }

    public function stepCaracteristiques(): View|RedirectResponse
    {
        $data = $this->getData();

        if (empty($data['methode'])) {
            return redirect()->route('creation.methode');
        }

        return view('personnages.creation.caracteristiques', [
            'methode' => $data['methode'],
            'abilites' => $data['abilites'],
            'dicePool' => $data['dice_pool'],
            'standardArray' => static::STANDARD_ARRAY,
            'race' => $race = Race::find($data['race_id']),
            'sousRace' => $sousRace = $data['sous_race_id'] ? SousRace::find($data['sous_race_id']) : null,
            'classe' => $classe = Classe::find($data['classe_id']),
            'bonusAptitudes' => [
                'race' => $race?->bonus_aptitudes ?? [],
                'sous_race' => $sousRace?->bonus_aptitudes ?? [],
                'classe' => $classe?->bonus_aptitudes ?? [],
            ],
            'auChoixDisponibles' => $this->bonusAuChoixDisponibles($data),
            'bonusChoix' => $data['bonus_choix'] ?? [],
            'currentStep' => 4,
            'progress' => $this->getProgress(),
        ]);
    }

    public function saveCaracteristiques(Request $request): RedirectResponse
    {
        $data = $this->getData();
        $methode = $data['methode'];

        $rules = [
            'force' => 'required|integer|min:1|max:30',
            'dexterite' => 'required|integer|min:1|max:30',
            'constitution' => 'required|integer|min:1|max:30',
            'intelligence' => 'required|integer|min:1|max:30',
            'sagesse' => 'required|integer|min:1|max:30',
            'charisme' => 'required|integer|min:1|max:30',
        ];

        if ($methode === 'points') {
            foreach ($rules as $key => &$rule) {
                $rule = 'required|integer|min:8|max:15';
            }
            unset($rule);
        }

        if ($methode === 'standard') {
            $rules['points_disponibles'] = 'nullable|integer';
        }

        $validated = $request->validate($rules);

        $abilites = [
            'force' => (int) $validated['force'],
            'dexterite' => (int) $validated['dexterite'],
            'constitution' => (int) $validated['constitution'],
            'intelligence' => (int) $validated['intelligence'],
            'sagesse' => (int) $validated['sagesse'],
            'charisme' => (int) $validated['charisme'],
        ];

        if ($methode === 'points') {
            $costTable = [8 => 0, 9 => 1, 10 => 2, 11 => 3, 12 => 4, 13 => 6, 14 => 8, 15 => 10];
            $totalCost = array_sum(array_map(fn ($v) => $costTable[$v] ?? 0, $abilites));
            if ($totalCost !== 27) {
                return redirect()->back()
                    ->with('error', 'Vous devez utiliser exactement 27 points (coût progressif : 9=1, 10=2, 11=3, 12=4, 13=6, 14=8, 15=10).');
            }
        }

        // La répartition standard et le lancer de dés attribuent chaque
        // caractéristique en glissant-déposant une valeur d'un lot fixe :
        // on vérifie ici que les valeurs soumises correspondent exactement
        // à ce lot (même multiset), pour empêcher un envoi de formulaire
        // trafiqué depuis le client.
        if ($methode === 'standard') {
            $submitted = array_values($abilites);
            sort($submitted);
            $expected = static::STANDARD_ARRAY;
            sort($expected);
            if ($submitted !== $expected) {
                return redirect()->back()
                    ->with('error', 'La répartition ne correspond pas aux valeurs disponibles (15, 14, 13, 12, 10, 8). Merci de glisser chaque valeur dans une seule caractéristique.');
            }
        }

        if ($methode === 'dice') {
            $pool = $data['dice_pool'];
            if (! is_array($pool) || count($pool) !== 6) {
                return redirect()->route('creation.caracteristiques')
                    ->with('error', 'Vous devez lancer les dés avant de continuer.');
            }
            $submitted = array_values($abilites);
            sort($submitted);
            $expected = $pool;
            sort($expected);
            if ($submitted !== $expected) {
                return redirect()->back()
                    ->with('error', 'La répartition ne correspond pas aux valeurs lancées. Merci de glisser chaque résultat dans une seule caractéristique.');
            }
        }

        // Bonus "au choix" (Humain Variant, Demi-elfe, ...) : le joueur doit
        // indiquer, pour chaque source concernée, exactement "nombre"
        // caractéristiques distinctes parmi les 6 (voir
        // bonusAuChoixDisponibles() et abilitesAvecBonus() qui applique
        // ensuite ces bonus sur le personnage final).
        $auChoixDisponibles = $this->bonusAuChoixDisponibles($data);
        $bonusChoix = [];

        foreach ($auChoixDisponibles as $source => $config) {
            $nombre = (int) $config['nombre'];
            $choix = array_values(array_filter((array) $request->input("bonus_choix_$source", [])));

            if (
                count($choix) !== $nombre
                || count(array_unique($choix)) !== $nombre
                || array_diff($choix, static::ABILITES_KEYS)
            ) {
                return redirect()->back()
                    ->with('error', "Vous devez choisir $nombre caractéristiques différentes pour le bonus au choix.");
            }

            $bonusChoix[$source] = $choix;
        }

        // Les valeurs de la session restent les valeurs attribuées par le
        // joueur. Les bonus (race, sous-race, classe, et les bonus "au
        // choix" une fois assignés) sont ajoutés uniquement au personnage
        // final afin de ne pas fausser la répartition ni son coût.
        $this->saveData(['abilites' => $abilites, 'bonus_choix' => $bonusChoix]);

        return redirect()->route('creation.competences');
    }

    /**
     * Lance les 6 groupes de caractéristiques (4d6, on garde les 3 meilleurs)
     * en une seule fois. Le joueur répartit ensuite librement les 6
     * résultats obtenus par glisser-déposer (voir _caracteristiques_dice).
     */
    public function rollDice(Request $request)
    {
        $rolls = [];
        for ($i = 0; $i < 6; $i++) {
            $dice = [];
            for ($j = 0; $j < 4; $j++) {
                $dice[] = random_int(1, 6);
            }
            rsort($dice);
            array_pop($dice);
            $rolls[] = array_sum($dice);
        }

        $abilitesVides = array_fill_keys(static::ABILITES_KEYS, 0);

        $this->saveData([
            'dice_pool' => $rolls,
            'abilites' => $abilitesVides,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['rolls' => $rolls]);
        }

        return redirect()->back();
    }

    public function stepCompetences(): View|RedirectResponse
    {
        $data = $this->getData();

        if (! $this->abilitesComplete($data['abilites'] ?? [])) {
            return redirect()->route('creation.caracteristiques');
        }

        $classe = Classe::find($data['classe_id']);
        $competencesDisponibles = $classe?->competences_disponibles ?? [];
        $nombreCompetences = $classe?->nombre_competences ?? 2;

        return view('personnages.creation.competences', [
            'classe' => $classe,
            'competencesDisponibles' => $competencesDisponibles,
            'competencesSelectionnees' => array_values(array_intersect($data['competences'] ?? [], $competencesDisponibles)),
            'nombreCompetences' => $nombreCompetences,
            'currentStep' => 5,
            'progress' => $this->getProgress(),
        ]);
    }

    public function saveCompetences(Request $request): RedirectResponse
    {
        $data = $this->getData();
        $classe = Classe::find($data['classe_id']);
        $competencesDisponibles = $classe?->competences_disponibles ?? [];
        $nombreCompetences = $classe?->nombre_competences ?? 2;

        $validated = $request->validate([
            'competences' => ['required', 'array', "size:$nombreCompetences"],
            'competences.*' => ['string', Rule::in($competencesDisponibles)],
        ], [
            'competences.required' => "Vous devez choisir $nombreCompetences compétences avant de continuer.",
            'competences.size' => "Vous devez choisir exactement $nombreCompetences compétences.",
            'competences.*.in' => 'Cette compétence ne fait pas partie de celles offertes par votre classe.',
        ]);

        $this->saveData([
            'competences' => array_values(array_unique($validated['competences'])),
        ]);

        return redirect()->route('creation.contexte');
    }

    public function stepContexte(): View|RedirectResponse
    {
        $data = $this->getData();

        if (empty($data['race_id'])) {
            return redirect()->route('creation.race');
        }

        $historiques = Historique::with(['ideaux', 'defauts', 'attachements', 'personnalites'])->get();
        $selectedHistoriqueModel = $data['historique_id'] ? $historiques->firstWhere('id', $data['historique_id']) : null;

        $race = Race::find($data['race_id']);
        $languesRace = $race->langues ?? ['Commun'];

        return view('personnages.creation.contexte', [
            'data' => $data,
            'historiques' => $historiques,
            'languesRace' => $languesRace,
            'selectedHistorique' => $data['historique_id'],
            'selectedHistoriqueModel' => $selectedHistoriqueModel,
            'selectedIdeal' => $data['ideal_id'],
            'selectedDefaut' => $data['defaut_id'],
            'selectedAttachement' => $data['attachement_id'],
            'selectedPersonnalite' => $data['personnalite_id'],
            'selectedOutil' => $data['outil'],
            'currentStep' => 6,
            'progress' => $this->getProgress(),
        ]);
    }

    public function saveContexte(Request $request): RedirectResponse
    {
        $data = $this->getData();

        // Le bouton "Retour" ramène le joueur à l'étape des compétences sans
        // que cette étape-ci soit terminée : les champs "required" ne
        // peuvent donc pas être exigés ici. On enregistre malgré tout, de
        // façon permissive, ce qui a déjà été rempli/sélectionné, pour que
        // revenir en arrière puis ravancer n'efface pas ces choix (avant ce
        // correctif, rien n'était sauvegardé tant que "Suivant" n'avait pas
        // été soumis avec succès, donc un aller-retour perdait tout).
        if ($request->input('direction') === 'back') {
            $idFields = ['historique_id', 'ideal_id', 'defaut_id', 'attachement_id', 'personnalite_id'];
            $request->merge(collect($idFields)->mapWithKeys(
                fn ($field) => [$field => $request->filled($field) ? $request->input($field) : null]
            )->all());

            $backValidated = $request->validate([
                'nom' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'historique_id' => 'nullable|integer|exists:historiques,id',
                'ideal_id' => 'nullable|integer|exists:ideaux,id',
                'defaut_id' => 'nullable|integer|exists:defauts,id',
                'attachement_id' => 'nullable|integer|exists:attachements,id',
                'personnalite_id' => 'nullable|integer|exists:personnalites,id',
                'outil' => 'nullable|string',
            ]);

            $this->saveData($backValidated);

            return redirect()->route('creation.competences');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string',
            'historique_id' => 'required|integer|exists:historiques,id',
            'ideal_id' => 'required|integer|exists:ideaux,id',
            'defaut_id' => 'required|integer|exists:defauts,id',
            'attachement_id' => 'required|integer|exists:attachements,id',
            'personnalite_id' => 'required|integer|exists:personnalites,id',
        ]);

        $race = Race::find($data['race_id']);
        $historique = Historique::find($validated['historique_id']);
        $ideal = Ideal::find($validated['ideal_id']);
        $defaut = Defaut::find($validated['defaut_id']);
        $attachement = Attachement::find($validated['attachement_id']);
        $personnalite = Personnalite::find($validated['personnalite_id']);

        // En 5e, si votre historique accorde une compétence que votre classe
        // vous a déjà fait choisir, les deux ne se cumulent pas : il faut
        // reprendre un choix différent pour votre classe. On bloque donc ici
        // plutôt que de silencieusement perdre une maîtrise.
        $conflits = array_values(array_intersect($data['competences'] ?? [], $historique?->competences ?? []));
        if (! empty($conflits)) {
            return redirect()->route('creation.competences')
                ->with('error', 'Vous maîtrisez déjà '.implode(' et ', $conflits).' grâce à l\'historique '.$historique->nom.'. Choisissez une compétence différente pour votre classe.');
        }

        // L'outil de départ n'est jamais un choix totalement libre en 5e :
        // certains historiques n'en accordent aucun, d'autres en imposent
        // un (ou plusieurs) fixe(s) sans choix (voir plus bas, fusionnés
        // avec l'équipement), et d'autres encore proposent une liste
        // précise dans laquelle il faut choisir. On ne peut donc valider ce
        // champ qu'une fois l'historique connu.
        $outilsDisponibles = $historique?->outils_disponibles ?? [];

        $outilValidated = $request->validate([
            'outil' => empty($outilsDisponibles)
                ? ['nullable']
                : ['required', 'string', Rule::in($outilsDisponibles)],
        ], [
            'outil.required' => 'Vous devez choisir un outil parmi ceux offerts par votre historique.',
            'outil.in' => 'Cet outil ne fait pas partie de ceux offerts par votre historique.',
        ]);

        $this->saveData([
            'historique_id' => $validated['historique_id'],
            'ideal_id' => $validated['ideal_id'],
            'defaut_id' => $validated['defaut_id'],
            'attachement_id' => $validated['attachement_id'],
            'personnalite_id' => $validated['personnalite_id'],
            'outil' => ! empty($outilsDisponibles) ? $outilValidated['outil'] : null,
            // L'historique vient avec son propre équipement de départ, auquel
            // s'ajoutent les outils qu'il accorde automatiquement sans choix
            // (outils_fixes) ; l'alignement final combine les 4 traits de
            // personnalité choisis (voir calculateAlignement()) ; la race
            // détermine les langues parlées : ces valeurs ne sont donc
            // jamais saisies directement par le joueur, elles sont dérivées
            // ici pour éviter toute incohérence.
            'equipement' => array_values(array_merge($historique?->equipement_depart ?? [], $historique?->outils_fixes ?? [])),
            'alignement' => $this->calculateAlignement($ideal, $defaut, $attachement, $personnalite),
            'langues' => $race?->langues ?? ['Commun'],
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('creation.confirmation');
    }

    /**
     * Combine les 4 traits de personnalité (idéal, défaut, attachement,
     * trait de personnalité) en un seul alignement final à deux axes,
     * plutôt que de se baser uniquement sur l'idéal choisi.
     *
     * Chaque trait qui porte une étiquette d'alignement "vote" sur les
     * deux axes : "Loyal" vaut -1 et "Chaotique" vaut +1 sur l'axe loi ;
     * "Bon" vaut -1 et "Mauvais" vaut +1 sur l'axe bien. Un trait sans
     * étiquette, ou dont l'étiquette est simplement "Neutre", ne fait
     * pencher aucun axe. Le signe de la somme des votes sur chaque axe
     * donne le mot final (Loyal/Neutre/Chaotique, puis Bon/Neutre/
     * Mauvais) ; une somme nulle (aucun vote, ou des votes qui
     * s'annulent) donne "Neutre" sur cet axe.
     *
     * NB : ce système de "vote" est un choix de conception maison, pas
     * une règle officielle de D&D 5e — dans les livres, seuls les idéaux
     * suggèrent un alignement ; les défauts, attachements et traits de
     * personnalité n'en portent pas officiellement. Les étiquettes
     * ajoutées ici dans HistoriquesSeeder reflètent simplement ce que le
     * texte de chaque trait semble indiquer.
     */
    protected function calculateAlignement(?Ideal $ideal, ?Defaut $defaut, ?Attachement $attachement, ?Personnalite $personnalite): string
    {
        $loiScore = 0;
        $bienScore = 0;

        foreach ([$ideal, $defaut, $attachement, $personnalite] as $trait) {
            $alignement = $trait?->alignement;

            if (! $alignement) {
                continue;
            }

            if (str_contains($alignement, 'Loyal')) {
                $loiScore--;
            } elseif (str_contains($alignement, 'Chaotique')) {
                $loiScore++;
            }

            if (str_contains($alignement, 'Bon')) {
                $bienScore--;
            } elseif (str_contains($alignement, 'Mauvais')) {
                $bienScore++;
            }
        }

        $loi = match (true) {
            $loiScore < 0 => 'Loyal',
            $loiScore > 0 => 'Chaotique',
            default => 'Neutre',
        };

        $bien = match (true) {
            $bienScore < 0 => 'Bon',
            $bienScore > 0 => 'Mauvais',
            default => 'Neutre',
        };

        if ($loi === 'Neutre' && $bien === 'Neutre') {
            return 'Neutre';
        }

        if ($loi === 'Neutre') {
            return "Neutre $bien";
        }

        if ($bien === 'Neutre') {
            return "$loi Neutre";
        }

        return "$loi $bien";
    }

    public function stepConfirmation(): View|RedirectResponse
    {
        $data = $this->getData();

        if (empty($data['nom'])) {
            return redirect()->route('creation.contexte');
        }

        $race = Race::find($data['race_id']);
        $sousRace = $data['sous_race_id'] ? SousRace::find($data['sous_race_id']) : null;
        $classe = Classe::find($data['classe_id']);
        $sousClasse = $data['sous_classe_id'] ? SousClasse::find($data['sous_classe_id']) : null;
        $historique = $data['historique_id'] ? Historique::find($data['historique_id']) : null;
        $ideal = $data['ideal_id'] ? Ideal::find($data['ideal_id']) : null;
        $defaut = $data['defaut_id'] ? Defaut::find($data['defaut_id']) : null;
        $attachement = $data['attachement_id'] ? Attachement::find($data['attachement_id']) : null;
        $personnalite = $data['personnalite_id'] ? Personnalite::find($data['personnalite_id']) : null;

        $abilites = $this->abilitesAvecBonus($data['abilites'], $data);
        $pv = $this->calculatePV($abilites, $classe);

        // Le personnage final cumule les compétences choisies pour la
        // classe et celles, fixes, accordées par l'historique (voir
        // saveContexte() pour la vérification anti-doublon).
        $competencesFinal = array_values(array_unique(array_merge($data['competences'] ?? [], $historique?->competences ?? [])));

        return view('personnages.creation.confirmation', [
            'data' => $data,
            'race' => $race,
            'sousRace' => $sousRace,
            'classe' => $classe,
            'sousClasse' => $sousClasse,
            'historique' => $historique,
            'ideal' => $ideal,
            'defaut' => $defaut,
            'attachement' => $attachement,
            'personnalite' => $personnalite,
            'abilites' => $abilites,
            'competencesFinal' => $competencesFinal,
            'pv' => $pv,
            'currentStep' => 6,
            'progress' => 6,
        ]);
    }

    protected function calculatePV(array $abilites, ?Classe $classe): int
    {
        $conMod = floor(($abilites['constitution'] ?? 8) - 10) / 2;
        $conMod = floor($conMod);

        if ($classe && $classe->de_de_vie) {
            $de = (int) str_replace('d', '', $classe->de_de_vie);

            return max(1, $de + $conMod);
        }

        return 10 + $conMod;
    }

    protected function abilitesAvecBonus(array $abilites, array $data): array
    {
        $race = Race::find($data['race_id']);
        $sousRace = ! empty($data['sous_race_id']) ? SousRace::find($data['sous_race_id']) : null;
        $classe = Classe::find($data['classe_id']);

        foreach ($abilites as $key => &$score) {
            $score += (int) data_get($race, "bonus_aptitudes.$key", 0);
            $score += (int) data_get($sousRace, "bonus_aptitudes.$key", 0);
            $score += (int) data_get($classe, "bonus_aptitudes.$key", 0);
        }
        unset($score);

        // Bonus "au choix" : la caractéristique concrète est celle choisie
        // par le joueur à l'étape des caractéristiques (voir
        // saveCaracteristiques()), pas une caractéristique fixe déclarée
        // sur la race/sous-race/classe.
        $auChoixDisponibles = $this->bonusAuChoixDisponibles($data);
        foreach ($auChoixDisponibles as $source => $config) {
            foreach ($data['bonus_choix'][$source] ?? [] as $key) {
                if (array_key_exists($key, $abilites)) {
                    $abilites[$key] += (int) $config['bonus'];
                }
            }
        }

        return $abilites;
    }

    public function finalize(Request $request, PersonnageCreationService $service): RedirectResponse
    {
        $data = $this->getData();
        $niveau = 1;

        if (! empty($data['sous_classe_id'])) {
            $sousClasse = SousClasse::find($data['sous_classe_id']);
            $classe = Classe::find($data['classe_id']);
            if ($sousClasse && $classe && $classe->niveau_sous_classe > $niveau) {
                $data['sous_classe_id'] = null;
            }
        }

        $historique = ! empty($data['historique_id']) ? Historique::find($data['historique_id']) : null;

        // Compétences finales du personnage : celles choisies pour la
        // classe, plus celles, fixes, accordées par l'historique (voir
        // saveContexte() pour la vérification anti-doublon en amont).
        $competencesFinal = array_values(array_unique(array_merge($data['competences'] ?? [], $historique?->competences ?? [])));

        $abilites = $this->abilitesAvecBonus($data['abilites'], $data);

        $payload = [
            'utilisateur_id' => auth()->id(),
            'race_id' => $data['race_id'],
            'sous_race_id' => $data['sous_race_id'],
            'classe_id' => $data['classe_id'],
            'sous_classe_id' => $data['sous_classe_id'],
            'historique_id' => $data['historique_id'] ?? null,
            'ideal_id' => $data['ideal_id'] ?? null,
            'defaut_id' => $data['defaut_id'] ?? null,
            'attachement_id' => $data['attachement_id'] ?? null,
            'personnalite_id' => $data['personnalite_id'] ?? null,
            'abilites' => $abilites,
            'competences' => $competencesFinal,
            'nom' => $data['nom'],
            'niveau' => $niveau,
            'alignement' => $data['alignement'],
            'description' => $data['description'],
            'point_de_vie' => $this->calculatePV($abilites, Classe::find($data['classe_id'])),
            'outil' => $data['outil'],
            'langues' => $data['langues'],
            'equipement' => $data['equipement'],
        ];

        try {
            $personnage = $service->create($payload);
            session()->forget(static::SESSION_KEY);

            return redirect()->route('personnages.show', $personnage)
                ->with('success', 'Votre personnage a été créé avec succès !');
        } catch (ValidationException $e) {
            return redirect()->route('creation.confirmation')
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function getSousClasses($classeId, $niveauMax = 1): JsonResponse
    {
        $classe = Classe::findOrFail($classeId);
        $sousClasses = $classe->niveau_sous_classe <= $niveauMax
            ? $classe->sousClasses->each->setAttribute('niveau_sous_classe', $classe->niveau_sous_classe)
            : collect();

        return response()->json($sousClasses);
    }

    public function getSousRaces($raceId): JsonResponse
    {
        $sousRaces = SousRace::where('race_id', $raceId)->get();

        return response()->json($sousRaces);
    }

    public function getHistoriqueDetails($historiqueId): JsonResponse
    {
        $historique = Historique::with(['ideaux', 'defauts', 'attachements', 'personnalites'])->find($historiqueId);

        if (! $historique) {
            return response()->json(['error' => 'Historique introuvable'], 404);
        }

        return response()->json($historique);
    }
}
