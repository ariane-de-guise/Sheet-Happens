<?php

namespace Database\Seeders;

use App\Models\PersonnageSort;
use Illuminate\Database\Seeder;

class PersonnageSortSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PersonnageSort
        |--------------------------------------------------------------------------
        |
        | Prepare :
        |
        | 0 = non préparé
        | 1 = préparé
        | 2 = état spécial
        |
        |--------------------------------------------------------------------------
        */

        $sorts = [
            /*
            |--------------------------------------------------------------------------
            | Exemple
            |--------------------------------------------------------------------------
            |
            | [
            |     'personnage_id' => 1,
            |     'sort_id' => 1,
            |     'preparation' => 1,
            | ],
            |
            */
        ];

        foreach ($sorts as $sort) {
            PersonnageSort::updateOrCreate(
                [
                    'personnage_id' => $sort['personnage_id'],
                    'sort_id' => $sort['sort_id'],
                ],
                [
                    'preparation' => $sort['preparation'],
                ]
            );
        }
    }
}
