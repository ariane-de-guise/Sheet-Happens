<?php

namespace Database\Seeders;

use App\Models\Race;
use App\Models\TraitModel;
use Illuminate\Database\Seeder;

class RaceTraitSeeder extends Seeder
{
    public function run(): void
    {
        $mappings = [
            'Nain' => [
                'Vision dans le noir',
                'Résilience naine',
                'Entraînement aux armes naines',
                'Sens de la pierre',
            ],
            'Elfe' => [
                'Vision dans le noir',
                'Sens aiguisés',
                'Ascendance féérique',
                'Transe',
            ],
            'Halfelin' => [
                'Chanceux',
                'Brave',
                'Agilité halfeline',
            ],
            'Humain' => [
                'Polyvalence des compétences',
            ],
            'Humain, Variant' => [
                'Polyvalence des compétences',
            ],
            'Drakéide' => [
                'Souffle draconique',
                'Résistance draconique',
            ],
            'Gnome' => [
                'Vision dans le noir',
                'Ruse gnome',
            ],
            'Demi-elfe' => [
                'Vision dans le noir',
                'Ascendance féérique',
                'Polyvalence des compétences',
            ],
            'Demi-orc' => [
                'Vision dans le noir',
                'Mençant',
                'Acharnement',
                'Attaques sauvages',
            ],
            'Tieffelin' => [
                'Vision dans le noir',
                'Résistance infernale',
                'Magie innée : Thaumaturgie',
                'Magie infernale : Représailles infernales',
                'Magie infernale : Ténèbres',
            ],
            'Aasimar' => [
                'Vision dans le noir',
                'Résistance céleste',
                'Porteur de lumière',
                'Mains curatives',
                'Âme radieuse',
            ],
        ];

        foreach ($mappings as $raceName => $traitNames) {
            $race = Race::where('nom', $raceName)->first();

            if (! $race) {
                throw new \RuntimeException("La race « {$raceName} » est absente du seeder des races.");
            }

            $traits = TraitModel::whereIn('nom', $traitNames)->get(['id', 'nom']);
            $missingTraitNames = array_diff($traitNames, $traits->pluck('nom')->all());

            if ($missingTraitNames !== []) {
                throw new \RuntimeException(
                    'Les traits suivants sont absents du seeder des traits : '
                    . implode(', ', $missingTraitNames)
                );
            }

            $race->traits()->syncWithoutDetaching($traits->pluck('id'));
        }
    }
}
