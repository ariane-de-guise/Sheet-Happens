<?php

namespace Database\Seeders;

use App\Models\Degat;
use Illuminate\Database\Seeder;

class DegatSeeder extends Seeder
{
    public function run(): void
    {
        $degats = [
            'Acide',
            'Contondant',
            'Froid',
            'Feu',
            'Force',
            'Foudre',
            'Nécrotique',
            'Perforant',
            'Poison',
            'Psychique',
            'Radiant',
            'Tranchant',
            'Tonnerre',
        ];

        foreach ($degats as $degat) {
            Degat::create([
                'Nom' => $degat,
            ]);
        }
    }
}
