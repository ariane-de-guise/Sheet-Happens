<?php

namespace Database\Factories;

use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Utilisateur>
 */
class UtilisateurFactory extends Factory
{
    /**
     * Le modèle associé à la factory.
     */
    protected $model = Utilisateur::class;

    /**
     * Mot de passe mis en cache pour accélérer la génération en masse.
     */
    protected static ?string $motDePasse;

    /**
     * Définition de l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'courriel' => fake()->unique()->safeEmail(),
            'mot_de_passe' => static::$motDePasse ??= Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
