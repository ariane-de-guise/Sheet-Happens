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
                'description' => 'Un guerrier sauvage qui entre dans une frénésie en combat.',
                'niveau_sous_classe' => 3,
                'sous_classes' => [
                    ['nom' => 'Barbare Berserk',  'description' => 'Barbare qui utilise la violence pour ses fins.'],
                    ['nom' => 'Barbare combattant Totem',  'description' => 'Barbare qui accept un animal totem comme guide, protecteur et source d\'inspiration.'],
                ],
            ],
            [
                'nom' => 'Clerc',
                'de_de_vie' => '8',
                'description' => 'Un prêtre qui puise sa puissance dans sa divinité.',
                'niveau_sous_classe' => 1,
                'sous_classes' => [
                    ['nom' => 'Domaine de la Vie',  'description' => 'Clerc qui valorise l’apprentissage et la compréhension avant tout.'],
                    ['nom' => 'Domaine de la Lumière',  'description' => 'Clerc qui promouvoit l\'énergie positive vibrante.'],
                    ['nom' => 'Domaine de la Connaissance',  'description' => 'Clerc qui promouvoit les idéaux de renaissance et de renouveau, de vérité, de vigilance et de beauté.'],
                    ['nom' => 'Domaine de la Nature',  'description' => 'Clerc qui promouvoit les intérêts d’un dieu de la nature.'],
                    ['nom' => 'Domaine de la Tempête',  'description' => 'Clerc envoyer par leur dieu pour inspirer la peur, maintenir la justice ou leur envoyer des sacrifices.'],
                    ['nom' => 'Domaine de la Ruse',  'description' => 'Clerc qui est une force perturbatrice dans le monde.'],
                    ['nom' => 'Domaine de la Guerre', 'description' => 'Clerc qui excel en combat grace à son dieu'],
                ],
            ],
            [
                'nom' => 'Combattant',
                'de_de_vie' => '8',
                'description' => 'Un combattant qui a une maîtrise inégalée des armes et des armures, ainsi qu’une connaissance approfondie des compétences de combat.',
                'niveau_sous_classe' => 3,
                'sous_classes' => [
                    ['nom' => 'Maitre de Bataille', 'description' => 'Combattant qui emploient des techniques martiales transmises de génération en génération.'],
                    ['nom' => 'Champion', 'description' => ''],
                    ['nom' => 'Chevalier Surnaturel', 'description' => ''],
                ],
            ],
            [
                'nom' => 'Roublard',
                'de_de_vie' => '8',
                'description' => 'Un expert de la furtivité et du piège.',
                'niveau_sous_classe' => 2,
                'sous_classes' => [
                    ['nom' => 'Voleur', 'description' => ''],
                    ['nom' => 'Assassin', 'description' => ''],
                ],
            ],
            [
                'nom' => 'Magicien',
                'de_de_vie' => '6',
                'description' => 'Un savant qui étudie les arcanes pour maîtriser la magie.',
                'niveau_sous_classe' => 2,
                'sous_classes' => [
                    ['nom' => 'École de l\'évasion', 'description' => ''],
                    ['nom' => 'École de l\'évoque', 'description' => ''],
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
                    'description' => $sc['description'],
                    'niveau_sous_classe' => $sc['niveau_sous_classe'],
                ]);
            }
        }
    }
}
