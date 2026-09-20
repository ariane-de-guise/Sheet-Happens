<?php

namespace Database\Factories;

use App\Models\PersonnageObjet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonnageObjet>
 */
class PersonnageObjetFactory extends Factory
{
    protected $model = PersonnageObjet::class;

    public function definition(): array
    {
        return [
            'personnage_id' => PersonnageFactory::new(),
            'objet_id' => ObjetFactory::new(),
            'quantite' => 1,
            'equipe' => false,
        ];
    }

    public function equipe(): self
    {
        return $this->state(fn () => ['equipe' => true]);
    }
}
