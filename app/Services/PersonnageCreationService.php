<?php

namespace App\Services;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\ListeCompetence;
use App\Models\Personnage;
use App\Models\Race;
use App\Models\SousClasse;
use App\Models\SousRace;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class PersonnageCreationService
{
    public function __construct(private readonly DatabaseManager $database) {}

    public function create(array $data): Personnage
    {
        return $this->database->transaction(function () use ($data): Personnage {
            $this->validateHierarchy($data);

            $abilites = Abilite::create($data['abilites']);
            $listeCompetence = ListeCompetence::create([
                'competences' => $data['competences'] ?? [],
            ]);

            return Personnage::create([
                'utilisateur_id' => $data['utilisateur_id'],
                'race_id' => $data['race_id'],
                'sous_race_id' => $data['sous_race_id'] ?? null,
                'classe_id' => $data['classe_id'],
                'sous_classe_id' => $data['sous_classe_id'] ?? null,
                'abilites_id' => $abilites->id,
                'liste_competence_id' => $listeCompetence->id,
                'nom' => $data['nom'],
                'niveau' => $data['niveau'] ?? 1,
                'alignement' => $data['alignement'] ?? null,
                'description' => $data['description'] ?? null,
                'point_de_vie' => $data['point_de_vie'],
            ])->load(['race', 'sousRace', 'classe', 'sousClasse', 'abilites', 'listeCompetence']);
        });
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
}
