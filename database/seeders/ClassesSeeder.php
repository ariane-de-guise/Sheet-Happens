<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\SousClasse;
use Illuminate\Database\Seeder;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        // "bonus_aptitudes" suit le même format que sur les races (voir
        // RacesSeeder) : un +N sur la caractéristique principale de la
        // classe, telle que listée dans limitations.txt. Ce sont des
        // valeurs de départ à ajuster en équipe si besoin — le Guerrier
        // n'a par exemple pas une caractéristique principale unique
        // ("Force ou Dextérité" dans limitations.txt), Force a été choisi
        // ici arbitrairement en attendant une décision d'équipe.
        $classes = [
            [
                'nom' => 'Barbare',
                'de_de_vie' => '12',
                'description' => 'Un guerrier sauvage qui entre dans une frénésie en combat.',
                'niveau_sous_classe' => 3,
                'competences_disponibles' => ['Dressage', 'Athlétisme', 'Intimidation', 'Nature', 'Perception', 'Survie'],
                'nombre_competences' => 2,
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
                'competences_disponibles' => ['Histoire', 'Intuition', 'Médecine', 'Persuasion', 'Religion'],
                'nombre_competences' => 2,
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
                'nom' => 'Roublard',
                'de_de_vie' => '8',
                'description' => 'Un expert de la furtivité et du piège.',
                'niveau_sous_classe' => 3,
                'competences_disponibles' => ['Acrobaties', 'Athlétisme', 'Art de la duplicité', 'Intuition', 'Intimidation', 'Investigation', 'Perception', 'Performance', 'Persuasion', 'Escamotage', 'Discrétion'],
                'nombre_competences' => 4,
                'sous_classes' => [
                    ['nom' => 'Voleur', 'description' => 'Roublard qui a perfectionné ses compétences dans les arts du vol'],
                    ['nom' => 'Assassin', 'description' => 'Roublard qui a concentré son entraînement sur l’art sombre de la mort'],
                    ['nom' => 'Filou Arcanique', 'description' => 'Roublard qui à perfectionné ses compétences à l\'aide de magie'],
                ],
            ],
            [
                'nom' => 'Magicien',
                'de_de_vie' => '6',
                'description' => 'Un savant qui étudie les arcanes pour maîtriser la magie.',
                'niveau_sous_classe' => 2,
                'competences_disponibles' => ['Arcanes', 'Histoire', 'Intuition', 'Investigation', 'Médecine', 'Religion'],
                'nombre_competences' => 2,
                'sous_classes' => [
                    ['nom' => 'École de l\'Abjuration', 'description' => 'Magicien qui se specialise dans la magie qui bloque, bannit ou protège.'],
                    ['nom' => 'École de la Conjuration', 'description' => 'Magicien qui se specialise dans les sorts qui produisent des objets et des créatures à partir de rien.'],
                    ['nom' => 'École de la Divination', 'description' => 'Magicien qui se specialise dans les sorts de discernement, la vision à distance, la connaissance surnaturelle et la clairvoyance.'],
                    ['nom' => 'École de l\'Enchantement', 'description' => 'Magicien qui a affiné sa capacité à captiver et séduire magiquement les autres et les monstres.'],
                    ['nom' => 'École de l\'Évocation', 'description' => 'Magicien qui se specialise dans la magie qui crée de puissants effets élémentaires tels que le froid mordant, les flammes brûlantes, le tonnerre roulant, la foudre crépitante et l’acide brûlant.'],
                    ['nom' => 'École de l\'Illusion', 'description' => 'Magicien qui se concentre sur une magie qui éblouit les sens, déroute l’esprit et trompe même les plus sages.'],
                    ['nom' => 'École de la Nécromancie', 'description' => 'Magicien qui se specialise dans la transformation de énergie vitale en puissance magique manipulable.'],
                    ['nom' => 'École de la Transmutation', 'description' => 'Magicien qui se specialise dans les sorts qui modifient l’énergie et la matière'],
                ],
            ],
            [
                'nom' => 'Guerrier',
                'de_de_vie' => '10',
                'description' => 'Un maître des armes et de l\'art de la guerre.',
                'niveau_sous_classe' => 3,
                'competences_disponibles' => ['Acrobaties', 'Dressage', 'Athlétisme', 'Histoire', 'Intuition', 'Intimidation', 'Perception', 'Survie'],
                'nombre_competences' => 2,
                'sous_classes' => [
                    ['nom' => 'Champion', 'description' => 'Un combattant direct et puissant. Il se concentre sur la force brute et l’optimisation des coups critiques, ce qui lui permet de réussir des attaques dévastatrices plus souvent que les autres.'],
                    ['nom' => 'Maître de guerre', 'description' => 'Un tacticien hors pair. Il utilise des « dés de supériorité » pour exécuter des manœuvres spéciales afin de perturber ses ennemis, contrôler le terrain ou protéger ses alliés'],
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
                ]);
            }
        }
    }
}
