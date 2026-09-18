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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

class PersonnageSheetController extends Controller
{
    public function __construct(private readonly FicheDePersonnageService $fiche) {}

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
        ]);

        return view('personnages.show', [
            'personnage' => $personnage,
            'bonusMaitrise' => $this->fiche->bonusMaitrise($personnage->niveau),
            'jetsDeSauvegarde' => $this->fiche->jetsDeSauvegarde($personnage),
            'competences' => $this->fiche->competences($personnage),
            'perceptionPassive' => $this->fiche->perceptionPassive($personnage),
            'actions' => $this->fiche->actions($personnage),
        ]);
        // Section "Traits" volontairement vide pour l'instant (à faire plus
        // tard) : voir FicheDePersonnageService::traits(), déjà prête et
        // testée, en attendant d'être branchée sur la vue.
    }

    public function inventaire(Personnage $personnage): View
    {
        $this->authorizeOwner($personnage);

        $personnage->load(['race', 'classe', 'historique']);

        return view('personnages.inventaire', [
            'personnage' => $personnage,
            'equipementClasse' => $this->fiche->equipementClasse($personnage),
        ]);
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

        $equipements = [
            'Harpe', 'Livre', 'Épée', 'Arc court',
            'Bouclier', 'Armure de cuir', 'Sac à dos',
            'Kit d\'explorateur', 'Lanternes', 'Cordes',
        ];

        $outils = [
            'Aucun', 'Kit de calligraphe', 'Kit de charpentier',
            'Kit de cuisinier', 'Kit de joueur', 'Kit de potier',
            'Instruments de musique', 'Outils de voleur',
        ];

        return view('personnages.edit', [
            'personnage' => $personnage,
            'races' => $races,
            'classes' => $classes,
            'historiques' => $historiques,
            'allCompetences' => $allCompetences,
            'langues' => $langues,
            'equipements' => $equipements,
            'outils' => $outils,
        ]);
    }

    public function update(Request $request, Personnage $personnage): RedirectResponse
    {
        $this->authorizeOwner($personnage);

        $personnage->load(['abilites', 'listeCompetence']);

        $niveau = (int) $request->input('niveau', $personnage->niveau);

        $maxCompetences = $this->getMaxCompetencesByLevel($niveau);
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
            'force' => 'required|integer|min:1|max:30',
            'dexterite' => 'required|integer|min:1|max:30',
            'constitution' => 'required|integer|min:1|max:30',
            'intelligence' => 'required|integer|min:1|max:30',
            'sagesse' => 'required|integer|min:1|max:30',
            'charisme' => 'required|integer|min:1|max:30',
            'competences' => ['nullable', 'array', "max:$maxCompetences"],
            'competences.*' => 'string|max:50',
            'langues' => ['nullable', 'array', "max:$maxLangues"],
            'langues.*' => 'string|max:50',
            'equipements' => 'nullable|array',
            'equipements.*' => 'string|max:50',
            'historique_id' => 'nullable|integer|exists:historiques,id',
            'ideal_id' => 'nullable|integer|exists:ideaux,id',
            'defaut_id' => 'nullable|integer|exists:defauts,id',
            'attachement_id' => 'nullable|integer|exists:attachements,id',
            'personnalite_id' => 'nullable|integer|exists:personnalites,id',
            'outil' => 'nullable|string|max:50',
        ], [
            'competences.max' => "Vous ne pouvez avoir que $maxCompetences compétence(s) à ce niveau.",
            'langues.max' => "Vous ne pouvez avoir que $maxLangues langue(s) à ce niveau.",
        ]);

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

        if ($personnage->listeCompetence && $request->has('competences')) {
            $personnage->listeCompetence->update([
                'competences' => array_unique($validated['competences'] ?? []),
            ]);
        }

        return redirect()->route('personnages.show', $personnage)
            ->with('success', 'Personnage mis à jour avec succès !');
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

    public function telecharger(Personnage $personnage)
    {
        $personnage->load([
            'race', 'sousRace', 'classe', 'sousClasse',
            'abilites', 'listeCompetence',
            'historique', 'ideal', 'defaut', 'attachement', 'personnalite',
        ]);

        $raceTraits = $personnage->race->traits;
        $sousRaceTraits = $personnage->sousRace?->traits ?? collect();

        return Pdf::view('pdf.fiche-personnage', [
            'personnage' => $personnage,
            'bonusMaitrise' => $this->fiche->bonusMaitrise($personnage->niveau),
            'jetsDeSauvegarde' => $this->fiche->jetsDeSauvegarde($personnage),
            'competences' => $this->fiche->competences($personnage),
            'perceptionPassive' => $this->fiche->perceptionPassive($personnage),
            'actions' => $this->fiche->actions($personnage),
            'raceTraits' => $raceTraits,
            'sousRaceTraits' => $sousRaceTraits,
            'equipementClasse' => $this->fiche->equipementClasse($personnage),
        ])
            ->format('a4')
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->noSandbox();
            })
            ->name(str($personnage->nom)->slug().'-fiche.pdf');
    }
}
