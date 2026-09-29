<?php

namespace App\Services;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\ListeCompetence;
use App\Models\Objet;
use App\Models\Personnage;
use App\Models\PersonnageObjet;
use App\Models\Race;
use App\Models\SousClasse;
use App\Models\SousRace;
use App\Models\Utilisateur;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

readonly class PersonnageCreationService
{
    public function __construct(
        private DatabaseManager $baseDeDonnees,
        private CapaciteService $capacites,
    ) {}

    public function create(array $donnees): Personnage
    {
        return $this->baseDeDonnees->transaction(function () use ($donnees): Personnage {
            $this->validerHierarchie($donnees);
            $this->validerLimiteNonAtteinte($donnees);

            $abilites = Abilite::create($donnees['abilites']);
            $listeCompetence = ListeCompetence::create([
                'competences' => $donnees['competences'] ?? [],
            ]);

            $personnage = Personnage::create([
                'utilisateur_id' => $donnees['utilisateur_id'],
                'race_id' => $donnees['race_id'],
                'sous_race_id' => $donnees['sous_race_id'] ?? null,
                'classe_id' => $donnees['classe_id'],
                'sous_classe_id' => $donnees['sous_classe_id'] ?? null,
                'historique_id' => $donnees['historique_id'] ?? null,
                'ideal_id' => $donnees['ideal_id'] ?? null,
                'defaut_id' => $donnees['defaut_id'] ?? null,
                'attachement_id' => $donnees['attachement_id'] ?? null,
                'personnalite_id' => $donnees['personnalite_id'] ?? null,
                'abilites_id' => $abilites->id,
                'liste_competence_id' => $listeCompetence->id,
                'nom' => $donnees['nom'],
                'niveau' => $donnees['niveau'] ?? 1,
                'alignement' => $donnees['alignement'] ?? null,
                'description' => $donnees['description'] ?? null,
                'point_de_vie' => $donnees['point_de_vie'],
                'langues' => $donnees['langues'] ?? [],
            ]);
            // 'langues' n'est pas une relation Eloquent mais une simple
            // colonne du modèle Personnage : elle est déjà présente sur
            // l'instance retournée par create().

            // Capacités de niveau 1 de la classe (et de la sous-classe si
            // elle se choisit dès le niveau 1, ex. domaine du clerc).
            $this->capacites->synchroniser($personnage);

            $this->ajouterEquipementDeDepart($personnage, $donnees['equipement'] ?? []);

            // L'outil choisi (ou imposé sans choix par l'historique) n'est
            // plus stocké comme un simple texte affiché sous "Compétences" :
            // il rejoint l'inventaire réel comme les autres objets de
            // départ, type "outil" par défaut s'il ne correspond à aucune
            // entrée existante du catalogue.
            if (! empty($donnees['outil'])) {
                $this->ajouterEquipementDeDepart($personnage, [$donnees['outil']], 'outil');
            }

            return $personnage->load(['race', 'sousRace', 'classe', 'sousClasse', 'historique', 'ideal', 'defaut', 'attachement', 'personnalite', 'abilites', 'listeCompetence']);
        });
    }

    /**
     * L'équipement de départ accordé par l'historique choisi (voir
     * `historiques.equipement_depart`/`outils_fixes`) est une simple liste
     * de noms : on les rattache à l'inventaire réel du personnage en les
     * faisant correspondre au catalogue `objets` (non équipés par défaut —
     * le joueur les équipe ensuite depuis sa fiche). Un nom sans
     * correspondance dans le catalogue (équipement narratif propre à un
     * historique, ex. "Symbole sacré", ou un outil choisi sans entrée
     * existante) devient un objet créé à la volée avec le type par défaut
     * fourni, plutôt que d'être perdu : tout ce que l'historique promet au
     * joueur doit se retrouver dans son inventaire.
     */
    private function ajouterEquipementDeDepart(Personnage $personnage, array $noms, string $typeParDefaut = 'autre'): void
    {
        foreach (array_count_values($noms) as $nom => $quantite) {
            $objet = Objet::firstOrCreate(
                ['nom' => $nom, 'utilisateur_id' => null],
                ['type' => $typeParDefaut],
            );

            // Cette méthode peut être appelée plusieurs fois pour un même
            // personnage (équipement de l'historique, puis outil choisi) :
            // si le même objet en résulte (ex. l'outil choisi fait déjà
            // partie de l'équipement fixe de l'historique), on incrémente
            // la ligne existante plutôt que de violer la contrainte
            // d'unicité (personnage_id, objet_id).
            $ligne = PersonnageObjet::where('personnage_id', $personnage->id)->where('objet_id', $objet->id)->first();

            if ($ligne) {
                $ligne->increment('quantite', $quantite);

                continue;
            }

            PersonnageObjet::create([
                'personnage_id' => $personnage->id,
                'objet_id' => $objet->id,
                'quantite' => $quantite,
                'quantite_equipee' => 0,
            ]);
        }
    }

    private function validerHierarchie(array $donnees): void
    {
        if (! empty($donnees['sous_race_id']) && SousRace::whereKey($donnees['sous_race_id'])->where('race_id', $donnees['race_id'])->doesntExist()) {
            throw ValidationException::withMessages([
                'sous_race_id' => 'La sous-race ne correspond pas à la race sélectionnée.',
            ]);
        }

        $classe = Classe::find($donnees['classe_id']);
        if (Race::whereKey($donnees['race_id'])->doesntExist() || ! $classe) {
            throw ValidationException::withMessages([
                'race_id' => 'La race ou la classe sélectionnée est invalide.',
            ]);
        }

        if (! empty($donnees['sous_classe_id'])) {
            $sousClasse = SousClasse::whereKey($donnees['sous_classe_id'])
                ->where('classe_id', $donnees['classe_id'])
                ->first();

            if (! $sousClasse || $classe->niveau_sous_classe > ($donnees['niveau'] ?? 1)) {
                throw ValidationException::withMessages([
                    'sous_classe_id' => 'La sous-classe ne correspond pas à la classe ou au niveau sélectionné.',
                ]);
            }
        }
    }

    /**
     * Empêche un utilisateur de dépasser son quota de personnages, quel
     * que soit le point d'entrée utilisé pour créer un personnage
     * (assistant de création).
     *
     * La ligne utilisateur est verrouillée (SELECT ... FOR UPDATE) pour
     * la durée de la transaction : si deux créations concurrentes du même
     * utilisateur (ex. deux onglets) arrivent en même temps alors qu'il
     * ne lui reste qu'une place, la seconde attend que la première soit
     * validée avant de recompter, au lieu de lire un compte périmé et de
     * laisser passer les deux.
     */
    private function validerLimiteNonAtteinte(array $donnees): void
    {
        Utilisateur::whereKey($donnees['utilisateur_id'])->lockForUpdate()->first();

        $nombrePersonnages = Personnage::where('utilisateur_id', $donnees['utilisateur_id'])->count();

        if ($nombrePersonnages >= Personnage::MAX_PAR_UTILISATEUR) {
            throw ValidationException::withMessages([
                'utilisateur_id' => 'Vous avez atteint la limite de '.Personnage::MAX_PAR_UTILISATEUR.' personnages. Supprimez-en un pour pouvoir en créer un nouveau.',
            ]);
        }
    }
}
