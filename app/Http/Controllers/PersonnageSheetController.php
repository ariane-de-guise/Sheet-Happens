<?php

namespace App\Http\Controllers;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\Historique;
use App\Models\ListeCompetence;
use App\Models\Personnage;
use App\Models\Race;
use App\Models\SousClasse;
use App\Models\SousRace;
use App\Models\Utilisateur;
use App\Services\FicheDePersonnageService;
use App\Services\MonteeNiveauService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PersonnageSheetController extends Controller
{
    public function __construct(
        private readonly FicheDePersonnageService $fiche,
        private readonly MonteeNiveauService $monteeNiveau,
    ) {}

    public function index(): View
    {
        $userId = auth()->id() ?? Utilisateur::query()->value('id');
        $personnages = Personnage::query()
            ->when($userId, fn ($query) => $query->where('utilisateur_id', $userId))
            ->with(['race', 'classe', 'abilites'])
            ->get();

        return view('personnages.index', [
            'personnages' => $personnages,
        ]);
    }

    public function show(Personnage $personnage): View
    {
        $this->authorizeOwner($personnage);

        $personnage->load([
            'race', 'sousRace', 'classe', 'sousClasse',
            'abilites', 'listeCompetence',
            'historique', 'ideal', 'defaut', 'attachement', 'personnalite',
            'objets',
        ]);

        return view('personnages.show', [
            'personnage' => $personnage,
            'bonusMaitrise' => $this->fiche->bonusMaitrise($personnage->niveau),
            'jetsDeSauvegarde' => $this->fiche->jetsDeSauvegarde($personnage),
            'competences' => $this->fiche->competences($personnage),
            'perceptionPassive' => $this->fiche->perceptionPassive($personnage),
            'classeArmure' => $this->fiche->classeArmure($personnage),
            'actions' => $this->fiche->actions($personnage),
            'seuilExperience' => $this->monteeNiveau->xpProchainNiveau($personnage),
        ]);
        // Section "Traits" volontairement vide pour l'instant (à faire plus
        // tard) : voir FicheDePersonnageService::traits(), déjà prête et
        // testée, en attendant d'être branchée sur la vue.
    }

    public function getTraits(Personnage $personnage): View
    {
        $this->authorizeOwner($personnage);

        $personnage->load([
            'race.traits', 'sousRace.traits',
        ]);

        $raceTraits = $personnage->race->traits;
        $sousRaceTraits = $personnage->sousRace?->traits ?? collect();

        return view('personnages.traits.index', [
            'personnage' => $personnage,
            'raceTraits' => $raceTraits,
            'sousRaceTraits' => $sousRaceTraits,
        ]);

    }

    public function edit(Personnage $personnage): View
    {
        $this->authorizeOwner($personnage);

        $personnage->load([
            'race', 'sousRace', 'classe', 'sousClasse',
            'abilites', 'listeCompetence',
            'historique', 'ideal', 'defaut', 'attachement', 'personnalite',
        ]);

        $races = Race::with('sousRaces')->get();
        $classes = Classe::with('sousClasses')->get();
        $historiques = Historique::with(['ideaux', 'defauts', 'attachements', 'personnalites'])->get();

        $allCompetences = [
            'Acrobaties', 'Arcanes', 'Athlétisme', 'Art de la duplicité',
            'Discrétion', 'Dressage', 'Escamotage', 'Histoire',
            'Intimidation', 'Intuition', 'Investigation', 'Médecine',
            'Nature', 'Perception', 'Performance', 'Persuasion',
            'Religion', 'Survie',
        ];

        $langues = [
            'Commun', 'Abyssal', 'Aérien', 'Ancien',
            'Bestiaire', 'Celte', 'Drow', 'Dwarvique',
            'Elfe', 'Géant', 'Gnôme', 'Gobelin',
            'Halfling', 'Hidana', 'Immortel', 'Nain',
            'Orc', 'Souterrain', 'Titan', 'Sylvain',
        ];

        return view('personnages.edit', [
            'personnage' => $personnage,
            'races' => $races,
            'classes' => $classes,
            'historiques' => $historiques,
            'allCompetences' => $allCompetences,
            'langues' => $langues,
        ]);
    }

    public function update(Request $request, Personnage $personnage): RedirectResponse
    {
        $this->authorizeOwner($personnage);

        $submittedFields = array_keys($request->except(['_token', '_method']));
        if ($submittedFields === ['niveau']) {
            $validated = $request->validate([
                'niveau' => 'required|integer|min:1|max:20',
            ]);

            $personnage->update(['niveau' => $validated['niveau']]);

            return redirect()->route('personnages.show', $personnage)
                ->with('success', 'Niveau mis à jour avec succès !');
        }

        if ($submittedFields === ['point_de_vie']) {
            $validated = $request->validate([
                'point_de_vie' => 'required|integer|min:1',
            ]);
            $personnage->load(['abilites', 'classe']);
            $this->validateHitPoints($validated['point_de_vie'], $personnage->niveau, $personnage->abilites, $personnage->classe);

            $personnage->update(['point_de_vie' => $validated['point_de_vie']]);

            return redirect()->route('personnages.show', $personnage)
                ->with('success', 'Points de vie mis à jour avec succès !');
        }

        $personnage->load(['abilites', 'listeCompetence', 'classe']);

        $niveau = (int) $request->input('niveau', $personnage->niveau);

        $maxCompetences = $personnage->classe?->nombre_competences ?? 2;
        $maxLangues = $this->getMaxLanguesByLevel($niveau);

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'race_id' => 'required|integer|exists:races,id',
            'sous_race_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_races', 'id')->where(function ($query) use ($request) {
                    return $query->where('race_id', $request->input('race_id'));
                }),
            ],
            'classe_id' => 'required|integer|exists:classes,id',
            'sous_classe_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_classes', 'id')->where(function ($query) use ($request) {
                    return $query->where('classe_id', $request->input('classe_id'));
                }),
            ],
            'niveau' => ['required', 'integer', Rule::in([(int) $personnage->niveau])],
            'alignement' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'point_de_vie' => 'required|integer|min:1',
            'force' => 'required|integer|min:1|max:20',
            'dexterite' => 'required|integer|min:1|max:20',
            'constitution' => 'required|integer|min:1|max:20',
            'intelligence' => 'required|integer|min:1|max:20',
            'sagesse' => 'required|integer|min:1|max:20',
            'charisme' => 'required|integer|min:1|max:20',
            'competences' => ['nullable', 'array'],
            'competences.*' => 'string|max:50',
            'langues' => ['nullable', 'array'],
            'langues.*' => 'string|max:50',
            'historique_id' => 'nullable|integer|exists:historiques,id',
            'ideal_id' => 'nullable|integer|exists:ideaux,id',
            'defaut_id' => 'nullable|integer|exists:defauts,id',
            'attachement_id' => 'nullable|integer|exists:attachements,id',
            'personnalite_id' => 'nullable|integer|exists:personnalites,id',
        ], [
        ]);

        $historiqueCompetences = [];
        $historiqueIds = array_filter([
            $personnage->historique_id,
            $validated['historique_id'] ?? null,
        ]);
        foreach (array_unique($historiqueIds) as $historiqueId) {
            $historiqueCompetences = array_merge(
                $historiqueCompetences,
                Historique::find($historiqueId)?->competences ?? [],
            );
        }
        $competences = array_values(array_unique(array_merge(
            $validated['competences'] ?? [],
            $historiqueCompetences,
        )));
        $anciennesCompetences = array_values(array_unique($personnage->listeCompetence->competences ?? []));
        $competencesClasse = array_values(array_diff($competences, $historiqueCompetences));
        $anciennesCompetencesClasse = array_values(array_diff($anciennesCompetences, $personnage->historique?->competences ?? []));
        $competencesTriees = $competencesClasse;
        $anciennesCompetencesTriees = $anciennesCompetencesClasse;
        sort($competencesTriees);
        sort($anciennesCompetencesTriees);
        if ($competencesTriees !== $anciennesCompetencesTriees && count($competencesClasse) > $maxCompetences) {
            throw ValidationException::withMessages([
                'competences' => "Vous ne pouvez avoir que $maxCompetences compétence(s) à ce niveau.",
            ]);
        }

        $langues = array_values(array_unique($validated['langues'] ?? []));
        $anciennesLangues = array_values(array_unique($personnage->langues ?? []));
        $languesTriees = $langues;
        $anciennesLanguesTriees = $anciennesLangues;
        sort($languesTriees);
        sort($anciennesLanguesTriees);
        if ($languesTriees !== $anciennesLanguesTriees && count($langues) > $maxLangues) {
            throw ValidationException::withMessages([
                'langues' => "Vous ne pouvez avoir que $maxLangues langue(s) à ce niveau.",
            ]);
        }

        $sousRaceId = $validated['sous_race_id'] ?? null;
        if ($sousRaceId) {
            $sr = SousRace::find($sousRaceId);
            if (! $sr || $sr->race_id !== $validated['race_id']) {
                $sousRaceId = null;
            }
        }

        $sousClasseId = $validated['sous_classe_id'] ?? null;
        if ($sousClasseId) {
            $sc = SousClasse::find($sousClasseId);
            $classe = Classe::find($validated['classe_id']);
            if (! $sc || ! $classe || $sc->classe_id !== $validated['classe_id'] || $classe->niveau_sous_classe > $validated['niveau']) {
                $sousClasseId = null;
            }
        }

        if ((int) $validated['classe_id'] !== (int) $personnage->classe_id) {
            throw ValidationException::withMessages([
                'classe_id' => 'La classe ne peut pas être changée sans gérer le multiclassage.',
            ]);
        }

        $ancienneClasse = $personnage->classe;
        $anciennesAbilites = $personnage->abilites;
        $nouvellesAbilites = [
            'force' => $validated['force'],
            'dexterite' => $validated['dexterite'],
            'constitution' => $validated['constitution'],
            'intelligence' => $validated['intelligence'],
            'sagesse' => $validated['sagesse'],
            'charisme' => $validated['charisme'],
        ];
        $this->validateAbilityScoreIncrease(
            $nouvellesAbilites,
            $anciennesAbilites,
            $personnage->niveau,
            $validated['niveau'],
            $ancienneClasse,
        );

        if ((int) $validated['point_de_vie'] !== (int) $personnage->point_de_vie) {
            $this->validateHitPoints($validated['point_de_vie'], $validated['niveau'], (object) $nouvellesAbilites, $ancienneClasse);
        }

        $personnage->update([
            'nom' => $validated['nom'],
            'race_id' => $validated['race_id'],
            'sous_race_id' => $sousRaceId,
            'classe_id' => $validated['classe_id'],
            'sous_classe_id' => $sousClasseId,
            'historique_id' => $validated['historique_id'] ?? null,
            'ideal_id' => $validated['ideal_id'] ?? null,
            'defaut_id' => $validated['defaut_id'] ?? null,
            'attachement_id' => $validated['attachement_id'] ?? null,
            'personnalite_id' => $validated['personnalite_id'] ?? null,
            'niveau' => $validated['niveau'],
            'alignement' => $validated['alignement'] ?? null,
            'description' => $validated['description'] ?? null,
            'point_de_vie' => $validated['point_de_vie'],
            'outil' => $validated['outil'] ?? null,
            'langues' => $langues,
            'equipement' => array_values(array_unique($validated['equipements'] ?? [])),
        ]);

        if ($personnage->abilites) {
            $personnage->abilites->update([
                'force' => $validated['force'],
                'dexterite' => $validated['dexterite'],
                'constitution' => $validated['constitution'],
                'intelligence' => $validated['intelligence'],
                'sagesse' => $validated['sagesse'],
                'charisme' => $validated['charisme'],
            ]);
        }

        if ($personnage->listeCompetence) {
            $personnage->listeCompetence->update([
                'competences' => $competences,
            ]);
        }

        return redirect()->route('personnages.show', $personnage)
            ->with('success', 'Personnage mis à jour avec succès !');
    }

    /**
     * Enregistre une photo de personnage envoyée par le joueur, en
     * remplacement du logo par défaut affiché sur la fiche. L'ancien
     * fichier (s'il y en avait un) est supprimé du disque pour ne pas
     * accumuler des images orphelines.
     */
    public function updatePhoto(Request $request, Personnage $personnage): RedirectResponse
    {
        $this->authorizeOwner($personnage);

        $validated = $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $ancienChemin = $personnage->photo;

        $chemin = $validated['photo']->store('personnages', 'public');
        $personnage->update(['photo' => $chemin]);

        if ($ancienChemin) {
            Storage::disk('public')->delete($ancienChemin);
        }

        return redirect()->route('personnages.show', $personnage)
            ->with('success', 'Photo mise à jour avec succès !');
    }

    /**
     * Ajoute de l'expérience au personnage. S'il en a assez pour franchir
     * le seuil de son niveau actuel, on l'envoie directement sur l'écran
     * de montée de niveau pour qu'il y fasse ses choix (sous-classe,
     * amélioration de caractéristiques/don, dé de vie), plutôt que de
     * monter le niveau tout seul sans lui laisser la main.
     */
    public function ajouterExperience(Request $request, Personnage $personnage): RedirectResponse
    {
        $this->authorizeOwner($personnage);

        $validated = $request->validate([
            'experience' => 'required|integer|min:1|max:1000000',
        ]);

        $personnage->increment('experience', $validated['experience']);

        if ($this->monteeNiveau->peutMonterDeNiveau($personnage)) {
            return redirect()->route('personnages.montee-niveau', $personnage)
                ->with('success', "{$personnage->nom} a assez d'expérience pour monter de niveau !");
        }

        return redirect()->route('personnages.show', $personnage)
            ->with('success', "{$validated['experience']} points d'expérience ajoutés.");
    }

    /**
     * Supprime définitivement un personnage ainsi que ses données associées
     * (caractéristiques et liste de compétences), qui lui sont propres et ne
     * sont partagées avec aucun autre personnage.
     *
     * L'opération est effectuée dans une transaction afin de garantir que le
     * personnage et ses données associées sont soit tous supprimés, soit
     * aucun ne l'est en cas d'erreur.
     */
    public function destroy(Personnage $personnage): RedirectResponse
    {
        $this->authorizeOwner($personnage);

        DB::transaction(function () use ($personnage): void {
            $abilitesId = $personnage->abilites_id;
            $listeCompetenceId = $personnage->liste_competence_id;

            $personnage->delete();

            Abilite::whereKey($abilitesId)->delete();
            ListeCompetence::whereKey($listeCompetenceId)->delete();
        });

        if ($personnage->photo) {
            Storage::disk('public')->delete($personnage->photo);
        }

        return redirect()->route('personnages.index')
            ->with('success', 'Personnage supprimé avec succès.');
    }

    protected function authorizeOwner(Personnage $personnage): void
    {
        abort_unless($personnage->utilisateur_id === Auth::id(), 403);
    }

    protected function getMaxCompetencesByLevel(int $niveau): int
    {
        return 2 + (int) floor(($niveau - 1) / 2);
    }

    protected function getMaxLanguesByLevel(int $niveau): int
    {
        return 2 + (int) floor(($niveau - 1) / 3);
    }

    protected function validateAbilityScoreIncrease(
        array $newScores,
        ?object $oldScores,
        int $oldLevel,
        int $newLevel,
        ?Classe $classe,
    ): void {
        if (! $oldScores) {
            return;
        }

        $increases = [];
        foreach (array_keys($newScores) as $ability) {
            $difference = $newScores[$ability] - (int) $oldScores->{$ability};
            if ($difference < 0) {
                throw ValidationException::withMessages([
                    $ability => 'Une caractéristique ne peut pas diminuer lors d’une amélioration D&D 5e.',
                ]);
            }
            if ($difference > 0) {
                $increases[$ability] = $difference;
            }
        }

        if ($increases === []) {
            return;
        }

        $asiCount = count(array_filter(
            $this->getAbilityScoreImprovementLevels($classe),
            fn (int $level): bool => $level > $oldLevel && $level <= $newLevel,
        ));
        $maximumIncrease = $asiCount * 2;

        if ($asiCount === 0 || array_sum($increases) > $maximumIncrease || count($increases) > 2 || max($increases) > 2) {
            $message = "Les caractéristiques ne peuvent augmenter qu’au niveau d’une amélioration de caractéristiques (ASI), avec un maximum de +2 par ASI.";
            throw ValidationException::withMessages(array_fill_keys(array_keys($increases), $message));
        }
    }

    protected function getAbilityScoreImprovementLevels(?Classe $classe): array
    {
        $levels = [4, 8, 12, 16, 19];
        $className = strtolower($classe?->nom ?? '');

        if (str_contains($className, 'guerrier')) {
            $levels = [4, 6, 8, 12, 14, 16, 19];
        } elseif (str_contains($className, 'roublard')) {
            $levels = [4, 8, 10, 12, 16, 19];
        }

        return $levels;
    }

    protected function validateHitPoints(int $hitPoints, int $level, ?object $abilities, ?Classe $classe): void
    {
        if (! $abilities) {
            return;
        }

        $hitDie = (int) preg_replace('/[^0-9]/', '', (string) ($classe?->de_de_vie ?? '10'));
        $constitutionModifier = (int) floor(((int) $abilities->constitution - 10) / 2);
        $firstLevelHitPoints = max(1, $hitDie + $constitutionModifier);
        $minimum = $firstLevelHitPoints + max(0, $level - 1) * max(1, 1 + $constitutionModifier);
        $maximum = $firstLevelHitPoints + max(0, $level - 1) * max(1, $hitDie + $constitutionModifier);

        if ($hitPoints < $minimum || $hitPoints > $maximum) {
            throw ValidationException::withMessages([
                'point_de_vie' => "Les PV doivent être compris entre $minimum et $maximum pour ce niveau, cette classe et cette Constitution.",
            ]);
        }
    }
}
