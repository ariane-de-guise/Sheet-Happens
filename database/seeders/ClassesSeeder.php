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
                'nom' => 'Clerc',
                'de_de_vie' => '8',
                'description' => 'Un prêtre qui puise sa puissance dans sa divinité.',
                'sous_classes' => [
                    ['nom' => 'Domaine de la vie', 'niveau_sous_classe' => 1],
                    ['nom' => 'Domaine de la guerre', 'niveau_sous_classe' => 1],
                ],
            ],
            [
                'nom' => 'Roublard',
                'de_de_vie' => '8',
                'description' => 'Un expert de la furtivité et du piège.',
                'sous_classes' => [
                    ['nom' => 'Voleur', 'niveau_sous_classe' => 3],
                    ['nom' => 'Assassin', 'niveau_sous_classe' => 3],
                ],
            ],
            [
                'nom' => 'Magicien',
                'de_de_vie' => '6',
                'description' => 'Un savant qui étudie les arcanes pour maîtriser la magie.',
                'sous_classes' => [
                    ['nom' => 'École de l\'évasion', 'niveau_sous_classe' => 2],
                    ['nom' => 'École de l\'évoque', 'niveau_sous_classe' => 2],
                ],
            ],
            [
                'nom' => 'Guerrier',
                'de_de_vie' => '10',
                'description' => 'Un maître des armes et de l\'art de la guerre.',
                'sous_classes' => [
                    ['nom' => 'Champion', 'niveau_sous_classe' => 3],
                    ['nom' => 'Maître de guerre', 'niveau_sous_classe' => 3],
                ],
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
