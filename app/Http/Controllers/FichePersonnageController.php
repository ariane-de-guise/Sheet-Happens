<?php

namespace App\Http\Controllers;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\Historique;
use App\Models\ListeCompetence;
use App\Models\Personnage;
use App\Models\PersonnageSort;
use App\Models\Race;
use App\Services\CapaciteService;
use App\Services\FicheDePersonnageService;
use App\Services\MonteeNiveauService;
use App\Services\SortService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FichePersonnageController extends Controller
{
    /**
     * Niveaux d'amélioration de caractéristiques (ASI) atteignables : jusqu'au
     * niveau 5 (maximum pris en charge), seul le niveau 4 en accorde une, et
     * ce pour toutes les classes (celles du Guerrier et du Roublard
     * n'arrivent qu'aux niveaux 6 et 10).
     */
    private const NIVEAUX_ASI = [4];

    public function __construct(
        private readonly FicheDePersonnageService $fiche,
        private readonly MonteeNiveauService $monteeNiveau,
        private readonly CapaciteService $capacites,
        private readonly SortService $sorts,
    ) {}

    public function index(): View
    {
        $personnages = Personnage::query()
            ->where('utilisateur_id', Auth::id())
            ->with(['race', 'classe', 'abilites'])
            ->get();

        return view('personnages.index', [
            'personnages' => $personnages,
        ]);
    }

    public function show(Personnage $personnage): View
    {
        $this->verifierProprietaire($personnage);

        $personnage->load([
            'race', 'sousRace', 'classe', 'sousClasse',
            'abilites', 'listeCompetence',
            'historique', 'ideal', 'defaut', 'attachement', 'personnalite',
            'objets', 'capacites.sousClasse', 'dons',
        ]);

        return view('personnages.show', [
            'personnage' => $personnage,
            'bonusMaitrise' => $this->fiche->bonusMaitrise($personnage->niveau),
            'jetsDeSauvegarde' => $this->fiche->jetsDeSauvegarde($personnage),
            'competences' => $this->fiche->competences($personnage),
            'perceptionPassive' => $this->fiche->perceptionPassive($personnage),
            'classeArmure' => $this->fiche->classeArmure($personnage),
            'actions' => $this->fiche->actions($personnage),
            'aDesSorts' => $this->sorts->aDesSorts($personnage),
            'seuilExperience' => $this->monteeNiveau->xpProchainNiveau($personnage),
            'peutMonterDeNiveau' => $this->monteeNiveau->peutMonterDeNiveau($personnage),
        ]);
    }

    public function afficherTraits(Personnage $personnage): View
    {
        $this->verifierProprietaire($personnage);

        $personnage->load([
            'race.traits', 'sousRace.traits', 'classe', 'capacites.sousClasse', 'dons',
        ]);

        $raceTraits = $personnage->race->traits;
        $sousRaceTraits = $personnage->sousRace?->traits ?? collect();

        return view('personnages.traits.index', [
            'personnage' => $personnage,
            'raceTraits' => $raceTraits,
            'sousRaceTraits' => $sousRaceTraits,
            'capacites' => $personnage->capacites,
            'dons' => $personnage->dons,
        ]);

    }

    public function edit(Personnage $personnage): View
    {
        $this->verifierProprietaire($personnage);

        $personnage->load([
            'race', 'sousRace', 'classe', 'sousClasse',
            'abilites', 'listeCompetence',
            'historique', 'ideal', 'defaut', 'attachement', 'personnalite',
        ]);

        $races = Race::with('sousRaces')->get();
        $classes = Classe::with('sousClasses')->get();
        $historiques = Historique::with(['ideaux', 'defauts', 'attachements', 'personnalites'])->get();

        $toutesCompetences = [
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
            'toutesCompetences' => $toutesCompetences,
            'langues' => $langues,
        ]);
    }

    public function update(Request $requete, Personnage $personnage): RedirectResponse
    {
        $this->verifierProprietaire($personnage);

        $champsSoumis = array_keys($requete->except(['_token', '_method']));
        if ($champsSoumis === ['point_de_vie']) {
            $donneesValidees = $requete->validate([
                'point_de_vie' => 'required|integer|min:1',
            ]);
            $personnage->load(['abilites', 'classe']);
            $this->validerPointsDeVie($donneesValidees['point_de_vie'], $personnage->niveau, $personnage->abilites, $personnage->classe);

            $personnage->update(['point_de_vie' => $donneesValidees['point_de_vie']]);

            return redirect()->route('personnages.show', $personnage)
                ->with('succes', 'Points de vie mis à jour avec succès !');
        }

        $personnage->load(['abilites', 'listeCompetence', 'classe']);

        $niveau = (int) $requete->input('niveau', $personnage->niveau);
        $maxLangues = $this->maxLanguesParNiveau($niveau);

        $donneesValidees = $requete->validate([
            'nom' => 'required|string|max:100',
            'race_id' => 'required|integer|exists:races,id',
            'sous_race_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_races', 'id')->where(function ($requeteSql) use ($requete) {
                    return $requeteSql->where('race_id', $requete->input('race_id'));
                }),
            ],
            'classe_id' => 'required|integer|exists:classes,id',
            'sous_classe_id' => [
                'nullable',
                'integer',
                Rule::exists('sous_classes', 'id')->where(function ($requeteSql) use ($requete) {
                    return $requeteSql->where('classe_id', $requete->input('classe_id'));
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
                Rule::exists('ideaux', 'id')->where(function ($requeteSql) use ($requete) {
                    return $requeteSql->where('historique_id', $requete->input('historique_id'));
                }),
            ],
            'defaut_id' => [
                'required',
                'integer',
                Rule::exists('defauts', 'id')->where(function ($requeteSql) use ($requete) {
                    return $requeteSql->where('historique_id', $requete->input('historique_id'));
                }),
            ],
            'attachement_id' => [
                'required',
                'integer',
                Rule::exists('attachements', 'id')->where(function ($requeteSql) use ($requete) {
                    return $requeteSql->where('historique_id', $requete->input('historique_id'));
                }),
            ],
            'personnalite_id' => [
                'required',
                'integer',
                Rule::exists('personnalites', 'id')->where(function ($requeteSql) use ($requete) {
                    return $requeteSql->where('historique_id', $requete->input('historique_id'));
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

        $this->verifierExperiencePourNiveau($personnage, (int) $donneesValidees['niveau']);

        $race = Race::with('sousRaces')->find($donneesValidees['race_id']);
        if ($race->sousRaces->isNotEmpty() && empty($donneesValidees['sous_race_id'])) {
            throw ValidationException::withMessages([
                'sous_race_id' => 'Vous devez choisir une sous-race pour cette race.',
            ]);
        }

        // Un changement de classe ici est un reclassement complet (pas un
        // multiclassage 5e à la carte) : le personnage abandonne son
        // ancienne classe pour la nouvelle, donc toutes les limites
        // dépendantes de la classe (compétences, dé de vie, ASI) se basent
        // désormais sur la nouvelle classe choisie.
        $nouvelleClasse = Classe::find($donneesValidees['classe_id']);
        $maxCompetences = $nouvelleClasse?->nombre_competences ?? 2;

        $sousClasseRequise = $nouvelleClasse
            && $nouvelleClasse->niveau_sous_classe <= (int) $donneesValidees['niveau']
            && $nouvelleClasse->sousClasses()->exists();
        if ($sousClasseRequise && empty($donneesValidees['sous_classe_id'])) {
            throw ValidationException::withMessages([
                'sous_classe_id' => 'Vous devez choisir une sous-classe pour cette classe.',
            ]);
        }

        $historiqueCompetences = [];
        $historiqueIds = array_filter([
            $personnage->historique_id,
            $donneesValidees['historique_id'] ?? null,
        ]);
        foreach (array_unique($historiqueIds) as $historiqueId) {
            $historiqueCompetences = array_merge(
                $historiqueCompetences,
                Historique::find($historiqueId)?->competences ?? [],
            );
        }
        $competences = array_values(array_unique(array_merge(
            $donneesValidees['competences'] ?? [],
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

        $langues = array_values(array_unique($donneesValidees['langues'] ?? []));
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

        $sousRaceId = $donneesValidees['sous_race_id'] ?? null;

        $sousClasseId = $donneesValidees['sous_classe_id'] ?? null;
        if ($sousClasseId) {
            if ($nouvelleClasse->niveau_sous_classe > (int) $donneesValidees['niveau']) {
                throw ValidationException::withMessages([
                    'sous_classe_id' => "Cette sous-classe n'est disponible qu'à partir du niveau {$nouvelleClasse->niveau_sous_classe}.",
                ]);
            }
        }

        $anciennesAbilites = $personnage->abilites;
        $nouvellesAbilites = [
            'force' => $donneesValidees['force'],
            'dexterite' => $donneesValidees['dexterite'],
            'constitution' => $donneesValidees['constitution'],
            'intelligence' => $donneesValidees['intelligence'],
            'sagesse' => $donneesValidees['sagesse'],
            'charisme' => $donneesValidees['charisme'],
        ];
        $this->validerAugmentationCaracteristiques(
            $nouvellesAbilites,
            $anciennesAbilites,
            $personnage->niveau,
            $donneesValidees['niveau'],
        );

        if ((int) $donneesValidees['point_de_vie'] !== (int) $personnage->point_de_vie) {
            $this->validerPointsDeVie($donneesValidees['point_de_vie'], $donneesValidees['niveau'], (object) $nouvellesAbilites, $nouvelleClasse);
        }

        $classeChangee = (int) $donneesValidees['classe_id'] !== (int) $personnage->classe_id;

        DB::transaction(function () use ($personnage, $donneesValidees, $sousRaceId, $sousClasseId, $langues, $competences, $classeChangee): void {
            $personnage->update([
                'nom' => $donneesValidees['nom'],
                'race_id' => $donneesValidees['race_id'],
                'sous_race_id' => $sousRaceId,
                'classe_id' => $donneesValidees['classe_id'],
                'sous_classe_id' => $sousClasseId,
                'historique_id' => $donneesValidees['historique_id'],
                'ideal_id' => $donneesValidees['ideal_id'],
                'defaut_id' => $donneesValidees['defaut_id'],
                'attachement_id' => $donneesValidees['attachement_id'],
                'personnalite_id' => $donneesValidees['personnalite_id'],
                'niveau' => $donneesValidees['niveau'],
                'alignement' => $donneesValidees['alignement'] ?? null,
                'description' => $donneesValidees['description'] ?? null,
                'point_de_vie' => $donneesValidees['point_de_vie'],
                'langues' => $langues,
            ]);

            if ($personnage->abilites) {
                $personnage->abilites->update([
                    'force' => $donneesValidees['force'],
                    'dexterite' => $donneesValidees['dexterite'],
                    'constitution' => $donneesValidees['constitution'],
                    'intelligence' => $donneesValidees['intelligence'],
                    'sagesse' => $donneesValidees['sagesse'],
                    'charisme' => $donneesValidees['charisme'],
                ]);
            }

            if ($personnage->listeCompetence) {
                $personnage->listeCompetence->update([
                    'competences' => $competences,
                ]);
            }

            // Reclassement complet : les sorts appris et préparés venaient de
            // la liste de l'ancienne classe, ils ne sont plus valides.
            if ($classeChangee) {
                PersonnageSort::query()->where('id_Personnage', $personnage->id)->delete();
            }

            // Les capacités découlent de la classe, de la sous-classe et du
            // niveau, qui ont pu changer.
            $this->capacites->synchroniser($personnage);
        });

        return redirect()->route('personnages.show', $personnage)
            ->with('succes', $classeChangee
                ? 'Personnage mis à jour avec succès ! Ses sorts ont été réinitialisés suite au changement de classe.'
                : 'Personnage mis à jour avec succès !');
    }

    /**
     * Enregistre une photo de personnage envoyée par le joueur, en
     * remplacement du logo par défaut affiché sur la fiche. L'ancien
     * fichier (s'il y en avait un) est supprimé du disque pour ne pas
     * accumuler des images orphelines.
     */
    public function modifierPhoto(Request $requete, Personnage $personnage): RedirectResponse
    {
        $this->verifierProprietaire($personnage);

        $donneesValidees = $requete->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $ancienChemin = $personnage->photo;

        $chemin = $donneesValidees['photo']->store('personnages', 'public');
        $personnage->update(['photo' => $chemin]);

        if ($ancienChemin) {
            Storage::disk('public')->delete($ancienChemin);
        }

        return redirect()->route('personnages.show', $personnage)
            ->with('succes', 'Photo mise à jour avec succès !');
    }

    /**
     * Ajoute de l'expérience au personnage. S'il en a assez pour franchir
     * le seuil de son niveau actuel, on l'envoie directement sur l'écran
     * de montée de niveau pour qu'il y fasse ses choix (sous-classe,
     * amélioration de caractéristiques/don, dé de vie), plutôt que de
     * monter le niveau tout seul sans lui laisser la main.
     */
    public function ajouterExperience(Request $requete, Personnage $personnage): RedirectResponse
    {
        $this->verifierProprietaire($personnage);

        $donneesValidees = $requete->validate([
            'experience' => 'required|integer|min:1|max:1000000',
        ]);

        $personnage->increment('experience', $donneesValidees['experience']);

        if ($this->monteeNiveau->peutMonterDeNiveau($personnage)) {
            return redirect()->route('personnages.montee-niveau', $personnage)
                ->with('succes', "{$personnage->nom} a assez d'expérience pour monter de niveau !");
        }

        return redirect()->route('personnages.show', $personnage)
            ->with('succes', "{$donneesValidees['experience']} points d'expérience ajoutés.");
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
        $this->verifierProprietaire($personnage);

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
            ->with('succes', 'Personnage supprimé avec succès.');
    }

    protected function verifierProprietaire(Personnage $personnage): void
    {
        abort_unless($personnage->utilisateur_id === Auth::id(), 403);
    }

    /** Refuse une montée de niveau que l'expérience du personnage ne permet pas. */
    protected function verifierExperiencePourNiveau(Personnage $personnage, int $niveau): void
    {
        if (! $this->monteeNiveau->peutAtteindreNiveau($personnage, $niveau)) {
            throw ValidationException::withMessages([
                'niveau' => $this->monteeNiveau->messageExperienceInsuffisante($personnage, $niveau),
            ]);
        }
    }

    protected function maxLanguesParNiveau(int $niveau): int
    {
        return 2 + (int) floor(($niveau - 1) / 3);
    }

    protected function validerAugmentationCaracteristiques(
        array $nouveauxScores,
        object $anciensScores,
        int $ancienNiveau,
        int $nouveauNiveau,
    ): void {
        $augmentations = [];
        foreach (array_keys($nouveauxScores) as $caracteristique) {
            $difference = $nouveauxScores[$caracteristique] - (int) $anciensScores->{$caracteristique};
            if ($difference < 0) {
                throw ValidationException::withMessages([
                    $caracteristique => 'Une caractéristique ne peut pas diminuer lors d’une amélioration D&D 5e.',
                ]);
            }
            if ($difference > 0) {
                $augmentations[$caracteristique] = $difference;
            }
        }

        if ($augmentations === []) {
            return;
        }

        $nombreAsi = count(array_filter(
            self::NIVEAUX_ASI,
            fn (int $niveauVise): bool => $niveauVise > $ancienNiveau && $niveauVise <= $nouveauNiveau,
        ));
        $augmentationMaximale = $nombreAsi * 2;

        if ($nombreAsi === 0 || array_sum($augmentations) > $augmentationMaximale || count($augmentations) > 2 || max($augmentations) > 2) {
            $message = 'Les caractéristiques ne peuvent augmenter qu’au niveau d’une amélioration de caractéristiques (ASI), avec un maximum de +2 par ASI.';
            throw ValidationException::withMessages(array_fill_keys(array_keys($augmentations), $message));
        }
    }

    protected function validerPointsDeVie(int $pointsDeVie, int $niveauVise, object $caracteristiques, ?Classe $classe): void
    {
        $deDeVie = (int) preg_replace('/[^0-9]/', '', (string) ($classe?->de_de_vie ?? '10'));
        $modificateurConstitution = (int) floor(((int) $caracteristiques->constitution - 10) / 2);
        $pvPremierNiveau = max(1, $deDeVie + $modificateurConstitution);
        $minimum = $pvPremierNiveau + max(0, $niveauVise - 1) * max(1, 1 + $modificateurConstitution);
        $maximum = $pvPremierNiveau + max(0, $niveauVise - 1) * max(1, $deDeVie + $modificateurConstitution);

        if ($pointsDeVie < $minimum || $pointsDeVie > $maximum) {
            throw ValidationException::withMessages([
                'point_de_vie' => "Les PV doivent être compris entre $minimum et $maximum pour ce niveau, cette classe et cette Constitution.",
            ]);
        }
    }
}
