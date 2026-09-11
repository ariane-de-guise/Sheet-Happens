<?php

namespace App\Http\Controllers;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\ListeCompetence;
use App\Models\Personnage;
use App\Models\Race;
use App\Models\SousClasse;
use App\Models\SousRace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PersonnageSheetController extends Controller
{
    public function index(): View
    {
        $personnages = Personnage::where('utilisateur_id', Auth::id())
            ->with(['race', 'classe', 'abilites'])
            ->get();

        return view('personnages.index', [
            'personnages' => $personnages,
        ]);
    }

    public function show(Personnage $personnage): View
    {
        $this->authorizeOwner($personnage);

        $personnage->load(['race', 'sousRace', 'classe', 'sousClasse', 'abilites', 'listeCompetence']);

        return view('personnages.show', [
            'personnage' => $personnage,
        ]);
    }

    public function edit(Personnage $personnage): View
    {
        $this->authorizeOwner($personnage);

        $personnage->load(['race', 'sousRace', 'classe', 'sousClasse', 'abilites', 'listeCompetence']);

        $races = Race::with('sousRaces')->get();
        $classes = Classe::with('sousClasses')->get();

        $allCompetences = [
            'Acrobaties', 'Arcanes', 'Athlétisme', 'Art de la duplicité',
            'Artisanat', 'Attention', 'Dissimulation', 'Histoire',
            'Intimidation', 'Insight', 'Médecine', 'Nature',
            'Perception', 'Performance', 'Persuasion', 'Religion',
            'Sapience animale', 'Survie',
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

        $maitrises = [
            'Religieux', 'Marchand', 'Artisan', 'Noble',
            'Soldat', 'Roublard', 'Erfge', 'Hors-la-loi',
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
            'allCompetences' => $allCompetences,
            'langues' => $langues,
            'equipements' => $equipements,
            'maitrises' => $maitrises,
            'outils' => $outils,
        ]);
    }

    public function update(Request $request, Personnage $personnage): RedirectResponse
    {
        $this->authorizeOwner($personnage);

        $personnage->load(['abilites', 'listeCompetence']);

        $niveau = (int)$request->input('niveau', $personnage->niveau);

        $maxCompetences = $this->getMaxCompetencesByLevel($niveau);
        $maxLangues = $this->getMaxLanguesByLevel($niveau);

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'race_id' => 'required|integer|exists:races,id',
            'sous_race_id' => 'nullable|integer|exists:sous_races,id',
            'classe_id' => 'required|integer|exists:classes,id',
            'sous_classe_id' => 'nullable|integer|exists:sous_classes,id',
            'niveau' => 'required|integer|min:1|max:20',
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
            'maitrise' => 'nullable|string|max:50',
            'outil' => 'nullable|string|max:50',
        ], [
            'competences.max' => "Vous ne pouvez avoir que $maxCompetences compétence(s) à ce niveau.",
            'langues.max' => "Vous ne pouvez avoir que $maxLangues langue(s) à ce niveau.",
        ]);

        $sousRaceId = $validated['sous_race_id'] ?? null;
        if ($sousRaceId) {
            $sr = SousRace::find($sousRaceId);
            if (!$sr || $sr->race_id !== $validated['race_id']) {
                $sousRaceId = null;
            }
        }

        $sousClasseId = $validated['sous_classe_id'] ?? null;
        if ($sousClasseId) {
            $sc = SousClasse::find($sousClasseId);
            if (!$sc || $sc->classe_id !== $validated['classe_id'] || $sc->niveau_sous_classe > $validated['niveau']) {
                $sousClasseId = null;
            }
        }

        $personnage->update([
            'nom' => $validated['nom'],
            'race_id' => $validated['race_id'],
            'sous_race_id' => $sousRaceId,
            'classe_id' => $validated['classe_id'],
            'sous_classe_id' => $sousClasseId,
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

    protected function authorizeOwner(Personnage $personnage): void
    {
        abort_unless($personnage->utilisateur_id === Auth::id(), 403);
    }

    protected function getMaxCompetencesByLevel(int $niveau): int
    {
        return 2 + (int)floor(($niveau - 1) / 2);
    }

    protected function getMaxLanguesByLevel(int $niveau): int
    {
        return 2 + (int)floor(($niveau - 1) / 3);
    }
}
