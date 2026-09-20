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
    public function __construct(private DatabaseManager $database) {}

    public function create(array $data): Personnage
    {
        return $this->database->transaction(function () use ($data): Personnage {
            $this->validateHierarchy($data);
            $this->validateLimiteNonAtteinte($data);

            $abilites = Abilite::create($data['abilites']);
            $listeCompetence = ListeCompetence::create([
                'competences' => $data['competences'] ?? [],
            ]);

            $personnage = Personnage::create([
                'utilisateur_id' => $data['utilisateur_id'],
                'race_id' => $data['race_id'],
                'sous_race_id' => $data['sous_race_id'] ?? null,
                'classe_id' => $data['classe_id'],
                'sous_classe_id' => $data['sous_classe_id'] ?? null,
                'historique_id' => $data['historique_id'] ?? null,
                'ideal_id' => $data['ideal_id'] ?? null,
                'defaut_id' => $data['defaut_id'] ?? null,
                'attachement_id' => $data['attachement_id'] ?? null,
                'personnalite_id' => $data['personnalite_id'] ?? null,
                'abilites_id' => $abilites->id,
                'liste_competence_id' => $listeCompetence->id,
                'nom' => $data['nom'],
                'niveau' => $data['niveau'] ?? 1,
                'alignement' => $data['alignement'] ?? null,
                'description' => $data['description'] ?? null,
                'point_de_vie' => $data['point_de_vie'],
                'langues' => $data['langues'] ?? [],
            ]);
            // 'langues' n'est pas une relation Eloquent mais une simple
            // colonne du modèle Personnage : elle est déjà présente sur
            // l'instance retournée par create().

            $this->ajouterEquipementDeDepart($personnage, $data['equipement'] ?? []);

            // L'outil choisi (ou imposé sans choix par l'historique) n'est
            // plus stocké comme un simple texte affiché sous "Compétences" :
            // il rejoint l'inventaire réel comme les autres objets de
            // départ, type "outil" par défaut s'il ne correspond à aucune
            // entrée existante du catalogue.
            if (! empty($data['outil'])) {
                $this->ajouterEquipementDeDepart($personnage, [$data['outil']], 'outil');
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
                'equipe' => false,
            ]);
        }
    }

    private function validateHierarchy(array $data): void
    {
        if (! empty($data['sous_race_id']) && SousRace::whereKey($data['sous_race_id'])->where('race_id', $data['race_id'])->doesntExist()) {
            throw ValidationException::withMessages([
                'sous_race_id' => 'La sous-race ne correspond pas à la race sélectionnée.',
            ]);
        }

        $classe = Classe::find($data['classe_id']);
        if (Race::whereKey($data['race_id'])->doesntExist() || ! $classe) {
            throw ValidationException::withMessages([
                'race_id' => 'La race ou la classe sélectionnée est invalide.',
            ]);
        }

        if (! empty($data['sous_classe_id'])) {
            $sousClasse = SousClasse::whereKey($data['sous_classe_id'])
                ->where('classe_id', $data['classe_id'])
                ->first();

            if (! $sousClasse || $classe->niveau_sous_classe > ($data['niveau'] ?? 1)) {
                throw ValidationException::withMessages([
                    'sous_classe_id' => 'La sous-classe ne correspond pas à la classe ou au niveau sélectionné.',
                ]);
            }
        }
    }

    /**
     * Empêche un utilisateur de dépasser son quota de personnages, quel
     * que soit le point d'entrée utilisé pour créer un personnage
     * (assistant de création ou API).
     *
     * La ligne utilisateur est verrouillée (SELECT ... FOR UPDATE) pour
     * la durée de la transaction : si deux créations concurrentes du même
     * utilisateur (ex. deux onglets) arrivent en même temps alors qu'il
     * ne lui reste qu'une place, la seconde attend que la première soit
     * validée avant de recompter, au lieu de lire un compte périmé et de
     * laisser passer les deux.
     */
    private function validateLimiteNonAtteinte(array $data): void
    {
        Utilisateur::whereKey($data['utilisateur_id'])->lockForUpdate()->first();

        $nombrePersonnages = Personnage::where('utilisateur_id', $data['utilisateur_id'])->count();

        if ($nombrePersonnages >= Personnage::MAX_PAR_UTILISATEUR) {
            throw ValidationException::withMessages([
                'utilisateur_id' => 'Vous avez atteint la limite de '.Personnage::MAX_PAR_UTILISATEUR.' personnages. Supprimez-en un pour pouvoir en créer un nouveau.',
            ]);
        }
    }
}
