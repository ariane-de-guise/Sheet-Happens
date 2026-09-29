<?php

namespace Database\Seeders;

use App\Models\LimiteMagie;
use App\Models\LimiteMagieClasse;
use App\Models\LimiteMagieSousClasse;
use Illuminate\Database\Seeder;

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
        | tours_de_magie = nombre de tours de magie
        | sorts_prepares = niveau de classe utilisé comme base pour les
        | sorts préparés.
        |
        | Pour un Clerc :
        | Sorts préparés = niveau de Clerc + modificateur de Sagesse
        |
        */

        $this->createClasseMagicLimits(
            2,
            [
                1 => [
                    'tours_de_magie' => 3,
                    'emplacements_niveau_1' => 2,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
                2 => [
                    'tours_de_magie' => 3,
                    'emplacements_niveau_1' => 3,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
                3 => [
                    'tours_de_magie' => 3,
                    'emplacements_niveau_1' => 4,
                    'emplacements_niveau_2' => 2,
                    'emplacements_niveau_3' => 0,
                ],
                4 => [
                    'tours_de_magie' => 4,
                    'emplacements_niveau_1' => 4,
                    'emplacements_niveau_2' => 3,
                    'emplacements_niveau_3' => 0,
                ],
                5 => [
                    'tours_de_magie' => 4,
                    'emplacements_niveau_1' => 4,
                    'emplacements_niveau_2' => 3,
                    'emplacements_niveau_3' => 2,
                ],
            ],
            'Sagesse'
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
        | Ici sorts_prepares représente donc la base du nombre de sorts
        | préparables : niveau de Magicien.
        |
        */

        $this->createClasseMagicLimits(
            4,
            [
                1 => [
                    'tours_de_magie' => 3,
                    'emplacements_niveau_1' => 2,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
                2 => [
                    'tours_de_magie' => 3,
                    'emplacements_niveau_1' => 3,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
                3 => [
                    'tours_de_magie' => 3,
                    'emplacements_niveau_1' => 4,
                    'emplacements_niveau_2' => 2,
                    'emplacements_niveau_3' => 0,
                ],
                4 => [
                    'tours_de_magie' => 4,
                    'emplacements_niveau_1' => 4,
                    'emplacements_niveau_2' => 3,
                    'emplacements_niveau_3' => 0,
                ],
                5 => [
                    'tours_de_magie' => 4,
                    'emplacements_niveau_1' => 4,
                    'emplacements_niveau_2' => 3,
                    'emplacements_niveau_3' => 2,
                ],
            ],
            'Intelligence'
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
            12,
            [
                3 => [
                    'tours_de_magie' => 2,
                    'sorts_prepares' => 3,
                    'emplacements_niveau_1' => 2,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
                4 => [
                    'tours_de_magie' => 2,
                    'sorts_prepares' => 4,
                    'emplacements_niveau_1' => 3,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
                5 => [
                    'tours_de_magie' => 2,
                    'sorts_prepares' => 4,
                    'emplacements_niveau_1' => 3,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
            ],
            'Intelligence'
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
            23,
            [
                3 => [
                    'tours_de_magie' => 2,
                    'sorts_prepares' => 3,
                    'emplacements_niveau_1' => 2,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
                4 => [
                    'tours_de_magie' => 2,
                    'sorts_prepares' => 4,
                    'emplacements_niveau_1' => 3,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
                5 => [
                    'tours_de_magie' => 2,
                    'sorts_prepares' => 4,
                    'emplacements_niveau_1' => 3,
                    'emplacements_niveau_2' => 0,
                    'emplacements_niveau_3' => 0,
                ],
            ],
            'Intelligence'
        );
    }

    /**
     * Crée les limites de magie d'une classe.
     */
    private function createClasseMagicLimits(
        int $idClasse,
        array $progression,
        ?string $abiliteMagie = null
    ): void {
        foreach ($progression as $niveau => $valeurs) {

            $limiteMagie = LimiteMagie::create([
                'tours_de_magie' => $valeurs['tours_de_magie'],
                'sorts_prepares' => $valeurs['sorts_prepares'] ?? null,
                'emplacements_niveau_1' => $valeurs['emplacements_niveau_1'],
                'emplacements_niveau_2' => $valeurs['emplacements_niveau_2'],
                'emplacements_niveau_3' => $valeurs['emplacements_niveau_3'],
            ]);

            $abilite = $abiliteMagie ?? ($valeurs['caracteristique_magie'] ?? 'Intelligence');

            LimiteMagieClasse::create([
                'classe_id' => $idClasse,
                'limite_magie_id' => $limiteMagie->id,
                'caracteristique_magie' => $abilite,
                'niveau' => $niveau,
            ]);
        }
    }

    /**
     * Crée les limites de magie d'une sous-classe.
     */
    private function createSousClasseMagicLimits(
        int $idSousClasse,
        array $progression,
        ?string $abiliteMagie = null
    ): void {
        foreach ($progression as $niveau => $valeurs) {

            $limiteMagie = LimiteMagie::create([
                'tours_de_magie' => $valeurs['tours_de_magie'],
                'sorts_prepares' => $valeurs['sorts_prepares'] ?? null,
                'emplacements_niveau_1' => $valeurs['emplacements_niveau_1'],
                'emplacements_niveau_2' => $valeurs['emplacements_niveau_2'],
                'emplacements_niveau_3' => $valeurs['emplacements_niveau_3'],
            ]);

            $abilite = $abiliteMagie ?? ($valeurs['caracteristique_magie'] ?? 'Intelligence');

            LimiteMagieSousClasse::create([
                'sous_classe_id' => $idSousClasse,
                'limite_magie_id' => $limiteMagie->id,
                'caracteristique_magie' => $abilite,
                'niveau' => $niveau,
            ]);
        }
    }
}
