<?php

namespace Database\Factories;

use App\Models\Classe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classe>
 */
class ClasseFactory extends Factory
{
    protected $model = Classe::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->word(),
            'de_de_vie' => 'd8',
            'description' => fake()->sentence(),
            'niveau_sous_classe' => 1,
        ];
    }
}
