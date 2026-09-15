<?php

namespace Database\Factories;

use App\Models\Classe;
use App\Models\SousClasse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SousClasse>
 */
class SousClasseFactory extends Factory
{
    protected $model = SousClasse::class;

    public function definition(): array
    {
        return [
            'classe_id' => ClasseFactory::new(),
            'nom' => fake()->unique()->word(),
            'description' => fake()->sentence(),
        ];
    }

    public function pourClasse(Classe $classe): static
    {
        return $this->state(fn () => ['classe_id' => $classe->id]);
    }
}
