<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\SousClasse;
use Illuminate\Database\Seeder;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            [
                'nom' => 'Barbare',
                'de_de_vie' => '12',
                'description' => 'Un guerrier sauvage qui entre dans une frénésie de combat.',
                'sous_classes' => [
                    ['nom' => 'Barbare du colosse', 'niveau_sous_classe' => 3],
                    ['nom' => 'Barbare du totém', 'niveau_sous_classe' => 3],
                ],
            ],
            [
                'nom' => 'Barde',
                'de_de_vie' => '8',
                'description' => 'Un magicien charismatique qui utilise la musique et les mots.',
                'sous_classes' => [
                    ['nom' => 'Collège de l\'éloquence', 'niveau_sous_classe' => 3],
                    ['nom' => 'Collège du mystère', 'niveau_sous_classe' => 3],
                ],
            ],
            [
                'nom' => 'Cléric',
                'de_de_vie' => '8',
                'description' => 'Un prêtre qui puise sa puissance dans sa divinité.',
                'sous_classes' => [
                    ['nom' => 'Domaine de la vie', 'niveau_sous_classe' => 2],
                    ['nom' => 'Domaine de la guerre', 'niveau_sous_classe' => 2],
                ],
            ],
            [
                'nom' => 'Druide',
                'de_de_vie' => '8',
                'description' => 'Un gardien de la nature capable de se transformer en bête.',
                'sous_classes' => [
                    ['nom' => 'Cercle des forêts', 'niveau_sous_classe' => 2],
                    ['nom' => 'Cercle de la lune', 'niveau_sous_classe' => 2],
                ],
            ],
            [
                'nom' => 'Guerrier',
                'de_de_vie' => '10',
                'description' => 'Un combattant expert armé et endurci.',
                'sous_classes' => [
                    ['nom' => 'Champion', 'niveau_sous_classe' => 3],
                    ['nom' => 'Maître de bataille', 'niveau_sous_classe' => 3],
                ],
            ],
            [
                'nom' => 'Moine',
                'de_de_vie' => '8',
                'description' => 'Un maître des arts martiaux et du ki.',
                'sous_classes' => [
                    ['nom' => 'Voie du moine ouvert', 'niveau_sous_classe' => 3],
                    ['nom' => 'Voie du shadow', 'niveau_sous_classe' => 3],
                ],
            ],
            [
                'nom' => 'Paladin',
                'de_de_vie' => '10',
                'description' => 'Un chevalier sacré jurant de combattre le mal.',
                'sous_classes' => [
                    ['nom' => 'Serment de dévotion', 'niveau_sous_classe' => 3],
                    ['nom' => 'Serment de vengeance', 'niveau_sous_classe' => 3],
                ],
            ],
            [
                'nom' => 'Rôdeur',
                'de_de_vie' => '10',
                'description' => 'Un traqueur expert des contrées sauvages.',
                'sous_classes' => [
                    ['nom' => 'Chasseur', 'niveau_sous_classe' => 3],
                    ['nom' => 'Gardien du cercle', 'niveau_sous_classe' => 3],
                ],
            ],
            [
                'nom' => 'Voleur',
                'de_de_vie' => '8',
                'description' => 'Un expert de la furtivité et du piège.',
                'sous_classes' => [
                    ['nom' => 'Voleur', 'niveau_sous_classe' => 3],
                    ['nom' => 'Assassin', 'niveau_sous_classe' => 3],
                ],
            ],
            [
                'nom' => 'Sorcier',
                'de_de_vie' => '6',
                'description' => 'Un magicien dont le pouvoir vient de son sang.',
                'sous_classes' => [
                    ['nom' => 'Sang draconique', 'niveau_sous_classe' => 1],
                    ['nom' => 'Magie sauvage', 'niveau_sous_classe' => 1],
                ],
            ],
            [
                'nom' => 'Mage',
                'de_de_vie' => '6',
                'description' => 'Un savant qui étudie les arcanes pour maîtriser la magie.',
                'sous_classes' => [
                    ['nom' => 'École de l\'évasion', 'niveau_sous_classe' => 2],
                    ['nom' => 'École de l\'évoque', 'niveau_sous_classe' => 2],
                ],
            ],
            [
                'nom' => 'Chasseur de monstres',
                'de_de_vie' => '10',
                'description' => 'Un spécialiste dans la traque des créatures monstrueuses.',
                'sous_classes' => [],
            ],
        ];

        foreach ($classes as $classeData) {
            $sousClasses = $classeData['sous_classes'];
            unset($classeData['sous_classes']);

            $classe = Classe::create($classeData);

            foreach ($sousClasses as $sc) {
                SousClasse::create([
                    'classe_id' => $classe->id,
                    'nom' => $sc['nom'],
                    'description' => null,
                    'niveau_sous_classe' => $sc['niveau_sous_classe'],
                ]);
            }
        }
    }
}
