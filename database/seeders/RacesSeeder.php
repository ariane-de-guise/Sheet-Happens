<?php

namespace Database\Seeders;

use App\Models\Race;
use App\Models\SousRace;
use Illuminate\Database\Seeder;

class RacesSeeder extends Seeder
{
    public function run(): void
    {
        $races = [
            [
                'nom' => 'Humain',
                'taille' => 'De 1,50 m à plus de 2 m',
                'vitesse' => 90,
                'type_creature' => 'Humanoïde',
                'description' => 'Les humains sont les plus nombreux et les plus diversifiés des peuples de l\'Île des Héros.',
                'sous_races' => [],
                'bonus_aptitudes' => [
                    'force' => 1,
                    'dexterite' => 1,
                    'constitution' => 1,
                    'intelligence' => 1,
                    'sagesse' => 1,
                    'charisme' => 1,
                ],
            ],
            [
                'nom' => 'Elfe',
                'taille' => 'De 1,50 m à 1,80 m',
                'vitesse' => 90,
                'type_creature' => 'Humanoïde',
                'description' => 'Les elfes sont gracieux, immortels et profondément liés à la magie.',
                'sous_races' => [
                    [
                        'nom' => 'Elfe de la haute forêt',
                        'description' => 'Nobles et magiciens, maîtres des arts mystiques.',
                        'bonus_aptitudes' => ['intelligence' => 1],
                    ],
                    [
                        'nom' => 'Elfe des bois',
                        'description' => 'Guerriers agiles, protecteurs des forêts.',
                        'bonus_aptitudes' => ['sagesse' => 1],
                    ],
                    [
                        'nom' => 'Elfe de la lune',
                        'description' => 'Mystiques et insaisissables, liés aux cycles lunaires.',
                        'bonus_aptitudes' => ['intelligence' => 1],
                    ],
                ],
                'bonus_aptitudes' => [
                    'dexterite' => 2,
                ],
            ],
            [
                'nom' => 'Nain',
                'taille' => 'De 1,20 m à 1,50 m',
                'vitesse' => 60,
                'type_creature' => 'Humanoïde',
                'description' => 'Les nains sont robustes, têtus et excellents artisans.',
                'sous_races' => [
                    [
                        'nom' => 'Nain de montagne',
                        'description' => 'Guerriers puissants, forgerons légendaires.',
                        'bonus_aptitudes' => ['force' => 2],
                    ],
                    [
                        'nom' => 'Nain de la colline',
                        'description' => 'Explorateurs et marchands aventureux.',
                        'bonus_aptitudes' => ['sagesse' => 1],
                    ],
                ],
                'bonus_aptitudes' => [
                    'constitution' => 2,
                ],
            ],
            [
                'nom' => 'Halfelin',
                'taille' => 'De 0,90 m à 1,20 m',
                'vitesse' => 60,
                'type_creature' => 'Humanoïde',
                'description' => 'Les halfelins sont petits, chanceux et aimables.',
                'sous_races' => [
                    [
                        'nom' => 'Halfelin pied-roux',
                        'description' => 'Curieux et aventureux, ils aiment voyager.',
                        'bonus_aptitudes' => ['constitution' => 1],
                    ],
                    [
                        'nom' => 'Halfelin pied-vif',
                        'description' => 'Agiles et furtifs, excellents voleurs.',
                        'bonus_aptitudes' => ['charisme' => 1],
                    ],
                ],
                'bonus_aptitudes' => [
                    'dexterite' => 2,
                ],
            ],
            [
                'nom' => 'Demi-elfe',
                'taille' => 'De 1,50 m à 1,80 m',
                'vitesse' => 90,
                'type_creature' => 'Humanoïde',
                'description' => 'Mi-humains, mi-elfes, ils combinent le meilleur des deux mondes.',
                'sous_races' => [],
                'bonus_aptitudes' => [
                    'charisme' => 2,
                ],
            ],
            [
                'nom' => 'Demi-orc',
                'taille' => 'De 1,80 m à plus de 2,10 m',
                'vitesse' => 90,
                'type_creature' => 'Humanoïde',
                'description' => 'Fort et résilient, le demi-orc est souvent méprisé mais loyal.',
                'sous_races' => [],
                'bonus_aptitudes' => [
                    'force' => 2,
                    'constitution' => 1,
                ],
            ],
            [
                'nom' => 'Drakéide',
                'taille' => 'De 1,80 m à plus de 2,10 m',
                'vitesse' => 90,
                'type_creature' => 'Humanoïde',
                'description' => 'Des humanoïdes à l\'apparence reptilienne, descendants de dragons.',
                'sous_races' => [
                    [
                        'nom' => 'Drakéide de feu',
                        'description' => 'Passionnés et colériques, respirant le feu.',
                        'bonus_aptitudes' => [],
                    ],
                    [
                        'nom' => 'Drakéide de froid',
                        'description' => 'Calmes et calculateurs, maîtrisant le gel.',
                        'bonus_aptitudes' => [],
                    ],
                    [
                        'nom' => 'Drakéide de tempête',
                        'description' => 'Imprévisibles et électriques, liés aux orages.',
                        'bonus_aptitudes' => [],
                    ],
                ],
                'bonus_aptitudes' => [
                    'force' => 2,
                    'charisme' => 1,
                ],
            ],
            [
                'nom' => 'Gnome',
                'taille' => 'De 0,90 m à 1,20 m',
                'vitesse' => 60,
                'type_creature' => 'Humanoïde',
                'description' => 'Petits, inventifs et curieux, les gnômes adorent la magie et les machines.',
                'sous_races' => [
                    [
                        'nom' => 'Gnome de la roche',
                        'description' => 'Magiciens rusés et excellents illusionnistes.',
                        'bonus_aptitudes' => ['constitution' => 1],
                    ],
                    [
                        'nom' => 'Gnome des forêts',
                        'description' => 'Sages et mystiques, protecteurs de la nature.',
                        'bonus_aptitudes' => ['dexterite' => 1],
                    ],
                ],
                'bonus_aptitudes' => [
                    'intelligence' => 2,
                ],
            ],
            [
                'nom' => 'Tieffelin',
                'taille' => 'De 1,50 m à 1,80 m',
                'vitesse' => 90,
                'type_creature' => 'Humanoïde',
                'description' => 'Marqués par le sang infernal, les tieffelins sont souvent craints mais résilients.',
                'sous_races' => [],
                'bonus_aptitudes' => [
                    'charisme' => 2,
                    'intelligence' => 1,
                ],
            ],
        ];

        foreach ($races as $raceData) {
            $sousRaces = $raceData['sous_races'];
            unset($raceData['sous_races']);

            $race = Race::create($raceData);

            foreach ($sousRaces as $sr) {
                SousRace::create([
                    'race_id' => $race->id,
                    'nom' => $sr['nom'],
                    'description' => $sr['description'] ?? null,
                    'bonus_aptitudes' => $sr['bonus_aptitudes'] ?? [],
                ]);
            }
        }
    }
}
