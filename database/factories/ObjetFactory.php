<?php

namespace Database\Factories;

use App\Models\Objet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Objet>
 */
class ObjetFactory extends Factory
{
    protected $modele = Objet::class;

    public function definition(): array
    {
        return [
            'utilisateur_id' => null,
            'nom' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'type' => 'autre',
            'magique' => false,
            'rarete' => null,
            'categorie' => null,
            'cout_mains' => 0,
            'ca_base' => null,
            'bonus' => null,
            'bonus_toujours_actif' => false,
            'est_consommable' => false,
            'effet_utilisation' => null,
            'degats' => null,
            'caracteristique_degats' => null,
            'type_degats' => null,
        ];
    }

    public function arme(string $categorie = 'une_main', int $coutMains = 1): self
    {
        return $this->state(fn () => [
            'type' => 'arme',
            'categorie' => $categorie,
            'cout_mains' => $coutMains,
            'degats' => '1d8',
            'caracteristique_degats' => 'force',
            'type_degats' => 'tranchant',
        ]);
    }

    public function armure(string $categorie = 'legere', int $caBase = 11): self
    {
        return $this->state(fn () => [
            'type' => 'armure',
            'categorie' => $categorie,
            'cout_mains' => 0,
            'ca_base' => $caBase,
        ]);
    }

    public function bouclier(): self
    {
        return $this->state(fn () => [
            'type' => 'bouclier',
            'categorie' => null,
            'cout_mains' => 1,
            'bonus' => ['ca' => 2],
        ]);
    }

    public function potion(array $effet = ['pv' => 7]): self
    {
        return $this->state(fn () => [
            'type' => 'potion',
            'cout_mains' => 0,
            'est_consommable' => true,
            'effet_utilisation' => $effet,
        ]);
    }

    public function magique(string $rarete = 'rare'): self
    {
        return $this->state(fn () => [
            'magique' => true,
            'rarete' => $rarete,
        ]);
    }
}
