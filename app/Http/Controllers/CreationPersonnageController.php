<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Historique;
use App\Models\Ideal;
use App\Models\Defaut;
use App\Models\Attachement;
use App\Models\Personnalite;
use App\Models\Race;
use App\Models\SousClasse;
use App\Models\SousRace;
use App\Models\Utilisateur;
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
        return session(static::SESSION_KEY, [
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
        ]);
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
        $validated = $request->validate([
            'race_id' => 'required|integer|exists:races,id',
            'sous_race_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_races', 'id')->where(function ($query) use ($request) {
                    return $query->where('race_id', $request->input('race_id'));
                }),
            ],
        ]);

        $current = $this->getData();
        $newRaceId = $validated['race_id'];
        $newSousRaceId = $validated['sous_race_id'] ?? null;

        if (! empty($newSousRaceId)) {
            $sr = SousRace::find($newSousRaceId);
            if (! $sr || $sr->race_id !== $newRaceId) {
                $newSousRaceId = null;
            }
        }

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
        $validated = $request->validate([
            'classe_id' => 'required|integer|exists:classes,id',
            'sous_classe_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_classes', 'id')->where(function ($query) use ($request) {
                    return $query->where('classe_id', $request->input('classe_id'));
                }),
            ],
        ]);

        $current = $this->getData();
        $newClasseId = $validated['classe_id'];
        $newSousClasseId = $validated['sous_classe_id'] ?? null;

        if ($newClasseId !== $current['classe_id'] && ! empty($newSousClasseId)) {
            $sc = SousClasse::find($newSousClasseId);
            if (! $sc || $sc->classe_id !== $newClasseId) {
                $newSousClasseId = null;
            }
        }

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
        ]);

        return redirect()->route('creation.caracteristiques');
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
            'force' => $validated['force'],
            'dexterite' => $validated['dexterite'],
            'constitution' => $validated['constitution'],
            'intelligence' => $validated['intelligence'],
            'sagesse' => $validated['sagesse'],
            'charisme' => $validated['charisme'],
        ];

        if ($methode === 'points') {
            $costTable = [8 => 0, 9 => 1, 10 => 2, 11 => 3, 12 => 4, 13 => 6, 14 => 8, 15 => 10];
            $totalCost = array_sum(array_map(fn ($v) => $costTable[$v] ?? 0, $abilites));
            if ($totalCost !== 27) {
                return redirect()->back()
                    ->with('error', 'Vous devez utiliser exactement 27 points (coût progressif : 9=1, 10=2, 11=3, 12=4, 13=6, 14=8, 15=10).');
            }
        }

        $this->saveData(['abilites' => $abilites]);

        return redirect()->route('creation.competences');
    }

    public function rollDice(Request $request)
    {
        $data = $this->getData();
        $abilites = $data['abilites'];

        $rolls = [];
        for ($i = 0; $i < 4; $i++) {
            $rolls[] = rand(1, 6);
        }
        rsort($rolls);
        array_pop($rolls);
        $result = array_sum($rolls);

        $abilite = $request->input('abilite');

        if (! $abilite || ! in_array($abilite, ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'])) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Abilite invalide'], 400)
                : redirect()->back();
        }

        $abilites[$abilite] = $result;
        $this->saveData(['abilites' => $abilites]);

        if ($request->expectsJson()) {
            return response()->json(['value' => $result, 'abilite' => $abilite]);
        }

        return redirect()->back()->with('rolled', $abilite);
    }

    public function stepCompetences(): View|RedirectResponse
    {
        $data = $this->getData();

        if (! $this->abilitesComplete($data['abilites'] ?? [])) {
            return redirect()->route('creation.caracteristiques');
        }

        $classe = Classe::find($data['classe_id']);

        $allCompetences = [
            'Acrobaties', 'Arcanes', 'Athlétisme', 'Art de la duplicité',
            'Artisanat', 'Attention', 'Dissimulation', 'Histoire',
            'Intimidation', 'Insight', 'Médecine', 'Nature',
            'Perception', 'Performance', 'Persuasion', 'Religion',
            'Sapience animale', 'Survie',
        ];

        return view('personnages.creation.competences', [
            'competencesDisponibles' => $allCompetences,
            'competencesSelectionnees' => $data['competences'] ?? [],
            'nombreCompetences' => 2,
            'currentStep' => 5,
            'progress' => $this->getProgress(),
        ]);
    }

    public function saveCompetences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'competences' => 'required|array|min:2',
            'competences.*' => 'string|max:50',
        ]);

        $this->saveData([
            'competences' => array_unique($validated['competences']),
        ]);

        return redirect()->route('creation.contexte');
    }

    public function stepContexte(): View
    {
        $data = $this->getData();

        $historiques = Historique::with(['ideaux', 'defauts', 'attachements', 'personnalites'])->get();
        $selectedHistoriqueModel = $data['historique_id'] ? $historiques->firstWhere('id', $data['historique_id']) : null;

        $outils = [
            'Aucun', 'Kit de calligraphe', 'Kit de charpentier',
            'Kit de cuisinier', 'Kit de joueur', 'Kit de potier',
            'Instruments de musique', 'Outils de voleur',
        ];

        $langues = [
            'Commun', 'Abyssal', 'Aérien', 'Ancien',
            'Bestiaire', 'Celte', 'Drow', 'Dwarvique',
            'Elfe', 'Géant', 'Gnôme', 'Gobelin',
            'Halfling', 'Hidana', 'Immortel', 'Nain',
            'Orc', 'Souterrain', 'Titan', 'Sylvain',
        ];

        $equipements = [
            'Harpe', 'Livre', 'Épée', 'Arc court',
            'Bouclier', 'Armure de cuir', 'Sac à dos',
            'Kit d\'explorateur', 'Lanternes', 'Cordes',
        ];

        return view('personnages.creation.contexte', [
            'historiques' => $historiques,
            'selectedHistoriqueModel' => $selectedHistoriqueModel,
            'outils' => $outils,
            'langues' => $langues,
            'equipements' => $equipements,
            'selectedHistorique' => $data['historique_id'],
            'selectedIdeal' => $data['ideal_id'],
            'selectedDefaut' => $data['defaut_id'],
            'selectedAttachement' => $data['attachement_id'],
            'selectedPersonnalite' => $data['personnalite_id'],
            'selectedOutil' => $data['outil'],
            'selectedLangues' => $data['langues'] ?? [],
            'selectedEquipements' => $data['equipement'] ?? [],
            'currentStep' => 6,
            'progress' => $this->getProgress(),
        ]);
    }

    public function saveContexte(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'historique_id' => 'nullable|integer|exists:historiques,id',
            'ideal_id' => 'nullable|integer|exists:ideaux,id',
            'defaut_id' => 'nullable|integer|exists:defauts,id',
            'attachement_id' => 'nullable|integer|exists:attachements,id',
            'personnalite_id' => 'nullable|integer|exists:personnalites,id',
            'outil' => 'nullable|string|max:50',
            'langues' => 'nullable|array|min:2',
            'langues.*' => 'string|max:50',
            'equipements' => 'nullable|array',
            'equipements.*' => 'string|max:50',
            'nom' => 'required|string|max:100',
            'alignement' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $this->saveData([
            'historique_id' => $validated['historique_id'] ?? null,
            'ideal_id' => $validated['ideal_id'] ?? null,
            'defaut_id' => $validated['defaut_id'] ?? null,
            'attachement_id' => $validated['attachement_id'] ?? null,
            'personnalite_id' => $validated['personnalite_id'] ?? null,
            'outil' => $validated['outil'] === 'Aucun' ? null : ($validated['outil'] ?? null),
            'langues' => $validated['langues'] ?? [],
            'equipement' => $validated['equipements'] ?? [],
            'nom' => $validated['nom'],
            'alignement' => $validated['alignement'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('creation.confirmation');
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

        $abilites = $data['abilites'];
        $pv = $this->calculatePV($abilites, $classe);

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

            return max($de, $conMod) + $conMod;
        }

        return 10 + $conMod;
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

        $payload = [
            'utilisateur_id' => auth()->id() ?? Utilisateur::query()->firstOrFail()->getKey(),
            'race_id' => $data['race_id'],
            'sous_race_id' => $data['sous_race_id'],
            'classe_id' => $data['classe_id'],
            'sous_classe_id' => $data['sous_classe_id'],
            'historique_id' => $data['historique_id'] ?? null,
            'ideal_id' => $data['ideal_id'] ?? null,
            'defaut_id' => $data['defaut_id'] ?? null,
            'attachement_id' => $data['attachement_id'] ?? null,
            'personnalite_id' => $data['personnalite_id'] ?? null,
            'abilites' => $data['abilites'],
            'competences' => $data['competences'],
            'nom' => $data['nom'],
            'niveau' => $niveau,
            'alignement' => $data['alignement'],
            'description' => $data['description'],
            'point_de_vie' => $this->calculatePV($data['abilites'], Classe::find($data['classe_id'])),
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

        if (!$historique) {
            return response()->json(['error' => 'Historique introuvable'], 404);
        }

        return response()->json($historique);
    }
}
