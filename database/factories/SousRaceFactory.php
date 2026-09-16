<?php

namespace Database\Factories;

use App\Models\Race;
use App\Models\SousRace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SousRace>
 */
class SousRaceFactory extends Factory
{
    protected $model = SousRace::class;

    public function definition(): array
    {
        return [
            'race_id' => RaceFactory::new(),
            'nom' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'bonus_aptitudes' => ['dexterite' => 1],
        ];
    }

    public function pourRace(Race $race): static
    {
        return $this->state(fn () => ['race_id' => $race->id]);
    }
}
