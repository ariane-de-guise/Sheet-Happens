<?php

namespace Database\Factories;

use App\Models\Personnage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Personnage>
 */
class PersonnageFactory extends Factory
{
    protected $model = Personnage::class;

    public function definition(): array
    {
        return [
            'utilisateur_id' => UtilisateurFactory::new(),
            'race_id' => RaceFactory::new(),
            'sous_race_id' => null,
            'classe_id' => ClasseFactory::new(),
            'sous_classe_id' => null,
            'abilites_id' => AbiliteFactory::new(),
            'liste_competence_id' => ListeCompetenceFactory::new(),
            'nom' => fake()->unique()->firstName(),
            'niveau' => 1,
            'alignement' => 'Neutre bon',
            'description' => fake()->sentence(),
            'point_de_vie' => 10,
        ];
    }
}
