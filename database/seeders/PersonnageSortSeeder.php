<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonnageSort;

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
            |     'id_Personnage' => 1,
            |     'id_Sort' => 1,
            |     'Prepare' => 1,
            | ],
            |
            */
        ];

        foreach ($sorts as $sort) {
            PersonnageSort::updateOrCreate(
                [
                    'id_Personnage' => $sort['id_Personnage'],
                    'id_Sort' => $sort['id_Sort'],
                ],
                [
                    'Prepare' => $sort['Prepare'],
                ]
            );
        }
    }
}
