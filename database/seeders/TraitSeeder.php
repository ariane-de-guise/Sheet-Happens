<?php

namespace Database\Seeders;

use App\Models\TraitModel;
use Illuminate\Database\Seeder;

class TraitSeeder extends Seeder
{
    public function run(): void
    {
        $traits = [
            // ==========================================
            // NIVEAU 1 : TRAITS RACIAUX GÉNÉRAUX & SOUS-RACES
            // ==========================================
            // Sens & Résistances de base
            [
                'nom' => 'Vision dans le noir',
                'niveau' => 1,
                'description' => 'Permet de voir à 18 mètres dans la pénombre comme dans la lumière vive, et dans le noir total comme dans la pénombre (en nuances de gris).',
            ],
            [
                'nom' => 'Vision dans le noir supérieure',
                'niveau' => 1,
                'description' => 'Portée de la vision dans le noir étendue à 36 mètres.',
            ],
            [
                'nom' => 'Ascendance féérique',
                'niveau' => 1,
                'description' => 'Avantage aux jets de sauvegarde contre les effets de charme et immunité contre le sommeil magique.',
            ],
            [
                'nom' => 'Transe',
                'niveau' => 1,
                'description' => 'Pas besoin de dormir. Médite profondément pendant 4 heures par jour pour obtenir les mêmes bénéfices qu’un repos long de 8 heures.',
            ],
            [
                'nom' => 'Résilience naine',
                'niveau' => 1,
                'description' => 'Avantage aux jets de sauvegarde contre le poison et résistance aux dégâts de poison.',
            ],
            [
                'nom' => 'Ténacité naine',
                'niveau' => 1,
                'description' => 'Le maximum de points de vie augmente de 1, et gagne 1 point de vie supplémentaire à chaque niveau supérieur.',
            ],
            [
                'nom' => 'Sens de la pierre',
                'niveau' => 1,
                'description' => 'Double le bonus de maîtrise pour tout test d’Intelligence (Histoire) lié à l’origine d’un travail de maçonnerie.',
            ],
            [
                'nom' => 'Entraînement aux armes naines',
                'niveau' => 1,
                'description' => 'Maîtrise de la hache de guerre, de la hachette, du marteau léger et du marteau de guerre.',
            ],
            [
                'nom' => 'Entraînement aux armes elfiques',
                'niveau' => 1,
                'description' => 'Maîtrise de l’épée longue, de l’épée courte, de l’arc court et de l’arc long.',
            ],
            [
                'nom' => 'Entraînement aux armes drows',
                'niveau' => 1,
                'description' => 'Maîtrise de la rapière, de l’épée courte et de l’arbalète de poing.',
            ],
            [
                'nom' => 'Sens aiguisés',
                'niveau' => 1,
                'description' => 'Maîtrise automatique de la compétence Perception.',
            ],
            [
                'nom' => 'Pied léger',
                'niveau' => 1,
                'description' => 'La vitesse de déplacement de base au sol augmente à 10,5 mètres (35 pieds).',
            ],
            [
                'nom' => 'Cachette naturelle',
                'niveau' => 1,
                'description' => 'Possibilité de tenter de se cacher même si seulement masqué par une créature d’au moins une taille supérieure.',
            ],
            [
                'nom' => 'Masque de la nature',
                'niveau' => 1,
                'description' => 'Possibilité de tenter de se cacher même en étant seulement légèrement dissimulé par le feuillage, une pluie battante, la neige ou la brume.',
            ],
            [
                'nom' => 'Sensibilité à la lumière du soleil',
                'niveau' => 1,
                'description' => 'Désavantage aux jets d’attaque et aux tests de Sagesse (Perception) basés sur la vue si exposé directement au soleil.',
            ],

            // Halfelins & Gnomes
            [
                'nom' => 'Chanceux',
                'niveau' => 1,
                'description' => 'Sur un résultat de 1 au d20 pour une attaque, un jet de sauvegarde ou un test de caractéristique, permet de relancer le dé (obligation de garder le nouveau résultat).',
            ],
            [
                'nom' => 'Brave',
                'niveau' => 1,
                'description' => 'Avantage aux jets de sauvegarde contre la terreur.',
            ],
            [
                'nom' => 'Agilité halfeline',
                'niveau' => 1,
                'description' => 'Permet de traverser l’espace de n’importe quelle créature ayant une taille supérieure à la sienne.',
            ],
            [
                'nom' => 'Résilience des Cœurs-Vaillants',
                'niveau' => 1,
                'description' => 'Avantage aux jets de sauvegarde contre le poison et résistance aux dégâts de poison.',
            ],
            [
                'nom' => 'Ruse gnome',
                'niveau' => 1,
                'description' => 'Avantage à tous les jets de sauvegarde d’Intelligence, de Sagesse et de Charisme contre la magie.',
            ],
            [
                'nom' => 'Connaissances d’ingénieur',
                'niveau' => 1,
                'description' => 'Double le bonus de maîtrise pour tout test d’Intelligence (Histoire) lié aux objets magiques, technologiques ou alchimiques.',
            ],
            [
                'nom' => 'Bricoleur',
                'niveau' => 1,
                'description' => 'Permet de fabriquer des mécanismes miniatures (allume-feu, boîte à musique, jouet mécanique) avec 1 heure et 10 po de matériaux.',
            ],
            [
                'nom' => 'Illusionniste né',
                'niveau' => 1,
                'description' => 'Connaît le tour de magie Illusion mineure (l’Intelligence est la caractéristique d’incantation).',
            ],
            [
                'nom' => 'Parler aux petites bêtes',
                'niveau' => 1,
                'description' => 'Permet de communiquer des idées simples aux bêtes de taille Petite ou inférieure.',
            ],

            // Drakéides, Demi-Orcs, Humains & Tieffelins
            [
                'nom' => 'Souffle draconique',
                'niveau' => 1,
                'description' => 'Exhale une vague d’énergie destructive selon l’ascendance draconique (2d6 dégâts au niv. 1, action, recharge après repos court ou long).',
            ],
            [
                'nom' => 'Résistance draconique',
                'niveau' => 1,
                'description' => 'Résistance aux dégâts du type associé à l’ascendance draconique (Feu, Froid, Foudre, Acide ou Poison).',
            ],
            [
                'nom' => 'Mençant',
                'niveau' => 1,
                'description' => 'Maîtrise automatique de la compétence Intimidation.',
            ],
            [
                'nom' => 'Acharnement',
                'niveau' => 1,
                'description' => 'Quand réduit à 0 PV sans être tué sur le coup, les PV tombent à 1 à la place. Utilisable une fois par repos long.',
            ],
            [
                'nom' => 'Attaques sauvages',
                'niveau' => 1,
                'description' => 'Lors d’un coup critique au corps-à-corps avec une arme, permet de lancer un dé de dégâts supplémentaire de l’arme.',
            ],
            [
                'nom' => 'Polyvalence des compétences',
                'niveau' => 1,
                'description' => 'Maîtrise de deux compétences au choix (spécifique aux Demi-Elfes et Humains variantes).',
            ],
            [
                'nom' => 'Résistance infernale',
                'niveau' => 1,
                'description' => 'Résistance naturelle aux dégâts de feu.',
            ],
            [
                'nom' => 'Magie innée : Thaumaturgie',
                'niveau' => 1,
                'description' => 'Connaît le tour de magie Thaumaturgie (Charisme pour l’incantation).',
            ],
            [
                'nom' => 'Magie drow : Lumières dansantes',
                'niveau' => 1,
                'description' => 'Connaît le tour de magie Lumières dansantes (Charisme pour l’incantation).',
            ],
            [
                'nom' => 'Mains curatives',
                'niveau' => 1,
                'description' => 'Permet de toucher une créature pour lui restaurer un nombre de points de vie égal à son niveau global. Une fois par repos long.',
            ],
            [
                'nom' => 'Porteur de lumière',
                'niveau' => 1,
                'description' => 'Connaît le tour de magie Lumière (Charisme pour l’incantation).',
            ],
            [
                'nom' => 'Résistance céleste',
                'niveau' => 1,
                'description' => 'Résistance naturelle aux dégâts nécrotiques et radiants.',
            ],

            // ==========================================
            // NIVEAU 3 : SORTS ET POUVOIRS INNÉS DÉBLOQUÉS
            // ==========================================
            [
                'nom' => 'Magie infernale : Représailles infernales',
                'niveau' => 3,
                'description' => 'Permet de lancer le sort Représailles infernales au niveau 2 une fois par repos long (Charisme comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Magie drow : Lueur féerique',
                'niveau' => 3,
                'description' => 'Permet de lancer le sort Lueur féerique une fois par repos long (Charisme comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Saut githyanki',
                'niveau' => 3,
                'description' => 'Permet de lancer le sort Saut une fois par repos long sans composante matérielle (Intelligence comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Saut de plume githzerai',
                'niveau' => 3,
                'description' => 'Permet de lancer le sort Chute de plume une fois par repos long (Sagesse comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Âme radieuse',
                'niveau' => 3,
                'description' => 'Déploie des ailes lumineuses pendant 1 min : vitesse de vol de 9 m, et inflige des dégâts radiants supplémentaires égaux à son niveau une fois par tour.',
            ],
            [
                'nom' => 'Linceul nécrotique',
                'niveau' => 3,
                'description' => 'Déploie une aura d’effroi pendant 1 min : effraie les ennemis proches (jet de sauvegarde Charisme) et inflige des dégâts nécrotiques supplémentaires égaux à son niveau.',
            ],
            [
                'nom' => 'Consomption radieuse',
                'niveau' => 3,
                'description' => 'Libère une lumière brûlante pendant 1 min : inflige des dégâts radiants aux créatures adjacentes et ajoute des dégâts radiants égaux à son niveau à une attaque.',
            ],

            // ==========================================
            // NIVEAU 5 : SORTS ET POUVOIRS INNÉS DÉBLOQUÉS
            // ==========================================
            [
                'nom' => 'Magie infernale : Ténèbres',
                'niveau' => 5,
                'description' => 'Permet de lancer le sort Ténèbres une fois par repos long (Charisme comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Magie drow : Ténèbres',
                'niveau' => 5,
                'description' => 'Permet de lancer le sort Ténèbres une fois par repos long sans composantes matérielles (Charisme comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Foulée brumeuse githyanki',
                'niveau' => 5,
                'description' => 'Permet de lancer le sort Foulée brumeuse une fois par repos long sans composante matérielle (Intelligence comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Détection des pensées githzerai',
                'niveau' => 5,
                'description' => 'Permet de lancer le sort Détection des pensées une fois par repos long sans composante matérielle (Sagesse comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Passage sans trace (Genasi de Terre)',
                'niveau' => 5,
                'description' => 'Permet de lancer le sort Passage sans trace une fois par repos long sans composantes matérielles (Constitution comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Lévitation (Genasi d’Air)',
                'niveau' => 5,
                'description' => 'Permet de lancer le sort Lévitation une fois par repos long sans composantes matérielles (Constitution comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Flammes intenses (Genasi de Feu)',
                'niveau' => 5,
                'description' => 'Permet de lancer le sort Lame ardente une fois par repos long (Constitution comme caractéristique d’incantation).',
            ],
            [
                'nom' => 'Création d’eau (Genasi d’Eau)',
                'niveau' => 5,
                'description' => 'Permet de lancer le sort Création ou destruction d’eau au niveau 2 une fois par repos long (Constitution comme caractéristique d’incantation).',
            ],
        ];

        foreach ($traits as $trait) {
            TraitModel::updateOrCreate(
                [
                    'nom' => $trait['nom'],
                    'niveau' => $trait['niveau'],
                ],
                [
                    'description' => $trait['description'],
                ]
            );
        }
    }
}
