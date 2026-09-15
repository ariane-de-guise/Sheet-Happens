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
            // Liste par défaut volontairement large (les 18 compétences de
            // la 5e) pour ne pas contraindre les tests qui n'ont pas besoin
            // de tester la restriction par classe ; les tests qui en ont
            // besoin surchargent ces deux champs explicitement.
            'competences_disponibles' => [
                'Acrobaties', 'Arcanes', 'Athlétisme', 'Art de la duplicité',
                'Discrétion', 'Dressage', 'Escamotage', 'Histoire',
                'Intimidation', 'Intuition', 'Investigation', 'Médecine',
                'Nature', 'Perception', 'Performance', 'Persuasion',
                'Religion', 'Survie',
            ],
            'nombre_competences' => 2,
        ];
    }
}
