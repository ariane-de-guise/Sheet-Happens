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
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun'],
                'description' => 'Les humains sont les plus nombreux et les plus diversifiés des peuples de l\'Île des Héros.',
                'sous_races' => [],
                'bonus_aptitudes' => ['force' => 1, 'dexterite' => 1, 'constitution' => 1, 'intelligence' => 1, 'sagesse' => 1, 'charisme' => 1],
            ],
            [
                'nom' => 'Humain, Variant',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun'],
                'description' => 'Les humains sont les plus nombreux et les plus diversifiés des peuples de l\'Île des Héros.',
                'sous_races' => [],
                'bonus_aptitudes' => ['au_choix' => ['nombre' => 2, 'bonus' => 1]],
            ],
            [
                'nom' => 'Elfe',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Elfe'],
                'description' => 'Les elfes sont gracieux, immortels et profondément liés à la magie.',
                'sous_races' => [
                    ['nom' => 'Elfe Sombre', 'description' => 'Elfe fin à la peau obsidienne banni de la surface.', 'bonus_aptitudes' => ['charisme' => 1]],
                    ['nom' => 'Elfe des bois', 'description' => 'Elfe furtif et rapide de la forêt.', 'bonus_aptitudes' => ['sagesse' => 1]],
                    ['nom' => 'Haut Elfe', 'description' => 'Elfe à l\'esprit vif maitrisant une base de magie.', 'bonus_aptitudes' => ['intelligence' => 1]],
                ],
                'bonus_aptitudes' => ['dexterite' => 2],
            ],
            [
                'nom' => 'Nain',
                'taille' => 'Moyenne',
                'vitesse' => 25,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Nain'],
                'description' => 'Les nains sont robustes, têtus et excellents artisans.',
                'sous_races' => [
                    ['nom' => 'Nain de montagne', 'description' => 'Nain fort et robuste habitué à la vie difficile.', 'bonus_aptitudes' => ['force' => 2]],
                    ['nom' => 'Nain de la colline', 'description' => 'Nain résilient aux sens vif.', 'bonus_aptitudes' => ['sagesse' => 1]],
                ],
                'bonus_aptitudes' => ['constitution' => 2],
            ],
            [
                'nom' => 'Halfelin',
                'taille' => 'Petite',
                'vitesse' => 25,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Halfling'],
                'description' => 'Les halfelins sont petits, chanceux et aimables.',
                'sous_races' => [
                    ['nom' => 'Halfelin pied-roux', 'description' => 'Halfelin corpulent resistant dit descendant de Nain', 'bonus_aptitudes' => ['constitution' => 1]],
                    ['nom' => 'Halfelin pied-vif', 'description' => 'Halfelin sympathique qui a soif de voyage.', 'bonus_aptitudes' => ['charisme' => 1]],
                ],
                'bonus_aptitudes' => ['dexterite' => 2],
            ],
            [
                'nom' => 'Demi-elfe',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Elfe'],
                'description' => 'Mi-humains, mi-elfes, ils combinent le meilleur des deux mondes.',
                'sous_races' => [],
                'bonus_aptitudes' => ['charisme' => 2, 'au_choix' => ['nombre' => 2, 'bonus' => 1]],
            ],
            [
                'nom' => 'Demi-orc',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Orc'],
                'description' => 'Fort et résilient, le demi-orc est souvent méprisé mais loyal.',
                'sous_races' => [],
                'bonus_aptitudes' => ['force' => 2, 'constitution' => 1],
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
                'bonus_aptitudes' => ['force' => 2, 'charisme' => 1],
            ],
            [
                'nom' => 'Gnome',
                'taille' => 'Petite',
                'vitesse' => 25,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Gnôme'],
                'description' => 'Petits, inventifs et curieux, les gnômes adorent la magie et les machines.',
                'sous_races' => [
                    ['nom' => 'Gnome de la roche', 'description' => 'Magiciens rusés et excellents illusionnistes.', 'bonus_aptitudes' => ['constitution' => 1]],
                    ['nom' => 'Gnome des forêts', 'description' => 'Sages et mystiques, protecteurs de la nature.', 'bonus_aptitudes' => ['dexterite' => 1]],
                ],
                'bonus_aptitudes' => ['intelligence' => 2],
            ],
            [
                'nom' => 'Tieffelin',
                'taille' => 'Moyenne',
                'vitesse' => 30,
                'type_creature' => 'Humanoïde',
                'langues' => ['Commun', 'Infernal'],
                'description' => 'Marqués par le sang infernal, les tieffelins sont souvent craints mais résilients.',
                'sous_races' => [
                    ['nom' => 'Tieffelin de la Lignée d\'Asmodée', 'description' => 'Tieffelin liées à Nessus qui commande le pouvoir du feu et des ténèbres', 'bonus_aptitudes' => ['intelligence' => 1]],
                ],
                'bonus_aptitudes' => ['charisme' => 2],
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
