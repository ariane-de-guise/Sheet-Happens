<?php

namespace Database\Seeders;

use App\Models\Sort;
use App\Models\SousClasseSort;
use Illuminate\Database\Seeder;

/**
 * SousClasseSortSeeder — D&D 5e 2014 / SRD 5.1
 *
 * Seuls les sorts réellement ajoutés par une sous-classe sont enregistrés.
 *
 * Sous-classes prises en compte :
 *  3  Domaine de la Vie
 *  4  Domaine de la Lumière
 *  5  Domaine de la Connaissance
 *  6  Domaine de la Nature
 *  7  Domaine de la Tempête
 *  8  Domaine de la Ruse
 *  9  Domaine de la Guerre
 *
 * Les autres sous-classes fournies n'ajoutent pas de liste fixe de sorts :
 * Barbare Berserk/Totem, Voleur, Assassin, Écoles de magie, Champion,
 * Maître de guerre.
 *
 * Filou Arcanique (12) et Chevalier Eldritch (23) ont bien du lancement de
 * sorts en 2014, mais ne donnent pas une liste fixe de sorts "ajoutés" :
 * leurs sorts sont choisis depuis la liste du Magicien selon leurs règles.
 * Ils sont donc volontairement exclus de SousClasseSort conformément à
 * « uniquement les sorts ajoutés par la sous-classe ».
 *
 * Le niveau indiqué dans cette structure est le niveau du sort (1 à 5),
 * pas le niveau du personnage.
 */
class SousClasseSortSeeder extends Seeder
{
    public function run(): void
    {
        $sousClasses = [
            3 => [ // Domaine de la Vie
                1 => ['Bless', 'Cure Wounds'],
                2 => ['Lesser Restoration', 'Spiritual Weapon'],
                3 => ['Beacon of Hope', 'Revivify'],
            ],

            4 => [ // Domaine de la Lumière
                1 => ['Burning Hands', 'Faerie Fire'],
                2 => ['Flaming Sphere', 'Scorching Ray'],
                3 => ['Daylight', 'Fireball'],
            ],

            5 => [ // Domaine de la Connaissance
                1 => ['Command', 'Identify'],
                2 => ['Augury', 'Suggestion'],
                3 => ['Nondetection', 'Speak with Dead'],
            ],

            6 => [ // Domaine de la Nature
                1 => ['Animal Friendship', 'Speak with Animals'],
                2 => ['Barkskin', 'Spike Growth'],
                3 => ['Plant Growth', 'Wind Wall'],
            ],

            7 => [ // Domaine de la Tempête
                1 => ['Fog Cloud', 'Thunderwave'],
                2 => ['Gust of Wind', 'Shatter'],
                3 => ['Call Lightning', 'Sleet Storm'],
            ],

            8 => [ // Domaine de la Ruse
                1 => ['Charm Person', 'Disguise Self'],
                2 => ['Mirror Image', 'Pass without Trace'],
                3 => ['Blink', 'Dispel Magic'],
            ],

            9 => [ // Domaine de la Guerre
                1 => ['Divine Favor', 'Shield of Faith'],
                2 => ['Magic Weapon', 'Spiritual Weapon'],
                3 => ["Crusader's Mantle", 'Spirit Guardians'],
            ],
            12 => [ // Filou Arcanique
                1 => ['Mage Hand'],
            ],

        ];

        foreach ($sousClasses as $idSousClasse => $niveaux) {
            foreach ($niveaux as $sorts) {
                foreach ($sorts as $nomAnglais) {
                    $sort = Sort::where('NomAnglais', $nomAnglais)->first();

                    if (! $sort) {
                        throw new \RuntimeException(
                            "Sort introuvable dans la table sorts : {$nomAnglais}. ".
                            "Ajoute d'abord ce sort dans SortSeeder."
                        );
                    }

                    SousClasseSort::updateOrCreate(
                        [
                            'id_SousClasse' => $idSousClasse,
                            'id_Sort' => $sort->id,
                        ],
                        []
                    );
                }
            }
        }
    }
}
