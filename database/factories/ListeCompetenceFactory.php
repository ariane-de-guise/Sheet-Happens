<?php

namespace Database\Factories;

use App\Models\ListeCompetence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListeCompetence>
 */
class ListeCompetenceFactory extends Factory
{
    protected $model = ListeCompetence::class;

    public function definition(): array
    {
        return [
            'competences' => ['Athlétisme', 'Perception'],
        ];
    }
}
