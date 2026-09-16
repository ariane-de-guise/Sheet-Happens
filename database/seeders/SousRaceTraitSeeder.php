<?php

namespace Database\Seeders;

use App\Models\SousRace;
use App\Models\TraitModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SousRaceTraitSeeder extends Seeder
{
    public function run(): void
    {
        $mappings = [
            // --- Drakéides (Ascendances) ---
            'Drakéide Argent' => [
                'Arme de souffle (Froid)',
                'Résistance aux dégâts (Froid)'
            ],
            'Drakéide Blanc' => [
                'Arme de souffle (Froid)',
                'Résistance aux dégâts (Froid)'
            ],
            'Drakéide Bleu' => [
                'Arme de souffle (Foudre)',
                'Résistance aux dégâts (Foudre)'
            ],
            'Drakéide Bronze' => [
                'Arme de souffle (Foudre)',
                'Résistance aux dégâts (Foudre)'
            ],
            'Drakéide Cuivre' => [
                'Arme de souffle (Acide)',
                'Résistance aux dégâts (Acide)'
            ],
            'Drakéide Laitons' => [
                'Arme de souffle (Feu)',
                'Résistance aux dégâts (Feu)'
            ],
            'Drakéide Noir' => [
                'Arme de souffle (Acide)',
                'Résistance aux dégâts (Acide)'
            ],
            'Drakéide Or' => [
                'Arme de souffle (Feu)',
                'Résistance aux dégâts (Feu)'
            ],
            'Drakéide Rouge' => [
                'Arme de souffle (Feu)',
                'Résistance aux dégâts (Feu)'
            ],
            'Drakéide Vert' => [
                'Arme de souffle (Poison)',
                'Résistance aux dégâts (Poison)'
            ],

            // --- Elfes ---
            'Elfe des bois' => [
                'Entraînement aux armes elfiques',
                'Pied léger',
                'Masque de la nature',
            ],
            'Elfe Sombre' => [
                'Vision dans le noir supérieure',
                'Sensibilité à la lumière du soleil',
                'Entraînement aux armes drows',
                'Magie drow : Lumières dansantes', // Niveau 1
                'Magie drow : Lueur féerique',     // Niveau 3
                'Magie drow : Ténèbres',           // Niveau 5
            ],
            'Haut Elfe' => [
                'Entraînement aux armes elfiques',
                'Sort mineur',
                'Langue supplémentaire',
            ],

            // --- Gnomes ---
            'Gnome de la roche' => [
                'Connaissances d’ingénieur',
                'Bricoleur',
            ],
            'Gnome des forêts' => [
                'Illusionniste né',
                'Parler aux petites bêtes',
            ],

            // --- Halfelins ---
            'Halfelin pied-roux' => [
                'Résilience des Cœurs-Vaillants',
            ],
            'Halfelin pied-vif' => [
                'Cachette naturelle',
            ],

            // --- Nains ---
            'Nain de la colline' => [
                'Ténacité naine',
            ],
            'Nain de montagne' => [
                'Entraînement au port des armures naines',
            ],

            // --- Tieffelins ---
            'Tieffelin de la Lignée d\'Asmodée' => [
                'Héritage infernal : Thaumaturgie',            // Niveau 1
                'Héritage infernal : Représailles infernales', // Niveau 3
                'Héritage infernal : Ténèbres',                // Niveau 5
            ],
        ];

        foreach ($mappings as $sousRaceName => $traitNames) {
            $sousRace = SousRace::where('nom', $sousRaceName)->first();

            if (! $sousRace) {
                throw new \RuntimeException("La sous-race « {$sousRaceName} » est absente de la base de données.");
            }

            $traits = TraitModel::whereIn('nom', $traitNames)->get(['id', 'nom']);
            $missingTraitNames = array_diff($traitNames, $traits->pluck('nom')->all());

            if ($missingTraitNames !== []) {
                throw new \RuntimeException(
                    "Les traits suivants sont absents du seeder des traits pour la sous-race {$sousRaceName} : "
                    . implode(', ', $missingTraitNames)
                );
            }

            $sousRace->traits()->syncWithoutDetaching($traits->pluck('id'));
        }
    }
}
