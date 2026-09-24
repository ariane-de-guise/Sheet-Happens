<?php

namespace App\Http\Controllers;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\Historique;
use App\Models\LimiteMagie;
use App\Models\LimiteMagieClasse;
use App\Models\LimiteMagieSousClasse;
use App\Models\ListeCompetence;
use App\Models\Personnage;
use App\Models\Race;
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
            'aDesSorts' => $this->aDesSorts($personnage),
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
            'niveau' => ['required', 'integer', Rule::in([(int) $personnage->niveau, (int) $personnage->niveau + 1])],
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
            'historique_id' => ['required', 'integer', 'exists:historiques,id'],
            'ideal_id' => [
                'required',
                'integer',
                Rule::exists('ideaux', 'id')->where(function ($query) use ($request) {
                    return $query->where('historique_id', $request->input('historique_id'));
                }),
            ],
            'defaut_id' => [
                'required',
                'integer',
                Rule::exists('defauts', 'id')->where(function ($query) use ($request) {
                    return $query->where('historique_id', $request->input('historique_id'));
                }),
            ],
            'attachement_id' => [
                'required',
                'integer',
                Rule::exists('attachements', 'id')->where(function ($query) use ($request) {
                    return $query->where('historique_id', $request->input('historique_id'));
                }),
            ],
            'personnalite_id' => [
                'required',
                'integer',
                Rule::exists('personnalites', 'id')->where(function ($query) use ($request) {
                    return $query->where('historique_id', $request->input('historique_id'));
                }),
            ],
        ], [
            'historique_id.required' => 'Vous devez choisir un historique.',
            'ideal_id.required' => 'Vous devez choisir un idéal.',
            'ideal_id.exists' => 'L’idéal choisi ne correspond pas à l’historique sélectionné.',
            'defaut_id.required' => 'Vous devez choisir un défaut.',
            'defaut_id.exists' => 'Le défaut choisi ne correspond pas à l’historique sélectionné.',
            'attachement_id.required' => 'Vous devez choisir un attachement.',
            'attachement_id.exists' => 'L’attachement choisi ne correspond pas à l’historique sélectionné.',
            'personnalite_id.required' => 'Vous devez choisir un trait de personnalité.',
            'personnalite_id.exists' => 'Le trait de personnalité choisi ne correspond pas à l’historique sélectionné.',
        ]);

        $race = Race::with('sousRaces')->find($validated['race_id']);
        if ($race->sousRaces->isNotEmpty() && empty($validated['sous_race_id'])) {
            throw ValidationException::withMessages([
                'sous_race_id' => 'Vous devez choisir une sous-race pour cette race.',
            ]);
        }

        // Un changement de classe ici est un reclassement complet (pas un
        // multiclassage 5e à la carte) : le personnage abandonne son
        // ancienne classe pour la nouvelle, donc toutes les limites
        // dépendantes de la classe (compétences, dé de vie, ASI) se basent
        // désormais sur la nouvelle classe choisie.
        $nouvelleClasse = Classe::find($validated['classe_id']);
        $maxCompetences = $nouvelleClasse?->nombre_competences ?? 2;

        $sousClasseRequise = $nouvelleClasse
            && $nouvelleClasse->niveau_sous_classe <= (int) $validated['niveau']
            && $nouvelleClasse->sousClasses()->exists();
        if ($sousClasseRequise && empty($validated['sous_classe_id'])) {
            throw ValidationException::withMessages([
                'sous_classe_id' => 'Vous devez choisir une sous-classe pour cette classe.',
            ]);
        }

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

        $sousClasseId = $validated['sous_classe_id'] ?? null;
        if ($sousClasseId) {
            if ($nouvelleClasse->niveau_sous_classe > (int) $validated['niveau']) {
                throw ValidationException::withMessages([
                    'sous_classe_id' => "Cette sous-classe n'est disponible qu'à partir du niveau {$nouvelleClasse->niveau_sous_classe}.",
                ]);
            }
        }

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
            $nouvelleClasse,
        );

        if ((int) $validated['point_de_vie'] !== (int) $personnage->point_de_vie) {
            $this->validateHitPoints($validated['point_de_vie'], $validated['niveau'], (object) $nouvellesAbilites, $nouvelleClasse);
        }

        $personnage->update([
            'nom' => $validated['nom'],
            'race_id' => $validated['race_id'],
            'sous_race_id' => $sousRaceId,
            'classe_id' => $validated['classe_id'],
            'sous_classe_id' => $sousClasseId,
            'historique_id' => $validated['historique_id'],
            'ideal_id' => $validated['ideal_id'],
            'defaut_id' => $validated['defaut_id'],
            'attachement_id' => $validated['attachement_id'],
            'personnalite_id' => $validated['personnalite_id'],
            'niveau' => $validated['niveau'],
            'alignement' => $validated['alignement'] ?? null,
            'description' => $validated['description'] ?? null,
            'point_de_vie' => $validated['point_de_vie'],
            'langues' => $langues,
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

    /**
     * Même règle que la page des sorts : limites de la classe si elle est
     * magique, sinon de la sous-classe, au niveau atteint. Vrai seulement si
     * elles accordent au moins un tour de magie ou un emplacement de sort.
     */
    protected function aDesSorts(Personnage $personnage): bool
    {
        $niveau = (int) $personnage->niveau;

        if (LimiteMagieClasse::query()->where('id_Classe', $personnage->classe_id)->exists()) {
            $ligne = LimiteMagieClasse::query()
                ->where('id_Classe', $personnage->classe_id)
                ->where('Niveau', '<=', $niveau)
                ->orderByDesc('Niveau')
                ->first();
        } elseif ($personnage->sous_classe_id) {
            $ligne = LimiteMagieSousClasse::query()
                ->where('id_SousClasse', $personnage->sous_classe_id)
                ->where('Niveau', '<=', $niveau)
                ->orderByDesc('Niveau')
                ->first();
        } else {
            return false;
        }

        $limite = $ligne ? LimiteMagie::find($ligne->id_LimiteMagie) : null;

        return $limite !== null
            && ((int) $limite->TourMagie > 0
                || (int) $limite->Niveau1 > 0
                || (int) $limite->Niveau2 > 0
                || (int) $limite->Niveau3 > 0);
    }

    protected function getMaxLanguesByLevel(int $niveau): int
    {
        return 2 + (int) floor(($niveau - 1) / 3);
    }

    protected function validateAbilityScoreIncrease(
        array $newScores,
        object $oldScores,
        int $oldLevel,
        int $newLevel,
        ?Classe $classe,
    ): void {
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
            $message = 'Les caractéristiques ne peuvent augmenter qu’au niveau d’une amélioration de caractéristiques (ASI), avec un maximum de +2 par ASI.';
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

    protected function validateHitPoints(int $hitPoints, int $level, object $abilities, ?Classe $classe): void
    {
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
