<?php

namespace Database\Factories;

use App\Models\Race;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Race>
 */
class RaceFactory extends Factory
{
    protected $model = Race::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->word(),
            'taille' => 'Moyenne',
            'vitesse' => 30,
            'type_creature' => 'Humanoide',
            'description' => fake()->sentence(),
            'bonus_abilite1' => 'force',
            'bonus_abilite2' => null,
            'bonus_abilite3' => null,
        ];
    }
}
