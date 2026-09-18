<?php

namespace Database\Seeders;

use App\Models\Objet;
use Illuminate\Database\Seeder;

/**
 * Catalogue d'objets de base (5e SRD), disponibles à tous les
 * utilisateurs (utilisateur_id = null). Idempotent (updateOrCreate par
 * nom) : peut être rejoué sans créer de doublon, y compris pour les objets
 * déjà créés à la volée par la migration de données de l'ancienne colonne
 * `personnages.equipement` (voir 2026_09_18_000003_migrer_equipement_vers_objets.php).
 */
class ObjetsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->objets() as $definition) {
            Objet::updateOrCreate(
                ['nom' => $definition['nom'], 'utilisateur_id' => null],
                $definition,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function objets(): array
    {
        return [
            ...$this->armures(),
            ...$this->boucliers(),
            ...$this->armesCourantes(),
            ...$this->armesDeGuerre(),
            ...$this->potions(),
            ...$this->equipementAventurier(),
            ...$this->outils(),
            ...$this->instruments(),
            ...$this->packs(),
            ...$this->objetsMagiques(),
        ];
    }

    /** Armures légères, intermédiaires et lourdes (12, table complète 5e SRD). */
    private function armures(): array
    {
        return [
            // Légères : plein bonus de Dextérité.
            ['nom' => 'Armure matelassée', 'type' => 'armure', 'categorie' => 'legere', 'cout_mains' => 0, 'ca_base' => 11, 'description' => 'Armure faite de couches de tissu et de rembourrage.'],
            ['nom' => 'Armure de cuir', 'type' => 'armure', 'categorie' => 'legere', 'cout_mains' => 0, 'ca_base' => 11, 'description' => 'Armure légère en cuir bouilli.'],
            ['nom' => 'Armure de cuir clouté', 'type' => 'armure', 'categorie' => 'legere', 'cout_mains' => 0, 'ca_base' => 12, 'description' => 'Cuir renforcé de rivets métalliques.'],

            // Intermédiaires : bonus de Dextérité plafonné à +2.
            ['nom' => 'Armure de fourrure', 'type' => 'armure', 'categorie' => 'intermediaire', 'cout_mains' => 0, 'ca_base' => 12, 'description' => 'Peaux de bêtes traitées, armure rudimentaire.'],
            ['nom' => 'Chemise de mailles', 'type' => 'armure', 'categorie' => 'intermediaire', 'cout_mains' => 0, 'ca_base' => 13, 'description' => 'Chemise de mailles couvrant le torse.'],
            ['nom' => 'Armure d\'écailles', 'type' => 'armure', 'categorie' => 'intermediaire', 'cout_mains' => 0, 'ca_base' => 14, 'description' => 'Armure de petites plaques de métal superposées.'],
            ['nom' => 'Cuirasse', 'type' => 'armure', 'categorie' => 'intermediaire', 'cout_mains' => 0, 'ca_base' => 14, 'description' => 'Plastron de métal ajusté.'],
            ['nom' => 'Demi-plate', 'type' => 'armure', 'categorie' => 'intermediaire', 'cout_mains' => 0, 'ca_base' => 15, 'description' => 'Plaques articulées couvrant l\'essentiel du corps.'],

            // Lourdes : aucun bonus de Dextérité.
            ['nom' => 'Cotte à anneaux', 'type' => 'armure', 'categorie' => 'lourde', 'cout_mains' => 0, 'ca_base' => 14, 'description' => 'Anneaux de métal cousus sur une armure de cuir.'],
            ['nom' => 'Cotte de mailles', 'type' => 'armure', 'categorie' => 'lourde', 'cout_mains' => 0, 'ca_base' => 16, 'description' => 'Armure complète de mailles métalliques.'],
            ['nom' => 'Armure clissée', 'type' => 'armure', 'categorie' => 'lourde', 'cout_mains' => 0, 'ca_base' => 17, 'description' => 'Lattes de métal fixées sur du cuir.'],
            ['nom' => 'Armure de plates', 'type' => 'armure', 'categorie' => 'lourde', 'cout_mains' => 0, 'ca_base' => 18, 'description' => 'Armure de plaques articulées, la plus protectrice.'],
        ];
    }

    private function boucliers(): array
    {
        return [
            ['nom' => 'Bouclier', 'type' => 'bouclier', 'cout_mains' => 1, 'bonus' => ['ca' => 2], 'description' => '+2 à la Classe d\'Armure tant qu\'il est équipé.'],
        ];
    }

    /** Armes courantes (simples) : corps à corps et à distance. */
    private function armesCourantes(): array
    {
        return [
            ['nom' => 'Gourdin', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d4', 'caracteristique_degats' => 'force', 'type_degats' => 'contondant', 'description' => 'Arme simple et rudimentaire.'],
            ['nom' => 'Dague', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d4', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Arme légère et rapide, se manie à la Dextérité.'],
            ['nom' => 'Massue', 'type' => 'arme', 'categorie' => 'deux_mains', 'cout_mains' => 2, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'contondant', 'description' => 'Gourdin de grande taille, se manie à deux mains.'],
            ['nom' => 'Hachette', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d6', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'description' => 'Petite hache, peut être lancée.'],
            ['nom' => 'Marteau léger', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d4', 'caracteristique_degats' => 'force', 'type_degats' => 'contondant', 'description' => 'Petit marteau, peut être lancé.'],
            ['nom' => 'Javeline', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d6', 'caracteristique_degats' => 'force', 'type_degats' => 'perforant', 'description' => 'Arme de jet simple.'],
            ['nom' => 'Masse d\'armes', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d6', 'caracteristique_degats' => 'force', 'type_degats' => 'contondant', 'description' => 'Arme simple à tête lourde.'],
            ['nom' => 'Bâton', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d6', 'caracteristique_degats' => 'force', 'type_degats' => 'contondant', 'description' => 'Long bâton de bois, arme simple.'],
            ['nom' => 'Faucille', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d4', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'description' => 'Lame courbe légère.'],
            ['nom' => 'Épieu', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d6', 'caracteristique_degats' => 'force', 'type_degats' => 'perforant', 'description' => 'Longue perche munie d\'une pointe, peut être lancée.'],
            ['nom' => 'Fléchette', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d4', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Petit projectile équilibré, se manie à la Dextérité.'],
            ['nom' => 'Arc court', 'type' => 'arme', 'categorie' => 'distance', 'cout_mains' => 2, 'degats' => '1d6', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Arc léger à courte portée.'],
            ['nom' => 'Fronde', 'type' => 'arme', 'categorie' => 'distance', 'cout_mains' => 1, 'degats' => '1d4', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'contondant', 'description' => 'Simple lanière de cuir pour projeter des pierres.'],
            ['nom' => 'Arbalète légère', 'type' => 'arme', 'categorie' => 'distance', 'cout_mains' => 2, 'degats' => '1d8', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Arbalète simple, se recharge rapidement.'],
        ];
    }

    /** Armes de guerre (martiales) : corps à corps et à distance. */
    private function armesDeGuerre(): array
    {
        return [
            ['nom' => 'Hache de guerre', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'description' => 'Hache à une main, arme de guerre courante.'],
            ['nom' => 'Fléau d\'armes', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'contondant', 'description' => 'Boulet articulé au bout d\'une chaîne.'],
            ['nom' => 'Glaive', 'type' => 'arme', 'categorie' => 'deux_mains', 'cout_mains' => 2, 'degats' => '1d10', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'description' => 'Lame montée sur une longue hampe.'],
            ['nom' => 'Hache d\'armes', 'type' => 'arme', 'categorie' => 'deux_mains', 'cout_mains' => 2, 'degats' => '1d12', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'description' => 'Grande hache à deux mains.'],
            ['nom' => 'Épée à deux mains', 'type' => 'arme', 'categorie' => 'deux_mains', 'cout_mains' => 2, 'degats' => '2d6', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'description' => 'Immense épée nécessitant les deux mains.'],
            ['nom' => 'Hallebarde', 'type' => 'arme', 'categorie' => 'deux_mains', 'cout_mains' => 2, 'degats' => '1d10', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'description' => 'Arme d\'hast à lame et pointe combinées.'],
            ['nom' => 'Lance de cavalerie', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d12', 'caracteristique_degats' => 'force', 'type_degats' => 'perforant', 'description' => 'Longue lance conçue pour le combat monté.'],
            ['nom' => 'Épée', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'description' => 'Épée longue, arme de corps à corps standard.'],
            ['nom' => 'Marteau-piqueur', 'type' => 'arme', 'categorie' => 'deux_mains', 'cout_mains' => 2, 'degats' => '2d6', 'caracteristique_degats' => 'force', 'type_degats' => 'contondant', 'description' => 'Immense marteau à deux mains.'],
            ['nom' => 'Étoile du matin', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'perforant', 'description' => 'Masse hérissée de pointes.'],
            ['nom' => 'Pique', 'type' => 'arme', 'categorie' => 'deux_mains', 'cout_mains' => 2, 'degats' => '1d10', 'caracteristique_degats' => 'force', 'type_degats' => 'perforant', 'description' => 'Longue perche munie d\'une pointe.'],
            ['nom' => 'Rapière', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Lame fine et précise, se manie à la Dextérité.'],
            ['nom' => 'Cimeterre', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d6', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'tranchant', 'description' => 'Lame courbe et légère, se manie à la Dextérité.'],
            ['nom' => 'Épée courte', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d6', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Lame courte et légère, se manie à la Dextérité.'],
            ['nom' => 'Trident', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d6', 'caracteristique_degats' => 'force', 'type_degats' => 'perforant', 'description' => 'Arme à trois pointes, peut être lancée.'],
            ['nom' => 'Marteau de guerre', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'contondant', 'description' => 'Marteau de guerre à une main.'],
            ['nom' => 'Pic de guerre', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'perforant', 'description' => 'Pic capable de percer les armures.'],
            ['nom' => 'Fouet', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d4', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'tranchant', 'description' => 'Lanière longue et souple, se manie à la Dextérité.'],
            ['nom' => 'Sarbacane', 'type' => 'arme', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Tube permettant de souffler de petites fléchettes.'],
            ['nom' => 'Arbalète de poing', 'type' => 'arme', 'categorie' => 'distance', 'cout_mains' => 1, 'degats' => '1d6', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Petite arbalète maniable à une seule main.'],
            ['nom' => 'Arbalète lourde', 'type' => 'arme', 'categorie' => 'distance', 'cout_mains' => 2, 'degats' => '1d10', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Arbalète massive, arme de guerre.'],
            ['nom' => 'Arc long', 'type' => 'arme', 'categorie' => 'distance', 'cout_mains' => 2, 'degats' => '1d8', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'description' => 'Arc puissant à longue portée.'],
            ['nom' => 'Filet', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Arme spéciale servant à entraver un adversaire (pas de dégâts).'],
        ];
    }

    /** Potions : effet instantané uniquement, toute durée reste narrative. */
    private function potions(): array
    {
        return [
            [
                'nom' => 'Potion de soins',
                'type' => 'potion',
                'cout_mains' => 0,
                'est_consommable' => true,
                'effet_utilisation' => ['pv' => 7],
                'description' => 'Rend 2d4+2 points de vie (7 en moyenne) dès son utilisation.',
            ],
            [
                'nom' => 'Potion de soins supérieurs',
                'type' => 'potion',
                'cout_mains' => 0,
                'est_consommable' => true,
                'effet_utilisation' => ['pv' => 14],
                'description' => 'Rend 4d4+4 points de vie (14 en moyenne) dès son utilisation.',
            ],
            [
                'nom' => 'Potion de soins suprêmes',
                'type' => 'potion',
                'cout_mains' => 0,
                'est_consommable' => true,
                'effet_utilisation' => ['pv' => 40],
                'description' => 'Rend 10d4+20 points de vie (40 en moyenne) dès son utilisation.',
            ],
            [
                'nom' => 'Potion de force de géant',
                'type' => 'potion',
                'magique' => true,
                'rarete' => 'peu_commun',
                'cout_mains' => 0,
                'est_consommable' => true,
                'effet_utilisation' => ['bonus' => ['force' => 4], 'duree_valeur' => 60, 'duree_unite' => 'minutes', 'duree_secondes' => 3600],
                'description' => 'Porte la Force à 21 (représenté ici par +4) pendant 1 heure. Le bonus s\'applique dès l\'utilisation et disparaît automatiquement à l\'expiration.',
            ],
            [
                'nom' => 'Élixir de rage',
                'type' => 'potion',
                'magique' => true,
                'rarete' => 'peu_commun',
                'cout_mains' => 0,
                'est_consommable' => true,
                'effet_utilisation' => ['bonus' => ['bonus_degats' => 2], 'duree_valeur' => 10, 'duree_unite' => 'tours', 'duree_secondes' => 60],
                'description' => 'Augmente les dégâts de +2 pendant 10 tours de combat. Durée décomptée soi-même tour par tour (pas d\'horloge fiable en combat), depuis la section "Effets actifs" de l\'inventaire.',
            ],
            [
                'nom' => 'Potion de rapidité',
                'type' => 'potion',
                'magique' => true,
                'rarete' => 'rare',
                'cout_mains' => 0,
                'est_consommable' => true,
                'description' => 'Effet équivalent au sort Hâte pendant 1 minute (10 tours). Effet à gérer soi-même.',
            ],
            [
                'nom' => 'Potion d\'invisibilité',
                'type' => 'potion',
                'magique' => true,
                'rarete' => 'rare',
                'cout_mains' => 0,
                'est_consommable' => true,
                'description' => 'Rend invisible pendant 1 heure, ou jusqu\'à ce qu\'une attaque soit portée. Effet à gérer soi-même.',
            ],
            [
                'nom' => 'Antitoxine',
                'type' => 'potion',
                'cout_mains' => 0,
                'est_consommable' => true,
                'description' => 'Donne l\'avantage aux jets de sauvegarde contre le poison pendant 1 heure. Effet à gérer soi-même.',
            ],
        ];
    }

    /** Équipement d'aventurier courant. */
    private function equipementAventurier(): array
    {
        return [
            ['nom' => 'Sac à dos', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Sac de transport standard.'],
            ['nom' => 'Cordes', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Corde de chanvre, 15 mètres.'],
            ['nom' => 'Lanternes', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Lanterne à huile.'],
            ['nom' => 'Livre', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Ouvrage relié.'],
            ['nom' => 'Torche', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Brûle environ 1 heure, éclaire sur 6 mètres.'],
            ['nom' => 'Rations de voyage (1 jour)', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Nourriture séchée pour une journée.'],
            ['nom' => 'Grappin', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Crochet de métal à quatre branches.'],
            ['nom' => 'Sac de couchage', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Pour dormir à la belle étoile.'],
            ['nom' => 'Gourde', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Contient environ 1,2 litre de liquide.'],
            ['nom' => 'Tente à deux places', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Abri simple pour deux personnes.'],
            ['nom' => 'Couverture', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Couverture de laine.'],
            ['nom' => 'Bâton de marche', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Bâton de bois utile pour marcher ou comme arme de fortune.'],
            ['nom' => 'Pied-de-biche', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Barre de métal pour forcer les portes et les coffres.'],
            ['nom' => 'Pelle', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Outil pour creuser.'],
            ['nom' => 'Seau', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Seau de bois ou de métal.'],
            ['nom' => 'Marteau', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Marteau ordinaire.'],
            ['nom' => 'Piton', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Pointe de métal pour s\'arrimer à une paroi.'],
            ['nom' => 'Sifflet', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Petit sifflet pour donner un signal.'],
            ['nom' => 'Miroir en acier poli', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Petit miroir de poche.'],
            ['nom' => 'Parchemin (feuille)', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Feuille de parchemin vierge.'],
            ['nom' => 'Encre et plume', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Flacon d\'encre et plume à écrire.'],
            ['nom' => 'Bougie', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Brûle environ 1 heure.'],
            ['nom' => 'Craie (bâton)', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Utile pour marquer un chemin.'],
            ['nom' => 'Chaîne (3 mètres)', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Chaîne de métal robuste.'],
            ['nom' => 'Cadenas', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Serrure portative.'],
            ['nom' => 'Sac de billes de métal', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Peut être répandu au sol pour ralentir des poursuivants.'],
            ['nom' => 'Perche (3 mètres)', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Longue perche de bois.'],
            ['nom' => 'Harpe', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Instrument de musique à cordes.'],
        ];
    }

    /** Outils : kits d'artisan, kits de compétence et instruments de survie. */
    private function outils(): array
    {
        return [
            ['nom' => 'Trousse de soins', 'type' => 'outil', 'cout_mains' => 0, 'description' => '10 utilisations, permet de stabiliser un blessé.'],
            ['nom' => 'Silex et acier', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Permet d\'allumer un feu.'],
            ['nom' => 'Outils de voleur', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Nécessaires pour crocheter serrures et désamorcer pièges.'],
            ['nom' => 'Kit de déguisement', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Maquillage, teintures et accessoires pour changer d\'apparence.'],
            ['nom' => 'Kit de faussaire', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Nécessaire pour reproduire documents et sceaux.'],
            ['nom' => 'Kit d\'herboriste', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Utile pour identifier et préparer des plantes, dont des potions.'],
            ['nom' => 'Outils de navigateur', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Instruments pour tracer une route en mer.'],
            ['nom' => 'Outils d\'artisan', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Nécessaire générique pour un artisanat.'],
            ['nom' => 'Kit de calligraphe', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Encres, plumes et matériel de calligraphie.'],
            ['nom' => 'Kit de charpentier', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Outils de travail du bois.'],
            ['nom' => 'Kit de cuisinier', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Ustensiles de cuisine de campagne.'],
            ['nom' => 'Kit de joueur', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Dés ou cartes à jouer.'],
            ['nom' => 'Kit de potier', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Outils pour façonner et cuire l\'argile.'],
        ];
    }

    /** Instruments de musique. */
    private function instruments(): array
    {
        return [
            ['nom' => 'Luth', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Instrument de musique à cordes.'],
            ['nom' => 'Flûte', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Instrument de musique à vent.'],
            ['nom' => 'Tambour', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Instrument de percussion.'],
            ['nom' => 'Cornemuse', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Instrument de musique à vent et à réserve d\'air.'],
            ['nom' => 'Cor de chasse', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Instrument de musique à vent en corne ou métal.'],
            ['nom' => 'Viole', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Instrument de musique à cordes et archet.'],
            ['nom' => 'Cloches à main', 'type' => 'outil', 'cout_mains' => 0, 'description' => 'Petites cloches accordées.'],
        ];
    }

    /** Packs d'équipement (bundles pré-composés). */
    private function packs(): array
    {
        return [
            ['nom' => 'Pack du cambrioleur', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Sac à dos, outils de crochetage, corde, sac de billes, et plus.'],
            ['nom' => 'Pack du diplomate', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Coffre, cartes, tenue de cérémonie et matériel d\'écriture.'],
            ['nom' => 'Pack de l\'aventurier des souterrains', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Sac à dos, torches, corde, pied-de-biche et rations.'],
            ['nom' => 'Pack de l\'artiste', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Sac à dos, costumes, et un instrument de musique.'],
            ['nom' => 'Kit d\'explorateur', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Sac à dos, corde, torches, rations et matériel de base pour l\'aventure.'],
            ['nom' => 'Pack du prêtre', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Sac à dos, couverture, bougies, encens et symbole sacré.'],
            ['nom' => 'Pack de l\'érudit', 'type' => 'autre', 'cout_mains' => 0, 'description' => 'Sac, livre de connaissances, matériel d\'écriture et parchemin.'],
        ];
    }

    /**
     * Objets magiques (5e DMG) : armes et armures enchantées (bonus
     * d'attaque/dégâts/CA, actif tant qu'équipés) et objets à porter
     * (anneaux, amulettes, ceintures — modélisés en `bonus_toujours_actif`
     * faute d'emplacement d'équipement dédié pour ce genre d'objet, cf.
     * InventaireService). Les scores de caractéristique "fixés" par
     * certains objets officiels (ex. Force à 19) sont représentés ici par
     * un bonus additif approximatif plutôt qu'une valeur plancher, pour
     * rester cohérent avec le système de bonus additif de l'application.
     */
    private function objetsMagiques(): array
    {
        return [
            ['nom' => 'Épée +1', 'type' => 'arme', 'magique' => true, 'rarete' => 'peu_commun', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'bonus' => ['bonus_attaque' => 1, 'bonus_degats' => 1], 'description' => 'Épée enchantée : +1 aux jets d\'attaque et de dégâts tant qu\'elle est équipée.'],
            ['nom' => 'Épée +2', 'type' => 'arme', 'magique' => true, 'rarete' => 'rare', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'bonus' => ['bonus_attaque' => 2, 'bonus_degats' => 2], 'description' => 'Épée enchantée : +2 aux jets d\'attaque et de dégâts tant qu\'elle est équipée.'],
            ['nom' => 'Épée +3', 'type' => 'arme', 'magique' => true, 'rarete' => 'tres_rare', 'categorie' => 'une_main', 'cout_mains' => 1, 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant', 'bonus' => ['bonus_attaque' => 3, 'bonus_degats' => 3], 'description' => 'Épée enchantée : +3 aux jets d\'attaque et de dégâts tant qu\'elle est équipée.'],
            ['nom' => 'Arc long +1', 'type' => 'arme', 'magique' => true, 'rarete' => 'peu_commun', 'categorie' => 'distance', 'cout_mains' => 2, 'degats' => '1d8', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant', 'bonus' => ['bonus_attaque' => 1, 'bonus_degats' => 1], 'description' => 'Arc enchanté : +1 aux jets d\'attaque et de dégâts tant qu\'il est équipé.'],

            ['nom' => 'Armure de cuir +1', 'type' => 'armure', 'magique' => true, 'rarete' => 'peu_commun', 'categorie' => 'legere', 'cout_mains' => 0, 'ca_base' => 11, 'bonus' => ['ca' => 1], 'description' => 'Armure enchantée : +1 à la Classe d\'Armure en plus du calcul habituel.'],
            ['nom' => 'Cotte de mailles +1', 'type' => 'armure', 'magique' => true, 'rarete' => 'rare', 'categorie' => 'lourde', 'cout_mains' => 0, 'ca_base' => 16, 'bonus' => ['ca' => 1], 'description' => 'Armure enchantée : +1 à la Classe d\'Armure en plus du calcul habituel.'],
            ['nom' => 'Armure de plates +2', 'type' => 'armure', 'magique' => true, 'rarete' => 'tres_rare', 'categorie' => 'lourde', 'cout_mains' => 0, 'ca_base' => 18, 'bonus' => ['ca' => 2], 'description' => 'Armure enchantée : +2 à la Classe d\'Armure en plus du calcul habituel.'],

            ['nom' => 'Bouclier +1', 'type' => 'bouclier', 'magique' => true, 'rarete' => 'peu_commun', 'cout_mains' => 1, 'bonus' => ['ca' => 3], 'description' => 'Bouclier enchanté : +3 à la Classe d\'Armure (2 de base +1 magique) tant qu\'il est équipé.'],
            ['nom' => 'Bouclier +2', 'type' => 'bouclier', 'magique' => true, 'rarete' => 'rare', 'cout_mains' => 1, 'bonus' => ['ca' => 4], 'description' => 'Bouclier enchanté : +4 à la Classe d\'Armure (2 de base +2 magique) tant qu\'il est équipé.'],

            ['nom' => 'Anneau de protection', 'type' => 'autre', 'magique' => true, 'rarete' => 'rare', 'cout_mains' => 0, 'bonus' => ['ca' => 1, 'bonus_jets_sauvegarde' => 1], 'bonus_toujours_actif' => true, 'description' => 'Anneau enchanté : +1 à la Classe d\'Armure et à tous les jets de sauvegarde, en permanence.'],
            ['nom' => 'Cape de protection', 'type' => 'autre', 'magique' => true, 'rarete' => 'peu_commun', 'cout_mains' => 0, 'bonus' => ['ca' => 1, 'bonus_jets_sauvegarde' => 1], 'bonus_toujours_actif' => true, 'description' => 'Cape enchantée : +1 à la Classe d\'Armure et à tous les jets de sauvegarde, en permanence.'],
            ['nom' => 'Ceinture de force de géant des collines', 'type' => 'autre', 'magique' => true, 'rarete' => 'rare', 'cout_mains' => 0, 'bonus' => ['force' => 4], 'bonus_toujours_actif' => true, 'description' => 'Porte la Force à 21 en permanence (représenté ici par un bonus additif de +4).'],
            ['nom' => 'Gantelets de force du colosse', 'type' => 'autre', 'magique' => true, 'rarete' => 'rare', 'cout_mains' => 0, 'bonus' => ['force' => 4], 'bonus_toujours_actif' => true, 'description' => 'Porte la Force à 19 en permanence (représenté ici par un bonus additif de +4).'],
            ['nom' => 'Amulette de santé', 'type' => 'autre', 'magique' => true, 'rarete' => 'rare', 'cout_mains' => 0, 'bonus' => ['constitution' => 4], 'bonus_toujours_actif' => true, 'description' => 'Porte la Constitution à 19 en permanence (représenté ici par un bonus additif de +4).'],
            ['nom' => 'Bandeau d\'intelligence', 'type' => 'autre', 'magique' => true, 'rarete' => 'rare', 'cout_mains' => 0, 'bonus' => ['intelligence' => 4], 'bonus_toujours_actif' => true, 'description' => 'Porte l\'Intelligence à 19 en permanence (représenté ici par un bonus additif de +4).'],
            ['nom' => 'Chapelet de sagesse', 'type' => 'autre', 'magique' => true, 'rarete' => 'rare', 'cout_mains' => 0, 'bonus' => ['sagesse' => 4], 'bonus_toujours_actif' => true, 'description' => 'Porte la Sagesse à 19 en permanence (représenté ici par un bonus additif de +4).'],
            ['nom' => 'Sac sans fond', 'type' => 'autre', 'magique' => true, 'rarete' => 'peu_commun', 'cout_mains' => 0, 'description' => 'Espace extradimensionnel, contient bien plus que sa taille ne le laisse penser.'],
            ['nom' => 'Bottes de vitesse', 'type' => 'autre', 'magique' => true, 'rarete' => 'rare', 'cout_mains' => 0, 'description' => 'Double la vitesse de déplacement pendant 10 minutes, une fois par jour.'],
        ];
    }
}
