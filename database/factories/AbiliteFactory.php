<?php

namespace Database\Factories;

use App\Models\Abilite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Abilite>
 */
class AbiliteFactory extends Factory
{
    protected $model = Abilite::class;

    public function definition(): array
    {
        return [
            'force' => 10,
            'dexterite' => 10,
            'constitution' => 10,
            'intelligence' => 10,
            'sagesse' => 10,
            'charisme' => 10,
        ];
    }
}
