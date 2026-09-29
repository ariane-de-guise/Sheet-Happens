<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModifierPersonnageRequest;
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
use Illuminate\View\View;

class FichePersonnageController extends Controller
{
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

    public function update(ModifierPersonnageRequest $requete, Personnage $personnage): RedirectResponse
    {
        $donneesValidees = $requete->validated();
        if (array_keys($donneesValidees) === ['point_de_vie']) {
            $personnage->update(['point_de_vie' => $donneesValidees['point_de_vie']]);

            return redirect()->route('personnages.show', $personnage)
                ->with('success', 'Points de vie mis à jour avec succès !');
        }

        $classeChangee = (int) $donneesValidees['classe_id'] !== (int) $personnage->classe_id;
        $pvMaximum = (int) $donneesValidees['point_de_vie'];

        DB::transaction(function () use ($requete, $personnage, $donneesValidees, $classeChangee, $pvMaximum): void {
            $personnage->update([
                'nom' => $donneesValidees['nom'],
                'race_id' => $donneesValidees['race_id'],
                'sous_race_id' => $donneesValidees['sous_race_id'] ?? null,
                'classe_id' => $donneesValidees['classe_id'],
                'sous_classe_id' => $donneesValidees['sous_classe_id'] ?? null,
                'historique_id' => $donneesValidees['historique_id'],
                'ideal_id' => $donneesValidees['ideal_id'],
                'defaut_id' => $donneesValidees['defaut_id'],
                'attachement_id' => $donneesValidees['attachement_id'],
                'personnalite_id' => $donneesValidees['personnalite_id'],
                'alignement' => $donneesValidees['alignement'] ?? null,
                'description' => $donneesValidees['description'] ?? null,
                'point_de_vie' => $pvMaximum,
                // Les dégâts déjà subis sont conservés si le maximum change.
                'point_de_vie_actuel' => max(0, min(
                    $pvMaximum,
                    ($personnage->point_de_vie_actuel ?? $personnage->point_de_vie) + $pvMaximum - (int) $personnage->point_de_vie,
                )),
                'langues' => $requete->languesFinales(),
            ]);

            $personnage->listeCompetence?->update(['competences' => $requete->competencesFinales()]);

            // Reclassement complet : les sorts appris et préparés venaient de
            // la liste de l'ancienne classe, ils ne sont plus valides.
            if ($classeChangee) {
                PersonnageSort::query()->where('personnage_id', $personnage->id)->delete();
            }

            // Les capacités découlent de la classe et de la sous-classe, qui
            // ont pu changer.
            $this->capacites->synchroniser($personnage);
        });

        return redirect()->route('personnages.show', $personnage)
            ->with('succes', $classeChangee
                ? 'Personnage mis à jour avec succès ! Ses sorts ont été réinitialisés suite au changement de classe.'
                : 'Personnage mis à jour avec succès !');
    }

    /**
     * Applique des dégâts ou des soins aux PV actuels du personnage, entre
     * 0 et son maximum de PV.
     */
    public function ajusterPointsDeVie(Request $requete, Personnage $personnage): RedirectResponse
    {
        $donneesValidees = $requete->validate([
            'montant' => 'required|integer|min:1|max:1000',
            'type' => 'required|in:degats,soins',
        ], [], ['montant' => 'nombre de points de vie']);

        $delta = $donneesValidees['type'] === 'degats' ? -$donneesValidees['montant'] : $donneesValidees['montant'];
        $personnage->modifierPointsDeVieActuels($delta);

        return redirect()->route('personnages.show', $personnage);
    }

    /**
     * Enregistre une photo de personnage envoyée par le joueur, en
     * remplacement du logo par défaut affiché sur la fiche. L'ancien
     * fichier (s'il y en avait un) est supprimé du disque pour ne pas
     * accumuler des images orphelines.
     */
    public function modifierPhoto(Request $requete, Personnage $personnage): RedirectResponse
    {
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
}
