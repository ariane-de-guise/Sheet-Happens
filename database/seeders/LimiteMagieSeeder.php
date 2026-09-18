<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LimiteMagie;
use App\Models\LimiteMagieClasse;
use App\Models\LimiteMagieSousClasse;

class LimiteMagieSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | CLERC
        |--------------------------------------------------------------------------
        |
        | Niveau de classe 1 à 5
        |
        | TourMagie = nombre de tours de magie
        | SortConnue = niveau de classe utilisé comme base pour les
        | sorts préparés.
        |
        | Pour un Clerc :
        | Sorts préparés = niveau de Clerc + modificateur de Sagesse
        |
        */

        $this->createClasseMagicLimits(
            idClasse: 2,
            progression: [
                1 => [
                    'TourMagie' => 3,
                    'SortConnue' => 1,
                    'Niveau1' => 2,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
                2 => [
                    'TourMagie' => 3,
                    'SortConnue' => 2,
                    'Niveau1' => 3,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
                3 => [
                    'TourMagie' => 3,
                    'SortConnue' => 3,
                    'Niveau1' => 4,
                    'Niveau2' => 2,
                    'Niveau3' => 0,
                ],
                4 => [
                    'TourMagie' => 4,
                    'SortConnue' => 4,
                    'Niveau1' => 4,
                    'Niveau2' => 3,
                    'Niveau3' => 0,
                ],
                5 => [
                    'TourMagie' => 4,
                    'SortConnue' => 5,
                    'Niveau1' => 4,
                    'Niveau2' => 3,
                    'Niveau3' => 2,
                ],
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | MAGICIEN
        |--------------------------------------------------------------------------
        |
        | Niveau de classe 1 à 5
        |
        | Sorts préparés = niveau de Magicien + modificateur d'Intelligence
        |
        | Le nombre de sorts dans le grimoire est différent :
        | niveau 1 = 6
        | puis +2 par niveau.
        |
        | Ici SortConnue représente donc la base du nombre de sorts
        | préparables : niveau de Magicien.
        |
        */

        $this->createClasseMagicLimits(
            idClasse: 4,
            progression: [
                1 => [
                    'TourMagie' => 3,
                    'SortConnue' => 1,
                    'Niveau1' => 2,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
                2 => [
                    'TourMagie' => 3,
                    'SortConnue' => 2,
                    'Niveau1' => 3,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
                3 => [
                    'TourMagie' => 3,
                    'SortConnue' => 3,
                    'Niveau1' => 4,
                    'Niveau2' => 2,
                    'Niveau3' => 0,
                ],
                4 => [
                    'TourMagie' => 4,
                    'SortConnue' => 4,
                    'Niveau1' => 4,
                    'Niveau2' => 3,
                    'Niveau3' => 0,
                ],
                5 => [
                    'TourMagie' => 4,
                    'SortConnue' => 5,
                    'Niveau1' => 4,
                    'Niveau2' => 3,
                    'Niveau3' => 2,
                ],
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | FILOU ARCANİQUE
        |--------------------------------------------------------------------------
        |
        | ID sous-classe : 12
        |
        | C'est un tiers de lanceur de sorts.
        | Aux niveaux 1 et 2 : aucune magie.
        | Niveau 3 : 2 tours de magie et 3 sorts connus.
        | Niveau 4 : 2 tours de magie et 4 sorts connus.
        | Niveau 5 : 2 tours de magie et 4 sorts connus.
        |
        */

        $this->createSousClasseMagicLimits(
            idSousClasse: 12,
            progression: [
                3 => [
                    'TourMagie' => 2,
                    'SortConnue' => 3,
                    'Niveau1' => 2,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
                4 => [
                    'TourMagie' => 2,
                    'SortConnue' => 4,
                    'Niveau1' => 3,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
                5 => [
                    'TourMagie' => 2,
                    'SortConnue' => 4,
                    'Niveau1' => 3,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | CHEVALIER ELDRITCH
        |--------------------------------------------------------------------------
        |
        | ID sous-classe : 23
        |
        | C'est un tiers de lanceur de sorts.
        | Il ne lance pas de sorts avant le niveau 3.
        |
        */

        $this->createSousClasseMagicLimits(
            idSousClasse: 23,
            progression: [
                3 => [
                    'TourMagie' => 2,
                    'SortConnue' => 3,
                    'Niveau1' => 2,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
                4 => [
                    'TourMagie' => 2,
                    'SortConnue' => 4,
                    'Niveau1' => 3,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
                5 => [
                    'TourMagie' => 2,
                    'SortConnue' => 4,
                    'Niveau1' => 3,
                    'Niveau2' => 0,
                    'Niveau3' => 0,
                ],
            ]
        );
    }


    /**
     * Crée les limites de magie d'une classe.
     */
    private function createClasseMagicLimits(
        int $idClasse,
        array $progression
    ): void {
        foreach ($progression as $niveau => $valeurs) {

            $limiteMagie = LimiteMagie::create([
                'TourMagie' => $valeurs['TourMagie'],
                'SortConnue' => $valeurs['SortConnue'],
                'Niveau1' => $valeurs['Niveau1'],
                'Niveau2' => $valeurs['Niveau2'],
                'Niveau3' => $valeurs['Niveau3'],
            ]);

            LimiteMagieClasse::create([
                'id_Classe' => $idClasse,
                'id_LimiteMagie' => $limiteMagie->id,
                'Niveau' => $niveau,
            ]);
        }
    }


    /**
     * Crée les limites de magie d'une sous-classe.
     */
    private function createSousClasseMagicLimits(
        int $idSousClasse,
        array $progression
    ): void {
        foreach ($progression as $niveau => $valeurs) {

            $limiteMagie = LimiteMagie::create([
                'TourMagie' => $valeurs['TourMagie'],
                'SortConnue' => $valeurs['SortConnue'],
                'Niveau1' => $valeurs['Niveau1'],
                'Niveau2' => $valeurs['Niveau2'],
                'Niveau3' => $valeurs['Niveau3'],
            ]);

            LimiteMagieSousClasse::create([
                'id_SousClasse' => $idSousClasse,
                'id_LimiteMagie' => $limiteMagie->id,
                'Niveau' => $niveau,
            ]);
        }
    }
}
