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
use App\Services\CalculCreationService;
use App\Services\PersonnageCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CreationPersonnageController extends Controller
{
    public function __construct(private readonly CalculCreationService $calculs) {}

    protected const CLE_SESSION = 'personnage_creation';

    protected const NOMBRE_ETAPES = 6;

    protected function donnees(): array
    {
        $valeursParDefaut = [
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
            'reserve_des' => null,
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

        return array_merge($valeursParDefaut, session(static::CLE_SESSION, []));
    }

    protected function enregistrerDonnees(array $donnees): void
    {
        $actuelles = $this->donnees();
        session([static::CLE_SESSION => array_merge($actuelles, $donnees)]);
    }

    protected function progression(): int
    {
        $donnees = $this->donnees();
        $etape = 0;

        if (! empty($donnees['race_id'])) {
            $etape = 1;
        }
        if (! empty($donnees['classe_id'])) {
            $etape = 2;
        }
        if (! empty($donnees['methode'])) {
            $etape = 3;
        }
        if ($this->abilitesCompletes($donnees['abilites'] ?? [])) {
            $etape = 4;
        }
        if (! empty($donnees['competences'])) {
            $etape = 5;
        }

        return $etape;
    }

    protected function abilitesCompletes(array $abilites): bool
    {
        return ! empty($abilites) &&
            ($abilites['force'] ?? 0) > 0 &&
            ($abilites['dexterite'] ?? 0) > 0 &&
            ($abilites['constitution'] ?? 0) > 0 &&
            ($abilites['intelligence'] ?? 0) > 0 &&
            ($abilites['sagesse'] ?? 0) > 0 &&
            ($abilites['charisme'] ?? 0) > 0;
    }

    public function etapeRace(): View
    {
        $races = Race::with('sousRaces')->get();
        $donnees = $this->donnees();

        return view('personnages.creation.race', [
            'races' => $races,
            'raceChoisie' => $donnees['race_id'],
            'sousRaceChoisie' => $donnees['sous_race_id'],
            'etapeCourante' => 1,
            'progression' => $this->progression(),
        ]);
    }

    public function enregistrerRace(Request $requete): RedirectResponse
    {
        // Une sous-race ne peut pas rester "optionnelle" quand la race en
        // propose : le joueur doit alors obligatoirement en choisir une.
        $race = Race::with('sousRaces')->find($requete->input('race_id'));
        $sousRaceRequise = $race && $race->sousRaces->isNotEmpty();

        $donneesValidees = $requete->validate([
            'race_id' => 'required|integer|exists:races,id',
            'sous_race_id' => [
                $sousRaceRequise ? 'required' : 'nullable',
                'integer',
                Rule::exists('sous_races', 'id')->where(function ($requeteSql) use ($requete) {
                    return $requeteSql->where('race_id', $requete->input('race_id'));
                }),
            ],
        ], [
            'race_id.required' => 'Vous devez choisir une race avant de continuer.',
            'sous_race_id.required' => 'Vous devez choisir une sous-race avant de continuer.',
        ]);

        // $donneesValidees vient d'une requête HTTP : ce sont des chaînes, même
        // pour les champs validés par la règle "integer". On les convertit
        // pour stocker des id cohérents en session (comparés ailleurs à des
        // id entiers renvoyés par Eloquent).
        //
        // La règle "exists" ci-dessus vérifie déjà, via une requête SQL
        // scopée sur race_id, que sous_race_id appartient bien à la race
        // envoyée : il n'y a donc rien à revalider ici.
        $nouvelleRaceId = (int) $donneesValidees['race_id'];
        $nouvelleSousRaceId = isset($donneesValidees['sous_race_id']) ? (int) $donneesValidees['sous_race_id'] : null;

        $this->enregistrerDonnees([
            'race_id' => $nouvelleRaceId,
            'sous_race_id' => $nouvelleSousRaceId,
        ]);

        return redirect()->route('creation.classe');
    }

    public function etapeClasse(): View|RedirectResponse
    {
        $classes = Classe::with('sousClasses')->get();
        $donnees = $this->donnees();

        if (empty($donnees['race_id'])) {
            return redirect()->route('creation.race');
        }

        return view('personnages.creation.classe', [
            'classes' => $classes,
            'classeChoisie' => $donnees['classe_id'],
            'sousClasseChoisie' => $donnees['sous_classe_id'],
            'etapeCourante' => 2,
            'progression' => $this->progression(),
        ]);
    }

    public function enregistrerClasse(Request $requete): RedirectResponse
    {
        // Une sous-classe ne peut pas rester "optionnelle" quand elle est
        // disponible dès le niveau 1 (niveau auquel un personnage est
        // toujours créé) : le joueur doit alors obligatoirement en choisir
        // une.
        $classe = Classe::with('sousClasses')->find($requete->input('classe_id'));
        $sousClasseRequise = $classe
            && $classe->niveau_sous_classe <= 1
            && $classe->sousClasses->isNotEmpty();

        $donneesValidees = $requete->validate([
            'classe_id' => 'required|integer|exists:classes,id',
            'sous_classe_id' => [
                $sousClasseRequise ? 'required' : 'nullable',
                'integer',
                Rule::exists('sous_classes', 'id')->where(function ($requeteSql) use ($requete) {
                    return $requeteSql->where('classe_id', $requete->input('classe_id'));
                }),
            ],
        ], [
            'classe_id.required' => 'Vous devez choisir une classe avant de continuer.',
            'sous_classe_id.required' => 'Vous devez choisir une sous-classe avant de continuer.',
        ]);

        // Mêmes précautions que dans enregistrerRace() : $donneesValidees contient des
        // chaînes issues de la requête HTTP, à convertir avant tout usage
        // comme id entier. La règle "exists" ci-dessus garantit déjà que
        // sous_classe_id appartient à la classe envoyée : rien à revalider
        // ici.
        $nouvelleClasseId = (int) $donneesValidees['classe_id'];
        $nouvelleSousClasseId = isset($donneesValidees['sous_classe_id']) ? (int) $donneesValidees['sous_classe_id'] : null;

        $this->enregistrerDonnees([
            'classe_id' => $nouvelleClasseId,
            'sous_classe_id' => $nouvelleSousClasseId,
        ]);

        return redirect()->route('creation.methode');
    }

    public function etapeMethode(): View|RedirectResponse
    {
        if (empty($this->donnees()['classe_id'])) {
            return redirect()->route('creation.classe');
        }

        return view('personnages.creation.methode', [
            'etapeCourante' => 3,
            'progression' => $this->progression(),
        ]);
    }

    public function enregistrerMethode(Request $requete): RedirectResponse
    {
        $donneesValidees = $requete->validate([
            'methode' => 'required|in:points,standard,des',
        ]);

        $abilites = match ($donneesValidees['methode']) {
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
            'des' => [
                'force' => 0,
                'dexterite' => 0,
                'constitution' => 0,
                'intelligence' => 0,
                'sagesse' => 0,
                'charisme' => 0,
            ],
        };

        $this->enregistrerDonnees([
            'methode' => $donneesValidees['methode'],
            'abilites' => $abilites,
            'reserve_des' => null,
        ]);

        return redirect()->route('creation.caracteristiques');
    }

    protected const REPARTITION_STANDARD = [15, 14, 13, 12, 10, 8];

    protected const CLES_ABILITES = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];

    public function etapeCaracteristiques(): View|RedirectResponse
    {
        $donnees = $this->donnees();

        if (empty($donnees['methode'])) {
            return redirect()->route('creation.methode');
        }

        return view('personnages.creation.caracteristiques', [
            'methode' => $donnees['methode'],
            'abilites' => $donnees['abilites'],
            'reserveDes' => $donnees['reserve_des'],
            'repartitionStandard' => static::REPARTITION_STANDARD,
            'race' => $race = Race::find($donnees['race_id']),
            'sousRace' => $sousRace = $donnees['sous_race_id'] ? SousRace::find($donnees['sous_race_id']) : null,
            'classe' => $classe = Classe::find($donnees['classe_id']),
            'bonusAptitudes' => [
                'race' => $race?->bonus_aptitudes ?? [],
                'sous_race' => $sousRace?->bonus_aptitudes ?? [],
                'classe' => $classe?->bonus_aptitudes ?? [],
            ],
            'auChoixDisponibles' => $this->calculs->bonusAuChoixDisponibles($donnees),
            'bonusChoix' => $donnees['bonus_choix'] ?? [],
            'etapeCourante' => 4,
            'progression' => $this->progression(),
        ]);
    }

    public function enregistrerCaracteristiques(Request $requete): RedirectResponse
    {
        $donnees = $this->donnees();
        $methode = $donnees['methode'];

        $regles = [
            'force' => 'required|integer|min:1|max:30',
            'dexterite' => 'required|integer|min:1|max:30',
            'constitution' => 'required|integer|min:1|max:30',
            'intelligence' => 'required|integer|min:1|max:30',
            'sagesse' => 'required|integer|min:1|max:30',
            'charisme' => 'required|integer|min:1|max:30',
        ];

        if ($methode === 'points') {
            foreach ($regles as $cle => &$regle) {
                $regle = 'required|integer|min:8|max:15';
            }
            unset($regle);
        }

        if ($methode === 'standard') {
            $regles['points_disponibles'] = 'nullable|integer';
        }

        $donneesValidees = $requete->validate($regles);

        $abilites = [
            'force' => (int) $donneesValidees['force'],
            'dexterite' => (int) $donneesValidees['dexterite'],
            'constitution' => (int) $donneesValidees['constitution'],
            'intelligence' => (int) $donneesValidees['intelligence'],
            'sagesse' => (int) $donneesValidees['sagesse'],
            'charisme' => (int) $donneesValidees['charisme'],
        ];

        if ($methode === 'points') {
            $tableCouts = [8 => 0, 9 => 1, 10 => 2, 11 => 3, 12 => 4, 13 => 6, 14 => 8, 15 => 10];
            $coutTotal = array_sum(array_map(fn ($v) => $tableCouts[$v] ?? 0, $abilites));
            if ($coutTotal !== 27) {
                return redirect()->back()
                    ->with('erreur', 'Vous devez utiliser exactement 27 points (coût progressif : 9=1, 10=2, 11=3, 12=4, 13=6, 14=8, 15=10).');
            }
        }

        // La répartition standard et le lancer de dés attribuent chaque
        // caractéristique en glissant-déposant une valeur d'un lot fixe :
        // on vérifie ici que les valeurs soumises correspondent exactement
        // à ce lot (même multiset), pour empêcher un envoi de formulaire
        // trafiqué depuis le client.
        if ($methode === 'standard') {
            $soumises = array_values($abilites);
            sort($soumises);
            $attendues = static::REPARTITION_STANDARD;
            sort($attendues);
            if ($soumises !== $attendues) {
                return redirect()->back()
                    ->with('erreur', 'La répartition ne correspond pas aux valeurs disponibles (15, 14, 13, 12, 10, 8). Merci de glisser chaque valeur dans une seule caractéristique.');
            }
        }

        if ($methode === 'des') {
            $reserve = $donnees['reserve_des'];
            if (! is_array($reserve) || count($reserve) !== 6) {
                return redirect()->route('creation.caracteristiques')
                    ->with('erreur', 'Vous devez lancer les dés avant de continuer.');
            }
            $soumises = array_values($abilites);
            sort($soumises);
            $attendues = $reserve;
            sort($attendues);
            if ($soumises !== $attendues) {
                return redirect()->back()
                    ->with('erreur', 'La répartition ne correspond pas aux valeurs lancées. Merci de glisser chaque résultat dans une seule caractéristique.');
            }
        }

        // Bonus "au choix" (Humain Variant, Demi-elfe, ...) : le joueur doit
        // indiquer, pour chaque source concernée, exactement "nombre"
        // caractéristiques distinctes parmi les 6 (voir
        // CalculCreationService::bonusAuChoixDisponibles() et
        // abilitesAvecBonus() qui applique
        // ensuite ces bonus sur le personnage final).
        $auChoixDisponibles = $this->calculs->bonusAuChoixDisponibles($donnees);
        $bonusChoix = [];

        foreach ($auChoixDisponibles as $source => $config) {
            $nombre = (int) $config['nombre'];
            $choix = array_values(array_filter((array) $requete->input("bonus_choix_$source", [])));

            if (
                count($choix) !== $nombre
                || count(array_unique($choix)) !== $nombre
                || array_diff($choix, static::CLES_ABILITES)
            ) {
                return redirect()->back()
                    ->with('erreur', "Vous devez choisir $nombre caractéristiques différentes pour le bonus au choix.");
            }

            $bonusChoix[$source] = $choix;
        }

        // Les valeurs de la session restent les valeurs attribuées par le
        // joueur. Les bonus (race, sous-race, classe, et les bonus "au
        // choix" une fois assignés) sont ajoutés uniquement au personnage
        // final afin de ne pas fausser la répartition ni son coût.
        $this->enregistrerDonnees(['abilites' => $abilites, 'bonus_choix' => $bonusChoix]);

        return redirect()->route('creation.competences');
    }

    /**
     * Lance les 6 groupes de caractéristiques (4d6, on garde les 3 meilleurs)
     * en une seule fois. Le joueur répartit ensuite librement les 6
     * résultats obtenus par glisser-déposer (voir _caracteristiques_dice).
     */
    public function lancerDes(Request $requete)
    {
        $lancers = [];
        for ($i = 0; $i < 6; $i++) {
            $des = [];
            for ($j = 0; $j < 4; $j++) {
                $des[] = random_int(1, 6);
            }
            rsort($des);
            array_pop($des);
            $lancers[] = array_sum($des);
        }

        $abilitesVides = array_fill_keys(static::CLES_ABILITES, 0);

        $this->enregistrerDonnees([
            'reserve_des' => $lancers,
            'abilites' => $abilitesVides,
        ]);

        if ($requete->expectsJson()) {
            return response()->json(['lancers' => $lancers]);
        }

        return redirect()->back();
    }

    public function etapeCompetences(): View|RedirectResponse
    {
        $donnees = $this->donnees();

        if (! $this->abilitesCompletes($donnees['abilites'] ?? [])) {
            return redirect()->route('creation.caracteristiques');
        }

        $classe = Classe::find($donnees['classe_id']);
        $competencesDisponibles = $classe?->competences_disponibles ?? [];
        $nombreCompetences = $classe?->nombre_competences ?? 2;

        return view('personnages.creation.competences', [
            'classe' => $classe,
            'competencesDisponibles' => $competencesDisponibles,
            'competencesSelectionnees' => array_values(array_intersect($donnees['competences'] ?? [], $competencesDisponibles)),
            'nombreCompetences' => $nombreCompetences,
            'etapeCourante' => 5,
            'progression' => $this->progression(),
        ]);
    }

    public function enregistrerCompetences(Request $requete): RedirectResponse
    {
        $donnees = $this->donnees();
        $classe = Classe::find($donnees['classe_id']);
        $competencesDisponibles = $classe?->competences_disponibles ?? [];
        $nombreCompetences = $classe?->nombre_competences ?? 2;

        $donneesValidees = $requete->validate([
            'competences' => ['required', 'array', "size:$nombreCompetences"],
            'competences.*' => ['string', Rule::in($competencesDisponibles)],
        ], [
            'competences.required' => "Vous devez choisir $nombreCompetences compétences avant de continuer.",
            'competences.size' => "Vous devez choisir exactement $nombreCompetences compétences.",
            'competences.*.in' => 'Cette compétence ne fait pas partie de celles offertes par votre classe.',
        ]);

        $competences = array_values(array_unique($donneesValidees['competences']));

        $this->enregistrerDonnees([
            'competences' => $competences,
        ]);

        // Si le joueur arrive ici après avoir été renvoyé depuis l'étape
        // contexte (conflit entre une compétence de classe et une
        // compétence déjà accordée par l'historique choisi), le reste du
        // formulaire contexte (historique, traits, nom, ...) est déjà
        // rempli et sauvegardé (voir enregistrerContexte()) : on ne lui fait pas
        // resaisir cette page, on vérifie juste que le nouveau choix
        // résout bien le conflit et on l'envoie directement à la
        // confirmation.
        $historique = ! empty($donnees['historique_id']) ? Historique::find($donnees['historique_id']) : null;
        $contexteDejaRempli = $historique
            && ! empty($donnees['ideal_id'])
            && ! empty($donnees['defaut_id'])
            && ! empty($donnees['attachement_id'])
            && ! empty($donnees['personnalite_id'])
            && ! empty($donnees['nom']);

        if ($contexteDejaRempli) {
            $conflits = array_values(array_intersect($competences, $historique->competences ?? []));

            if (! empty($conflits)) {
                return redirect()->route('creation.competences')
                    ->with('erreur', 'Vous maîtrisez déjà '.implode(' et ', $conflits).' grâce à l\'historique '.$historique->nom.'. Choisissez une compétence différente pour votre classe.');
            }

            $race = Race::find($donnees['race_id']);
            $ideal = ! empty($donnees['ideal_id']) ? Ideal::find($donnees['ideal_id']) : null;
            $defaut = ! empty($donnees['defaut_id']) ? Defaut::find($donnees['defaut_id']) : null;
            $attachement = ! empty($donnees['attachement_id']) ? Attachement::find($donnees['attachement_id']) : null;
            $personnalite = ! empty($donnees['personnalite_id']) ? Personnalite::find($donnees['personnalite_id']) : null;

            $this->enregistrerDonnees([
                'equipement' => array_values(array_merge($historique->equipement_depart ?? [], $historique->outils_fixes ?? [])),
                'alignement' => $this->calculs->alignement($ideal, $defaut, $attachement, $personnalite),
                'langues' => $race?->langues ?? ['Commun'],
            ]);

            return redirect()->route('creation.confirmation');
        }

        return redirect()->route('creation.contexte');
    }

    public function etapeContexte(): View|RedirectResponse
    {
        $donnees = $this->donnees();

        if (empty($donnees['race_id'])) {
            return redirect()->route('creation.race');
        }

        $historiques = Historique::with(['ideaux', 'defauts', 'attachements', 'personnalites'])->get();
        $historiqueChoisiModele = $donnees['historique_id'] ? $historiques->firstWhere('id', $donnees['historique_id']) : null;

        $race = Race::find($donnees['race_id']);
        $languesRace = $race->langues ?? ['Commun'];

        return view('personnages.creation.contexte', [
            'donnees' => $donnees,
            'historiques' => $historiques,
            'languesRace' => $languesRace,
            'historiqueChoisi' => $donnees['historique_id'],
            'historiqueChoisiModele' => $historiqueChoisiModele,
            'idealChoisi' => $donnees['ideal_id'],
            'defautChoisi' => $donnees['defaut_id'],
            'attachementChoisi' => $donnees['attachement_id'],
            'personnaliteChoisie' => $donnees['personnalite_id'],
            'outilChoisi' => $donnees['outil'],
            'etapeCourante' => 6,
            'progression' => $this->progression(),
        ]);
    }

    public function enregistrerContexte(Request $requete): RedirectResponse
    {
        $donnees = $this->donnees();

        // Le bouton "Retour" ramène le joueur à l'étape des compétences sans
        // que cette étape-ci soit terminée : les champs "required" ne
        // peuvent donc pas être exigés ici. On enregistre malgré tout, de
        // façon permissive, ce qui a déjà été rempli/sélectionné, pour que
        // revenir en arrière puis ravancer n'efface pas ces choix (avant ce
        // correctif, rien n'était sauvegardé tant que "Suivant" n'avait pas
        // été soumis avec succès, donc un aller-retour perdait tout).
        if ($requete->input('direction') === 'back') {
            $champsId = ['historique_id', 'ideal_id', 'defaut_id', 'attachement_id', 'personnalite_id'];
            $requete->merge(collect($champsId)->mapWithKeys(
                fn ($champ) => [$champ => $requete->filled($champ) ? $requete->input($champ) : null]
            )->all());

            $retourValide = $requete->validate([
                'nom' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'historique_id' => 'nullable|integer|exists:historiques,id',
                'ideal_id' => 'nullable|integer|exists:ideaux,id',
                'defaut_id' => 'nullable|integer|exists:defauts,id',
                'attachement_id' => 'nullable|integer|exists:attachements,id',
                'personnalite_id' => 'nullable|integer|exists:personnalites,id',
                'outil' => 'nullable|string',
            ]);

            $this->enregistrerDonnees($retourValide);

            return redirect()->route('creation.competences');
        }

        $donneesValidees = $requete->validate([
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string',
            'historique_id' => 'required|integer|exists:historiques,id',
            'ideal_id' => 'required|integer|exists:ideaux,id',
            'defaut_id' => 'required|integer|exists:defauts,id',
            'attachement_id' => 'required|integer|exists:attachements,id',
            'personnalite_id' => 'required|integer|exists:personnalites,id',
        ]);

        $race = Race::find($donnees['race_id']);
        $historique = Historique::find($donneesValidees['historique_id']);
        $ideal = Ideal::find($donneesValidees['ideal_id']);
        $defaut = Defaut::find($donneesValidees['defaut_id']);
        $attachement = Attachement::find($donneesValidees['attachement_id']);
        $personnalite = Personnalite::find($donneesValidees['personnalite_id']);

        // On sauvegarde tout de suite ce qui vient d'être saisi/sélectionné
        // sur cette page, avant même la vérification du conflit
        // compétences/historique ci-dessous : sinon, en cas de conflit, la
        // redirection vers l'étape compétences effaçait tout ce que le
        // joueur venait de remplir ici (nom, description, historique et
        // traits choisis), l'obligeant à tout resaisir après correction.
        // L'outil est conservé tel quel (non encore validé formellement
        // via outils_disponibles) : sa validation stricte dépend de
        // l'historique et ne doit avoir lieu qu'une fois le conflit
        // écarté, comme avant ce correctif (voir plus bas).
        $this->enregistrerDonnees([
            'historique_id' => $donneesValidees['historique_id'],
            'ideal_id' => $donneesValidees['ideal_id'],
            'defaut_id' => $donneesValidees['defaut_id'],
            'attachement_id' => $donneesValidees['attachement_id'],
            'personnalite_id' => $donneesValidees['personnalite_id'],
            'outil' => $requete->filled('outil') ? $requete->input('outil') : null,
            'nom' => $donneesValidees['nom'],
            'description' => $donneesValidees['description'] ?? null,
        ]);

        // En 5e, si votre historique accorde une compétence que votre classe
        // vous a déjà fait choisir, les deux ne se cumulent pas : il faut
        // reprendre un choix différent pour votre classe. On bloque donc ici
        // plutôt que de silencieusement perdre une maîtrise. Ce contrôle
        // doit rester avant la validation stricte de l'outil ci-dessous :
        // un historique qui impose un choix d'outil ne doit pas empêcher
        // d'afficher l'erreur de conflit de compétences en premier.
        $conflits = array_values(array_intersect($donnees['competences'] ?? [], $historique?->competences ?? []));
        if (! empty($conflits)) {
            return redirect()->route('creation.competences')
                ->with('erreur', 'Vous maîtrisez déjà '.implode(' et ', $conflits).' grâce à l\'historique '.$historique->nom.'. Choisissez une compétence différente pour votre classe.');
        }

        // L'outil de départ n'est jamais un choix totalement libre en 5e :
        // certains historiques n'en accordent aucun, d'autres en imposent
        // un (ou plusieurs) fixe(s) sans choix (voir plus bas, fusionnés
        // avec l'équipement), et d'autres encore proposent une liste
        // précise dans laquelle il faut choisir. On ne peut donc valider ce
        // champ qu'une fois l'historique connu (et le conflit écarté).
        $outilsDisponibles = $historique?->outils_disponibles ?? [];

        $outilValide = $requete->validate([
            'outil' => empty($outilsDisponibles)
                ? ['nullable']
                : ['required', 'string', Rule::in($outilsDisponibles)],
        ], [
            'outil.required' => 'Vous devez choisir un outil parmi ceux offerts par votre historique.',
            'outil.in' => 'Cet outil ne fait pas partie de ceux offerts par votre historique.',
        ]);

        $this->enregistrerDonnees([
            'outil' => ! empty($outilsDisponibles) ? $outilValide['outil'] : null,
            // L'historique vient avec son propre équipement de départ, auquel
            // s'ajoutent les outils qu'il accorde automatiquement sans choix
            // (outils_fixes) ; l'alignement final combine les 4 traits de
            // personnalité choisis (voir CalculCreationService::alignement()) ; la race
            // détermine les langues parlées : ces valeurs ne sont donc
            // jamais saisies directement par le joueur, elles sont dérivées
            // ici pour éviter toute incohérence.
            'equipement' => array_values(array_merge($historique?->equipement_depart ?? [], $historique?->outils_fixes ?? [])),
            'alignement' => $this->calculs->alignement($ideal, $defaut, $attachement, $personnalite),
            'langues' => $race?->langues ?? ['Commun'],
        ]);

        return redirect()->route('creation.confirmation');
    }

    public function etapeConfirmation(): View|RedirectResponse
    {
        $donnees = $this->donnees();

        if (empty($donnees['nom'])) {
            return redirect()->route('creation.contexte');
        }

        $race = Race::find($donnees['race_id']);
        $sousRace = $donnees['sous_race_id'] ? SousRace::find($donnees['sous_race_id']) : null;
        $classe = Classe::find($donnees['classe_id']);
        $sousClasse = $donnees['sous_classe_id'] ? SousClasse::find($donnees['sous_classe_id']) : null;
        $historique = $donnees['historique_id'] ? Historique::find($donnees['historique_id']) : null;
        $ideal = $donnees['ideal_id'] ? Ideal::find($donnees['ideal_id']) : null;
        $defaut = $donnees['defaut_id'] ? Defaut::find($donnees['defaut_id']) : null;
        $attachement = $donnees['attachement_id'] ? Attachement::find($donnees['attachement_id']) : null;
        $personnalite = $donnees['personnalite_id'] ? Personnalite::find($donnees['personnalite_id']) : null;

        $abilites = $this->calculs->abilitesAvecBonus($donnees['abilites'], $donnees);
        $pv = $this->calculs->pointsDeVieInitiaux($abilites, $classe);

        // Le personnage final cumule les compétences choisies pour la
        // classe et celles, fixes, accordées par l'historique (voir
        // enregistrerContexte() pour la vérification anti-doublon).
        $competencesFinal = array_values(array_unique(array_merge($donnees['competences'] ?? [], $historique?->competences ?? [])));

        return view('personnages.creation.confirmation', [
            'donnees' => $donnees,
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
            'etapeCourante' => 6,
            'progression' => 6,
        ]);
    }

    public function finaliser(Request $requete, PersonnageCreationService $service): RedirectResponse
    {
        $donnees = $this->donnees();
        $niveau = 1;

        if (! empty($donnees['sous_classe_id'])) {
            $sousClasse = SousClasse::find($donnees['sous_classe_id']);
            $classe = Classe::find($donnees['classe_id']);
            if ($sousClasse && $classe && $classe->niveau_sous_classe > $niveau) {
                $donnees['sous_classe_id'] = null;
            }
        }

        $historique = ! empty($donnees['historique_id']) ? Historique::find($donnees['historique_id']) : null;

        // Compétences finales du personnage : celles choisies pour la
        // classe, plus celles, fixes, accordées par l'historique (voir
        // enregistrerContexte() pour la vérification anti-doublon en amont).
        $competencesFinal = array_values(array_unique(array_merge($donnees['competences'] ?? [], $historique?->competences ?? [])));

        $abilites = $this->calculs->abilitesAvecBonus($donnees['abilites'], $donnees);

        $donneesPersonnage = [
            'utilisateur_id' => auth()->id(),
            'race_id' => $donnees['race_id'],
            'sous_race_id' => $donnees['sous_race_id'],
            'classe_id' => $donnees['classe_id'],
            'sous_classe_id' => $donnees['sous_classe_id'],
            'historique_id' => $donnees['historique_id'] ?? null,
            'ideal_id' => $donnees['ideal_id'] ?? null,
            'defaut_id' => $donnees['defaut_id'] ?? null,
            'attachement_id' => $donnees['attachement_id'] ?? null,
            'personnalite_id' => $donnees['personnalite_id'] ?? null,
            'abilites' => $abilites,
            'competences' => $competencesFinal,
            'nom' => $donnees['nom'],
            'niveau' => $niveau,
            'alignement' => $donnees['alignement'],
            'description' => $donnees['description'],
            'point_de_vie' => $this->calculs->pointsDeVieInitiaux($abilites, Classe::find($donnees['classe_id'])),
            'outil' => $donnees['outil'],
            'langues' => $donnees['langues'],
            'equipement' => $donnees['equipement'],
        ];

        try {
            $personnage = $service->create($donneesPersonnage);
            session()->forget(static::CLE_SESSION);

            return redirect()->route('personnages.show', $personnage)
                ->with('succes', 'Votre personnage a été créé avec succès !');
        } catch (ValidationException $e) {
            return redirect()->route('creation.confirmation')
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function listerSousClasses($classeId, $niveauMax = 1): JsonResponse
    {
        $classe = Classe::findOrFail($classeId);
        $sousClasses = $classe->niveau_sous_classe <= $niveauMax
            ? $classe->sousClasses->each->setAttribute('niveau_sous_classe', $classe->niveau_sous_classe)
            : collect();

        return response()->json($sousClasses);
    }

    public function listerSousRaces($raceId): JsonResponse
    {
        $sousRaces = SousRace::where('race_id', $raceId)->get();

        return response()->json($sousRaces);
    }

    public function detailsHistorique($historiqueId): JsonResponse
    {
        $historique = Historique::with(['ideaux', 'defauts', 'attachements', 'personnalites'])->find($historiqueId);

        if (! $historique) {
            return response()->json(['erreur' => 'Historique introuvable'], 404);
        }

        return response()->json($historique);
    }
}
