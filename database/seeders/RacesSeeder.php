<?php

namespace Database\Seeders;

use App\Models\Race;
use App\Models\SousRace;
use Illuminate\Database\Seeder;

class RacesSeeder extends Seeder
{
    public function run(): void
    {
        $bonusToAptitudes = static function (array $bonusFields): array {
            $keys = [
                'force' => 'force',
                'dextérité' => 'dexterite',
                'dexterite' => 'dexterite',
                'constitution' => 'constitution',
                'intelligence' => 'intelligence',
                'sagesse' => 'sagesse',
                'charisme' => 'charisme',
            ];
            $bonuses = [];

            foreach ($bonusFields as $bonus) {
                $bonus = mb_strtolower(trim((string) $bonus));
                if ($bonus === 'tous') {
                    foreach ($keys as $key) {
                        $bonuses[$key] = ($bonuses[$key] ?? 0) + 1;
                    }
                    continue;
                }

                if (isset($keys[$bonus])) {
                    $key = $keys[$bonus];
                    $bonuses[$key] = ($bonuses[$key] ?? 0) + 1;
                }
            }

            return $bonuses;
        };

        $races = [
            [
                'nom' => 'Humain',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun'],
                'description' => 'Les humains sont les plus nombreux et les plus diversifiés des peuples de l\'Île des Héros.',
                'sous_races' => [],
                'bonus_abilite1' => 'tous',
            ],
            [
                'nom' => 'Humain, Variant',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun'],
                'description' => 'Les humains sont les plus nombreux et les plus diversifiés des peuples de l\'Île des Héros.',
                'sous_races' => [],
                'bonus_abilite1' => 'au choix',
            ],
            [
                'nom' => 'Elfe',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Elfe'],
                'description' => 'Les elfes sont gracieux, immortels et profondément liés à la magie.',
                'sous_races' => [
                    ['nom' => 'Elfe Sombre', 'description' => 'Elfe fin à la peau obsidienne banni de la surface.', 'bonus_abilite1' => 'Charisme'],
                    ['nom' => 'Elfe des bois', 'description' => 'Elfe furtif et rapide de la forêt.', 'bonus_abilite1' => 'Sagesse'],
                    ['nom' => 'Haut Elfe', 'description' => 'Elfe à l\'esprit vif maitrisant une base de magie.', 'bonus_abilite1' => 'Intelligence'],
                ],
                'bonus_abilite1' => 'Dextérité',
            ],
            [
                'nom' => 'Nain',
                'taille' => 'Moyenne',
                'vitesse' => 25,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Nain'],
                'description' => 'Les nains sont robustes, têtus et excellents artisans.',
                'sous_races' => [
                    ['nom' => 'Nain de montagne', 'description' => 'Nain fort et robuste habitué à la vie difficile.', 'bonus_abilite1' => 'Force', 'bonus_abilite2' => 'Force'],
                    ['nom' => 'Nain de la colline', 'description' => 'Nain résilient aux sens vif.', 'bonus_abilite1' => 'Intelligence'],
                ],
                'bonus_abilite1' => 'Constitution',
            ],
            [
                'nom' => 'Halfelin',
                'taille' => 'Petite',
                'vitesse' => 25,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Halfling'],
                'description' => 'Les halfelins sont petits, chanceux et aimables.',
                'sous_races' => [
                    ['nom' => 'Halfelin pied-roux', 'description' => 'Halfelin corpulent resistant dit descendant de Nain', 'bonus_abilite1' => 'Constitution'],
                    ['nom' => 'Halfelin pied-vif', 'description' => 'Halfelin sympathique qui a soif de voyage.', 'bonus_abilite1' => 'Charisme'],
                ],
                'bonus_abilite1' => 'Dextérité',
            ],
            [
                'nom' => 'Demi-elfe',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Elfe'],
                'description' => 'Mi-humains, mi-elfes, ils combinent le meilleur des deux mondes.',
                'sous_races' => [],
                'bonus_abilite1' => 'Charisme',
                'bonus_abilite2' => 'au choix',
                'bonus_abilite3' => 'au choix',
            ],
            [
                'nom' => 'Demi-orc',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Orc'],
                'description' => 'Fort et résilient, le demi-orc est souvent méprisé mais loyal.',
                'sous_races' => [],
                'bonus_abilite1' => 'Force',
                'bonus_abilite2' => 'Constitution',
            ],
            [
                'nom' => 'Drakéide',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Draconique'],
                'description' => 'Des humanoïdes à l\'apparence reptilienne, descendants de dragons.',
                'sous_races' => [
                    ['nom' => 'Drakéide Noir', 'description' => 'Drakéide, respirant de l\'acide.'],
                    ['nom' => 'Drakéide Bleu', 'description' => 'Drakéide, respirant de l\'électricité.'],
                    ['nom' => 'Drakéide Laitons', 'description' => 'Drakéide, respirant du feu.'],
                    ['nom' => 'Drakéide Bronze', 'description' => 'Drakéide, respirant de l\'électricité.'],
                    ['nom' => 'Drakéide Cuivre', 'description' => 'Drakéide, respirant de l\'acide.'],
                    ['nom' => 'Drakéide Or', 'description' => 'Drakéide, respirant du feu.'],
                    ['nom' => 'Drakéide Vert', 'description' => 'Drakéide, respirant du poison'],
                    ['nom' => 'Drakéide Rouge', 'description' => 'Drakéide, respirant du feu.'],
                    ['nom' => 'Drakéide Argent', 'description' => 'Drakéide, respirant de la glace'],
                    ['nom' => 'Drakéide Blanc', 'description' => 'Drakéide, respirant de la glace'],
                ],
                'bonus_abilite1' => 'Force',
                'bonus_abilite2' => 'Charisme',
            ],
            [
                'nom' => 'Gnome',
                'taille' => 'Petite',
                'vitesse' => 25,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Gnôme'],
                'description' => 'Petits, inventifs et curieux, les gnômes adorent la magie et les machines.',
                'sous_races' => [
                    ['nom' => 'Gnome de la roche', 'description' => 'Magiciens rusés et excellents illusionnistes.', 'bonus_abilite1' => 'Constitution'],
                    ['nom' => 'Gnome des forêts', 'description' => 'Sages et mystiques, protecteurs de la nature.', 'bonus_abilite1' => 'Dextérité'],
                ],
                'bonus_abilite1' => 'Intelligence',
            ],
            [
                'nom' => 'Tieffelin',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Abyssal'],
                'description' => 'Marqués par le sang infernal, les tieffelins sont souvent craints mais résilients.',
                'sous_races' => [
                    ['nom' => 'Tieffelin de la Lignée d\'Asmodée', 'description' => 'Tieffelin liées à Nessus qui commande le pouvoir du feu et des ténèbres', 'bonus_abilite1' => 'Intelligence'],
                ],
                'bonus_abilite1' => 'Charisme',
            ],
        ];

        foreach ($races as $raceData) {
            $sousRaces = $raceData['sous_races'];
            unset($raceData['sous_races']);
            $raceData['bonus_aptitudes'] = $bonusToAptitudes([
                $raceData['bonus_abilite1'] ?? null,
                $raceData['bonus_abilite2'] ?? null,
                $raceData['bonus_abilite3'] ?? null,
            ]);
            unset($raceData['bonus_abilite1'], $raceData['bonus_abilite2'], $raceData['bonus_abilite3']);

            $race = Race::create($raceData);

            foreach ($sousRaces as $sr) {
                SousRace::create([
                    'race_id' => $race->id,
                    'nom' => $sr['nom'],
                    'description' => $sr['description'] ?? null,
                    'bonus_aptitudes' => $bonusToAptitudes([
                        $sr['bonus_abilite1'] ?? null,
                        $sr['bonus_abilite2'] ?? null,
                    ]),
                ]);
            }
        }
    }
}
