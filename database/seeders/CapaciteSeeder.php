<?php

namespace Database\Seeders;

use App\Models\Capacite;
use App\Models\Classe;
use App\Models\Personnage;
use App\Models\SousClasse;
use App\Services\CapaciteService;
use Illuminate\Database\Seeder;

class CapaciteSeeder extends Seeder
{
    /**
     * Capacités de classe PHB 2014, niveaux 1 à 5 (niveau maximum pris en
     * charge par la montée de niveau). Classes et sous-classes sont
     * retrouvées par leur nom ; celles absentes de la base sont ignorées.
     *
     * Format : [niveau, nom, description]
     */
    private const CLASSES = [
        'Barbare' => [
            [1, 'Rage', 'Par une action bonus, entrez en rage pendant 1 minute : avantage aux tests et sauvegardes de Force, bonus de +2 aux dégâts des attaques au corps à corps utilisant la Force, et résistance aux dégâts contondants, perforants et tranchants. Utilisable 2 fois (3 au niveau 3) par repos long.'],
            [1, 'Défense sans armure', 'Sans armure, votre CA est égale à 10 + modificateur de Dextérité + modificateur de Constitution. Vous pouvez tout de même utiliser un bouclier.'],
            [2, 'Attaque téméraire', 'Lors de votre première attaque du tour, vous pouvez attaquer témérairement : avantage aux attaques au corps à corps utilisant la Force pendant ce tour, mais les attaques contre vous ont l’avantage jusqu’à votre prochain tour.'],
            [2, 'Sens du danger', 'Avantage aux jets de sauvegarde de Dextérité contre les effets que vous pouvez voir (pièges, sorts…), tant que vous n’êtes pas aveuglé, assourdi ou neutralisé.'],
            [3, 'Voie primitive', 'Vous choisissez la voie qui façonne la nature de votre rage (sous-classe).'],
            [4, 'Amélioration de caractéristiques ou don', 'Augmentez une caractéristique de 2, ou deux caractéristiques de 1 (maximum 20), ou choisissez un don.'],
            [5, 'Attaque supplémentaire', 'Vous pouvez attaquer deux fois, au lieu d’une, lorsque vous effectuez l’action Attaquer.'],
            [5, 'Déplacement rapide', 'Votre vitesse augmente de 3 mètres tant que vous ne portez pas d’armure lourde.'],
        ],
        'Clerc' => [
            [1, 'Incantation', 'Vous préparez et lancez des sorts de clerc en utilisant la Sagesse comme caractéristique d’incantation. Un symbole sacré peut servir de focaliseur.'],
            [1, 'Domaine divin', 'Vous choisissez un domaine lié à votre divinité (sous-classe), qui vous accorde des sorts de domaine et des capacités supplémentaires.'],
            [2, 'Canalisation d’énergie divine', 'Une fois par repos court ou long, canalisez l’énergie de votre divinité : Renvoi des morts-vivants (les morts-vivants à 9 mètres qui ratent une sauvegarde de Sagesse fuient pendant 1 minute) ou l’effet accordé par votre domaine.'],
            [2, 'Capacité de domaine divin', 'Votre domaine vous accorde un nouvel usage de Canalisation d’énergie divine.'],
            [3, 'Emplacements de sorts de niveau 2', 'Vous pouvez désormais préparer et lancer des sorts de niveau 2.'],
            [4, 'Amélioration de caractéristiques ou don', 'Augmentez une caractéristique de 2, ou deux caractéristiques de 1 (maximum 20), ou choisissez un don.'],
            [4, 'Sort mineur supplémentaire', 'Vous apprenez un sort mineur de clerc supplémentaire.'],
            [5, 'Détruire les morts-vivants (FP 1/2)', 'Lorsqu’un mort-vivant de FP 1/2 ou moins rate sa sauvegarde contre votre Renvoi des morts-vivants, il est instantanément détruit.'],
            [5, 'Emplacements de sorts de niveau 3', 'Vous pouvez désormais préparer et lancer des sorts de niveau 3.'],
        ],
        'Roublard' => [
            [1, 'Expertise', 'Choisissez deux compétences maîtrisées (ou une compétence et les outils de voleur) : votre bonus de maîtrise est doublé pour les tests qui les utilisent.'],
            [1, 'Attaque sournoise 1d6', 'Une fois par tour, infligez 1d6 dégâts supplémentaires à une créature touchée avec une arme de finesse ou à distance, si vous avez l’avantage ou si un allié non neutralisé est à 1,5 mètre de la cible.'],
            [1, 'Jargon des voleurs', 'Vous connaissez l’argot secret des voleurs, un mélange de dialecte, de jargon et de codes permettant de cacher des messages dans une conversation anodine.'],
            [2, 'Ruse', 'À chacun de vos tours, vous pouvez utiliser une action bonus pour Foncer, vous Désengager ou vous Cacher.'],
            [3, 'Archétype de roublard', 'Vous choisissez l’archétype qui oriente vos talents (sous-classe).'],
            [3, 'Attaque sournoise 2d6', 'Les dégâts de votre Attaque sournoise passent à 2d6.'],
            [4, 'Amélioration de caractéristiques ou don', 'Augmentez une caractéristique de 2, ou deux caractéristiques de 1 (maximum 20), ou choisissez un don.'],
            [5, 'Esquive instinctive', 'Lorsqu’un attaquant que vous voyez vous touche, vous pouvez utiliser votre réaction pour diviser par deux les dégâts de cette attaque.'],
            [5, 'Attaque sournoise 3d6', 'Les dégâts de votre Attaque sournoise passent à 3d6.'],
        ],
        'Magicien' => [
            [1, 'Incantation', 'Vous lancez des sorts de magicien inscrits dans votre grimoire, en utilisant l’Intelligence comme caractéristique d’incantation.'],
            [1, 'Restauration arcanique', 'Une fois par jour, lors d’un repos court, récupérez des emplacements de sorts dont le niveau cumulé est au plus égal à la moitié de votre niveau de magicien (arrondi au supérieur).'],
            [2, 'Tradition arcanique', 'Vous choisissez une école de magie dans laquelle vous vous spécialisez (sous-classe).'],
            [3, 'Emplacements de sorts de niveau 2', 'Vous pouvez désormais préparer et lancer des sorts de niveau 2.'],
            [4, 'Amélioration de caractéristiques ou don', 'Augmentez une caractéristique de 2, ou deux caractéristiques de 1 (maximum 20), ou choisissez un don.'],
            [4, 'Sort mineur supplémentaire', 'Vous apprenez un sort mineur de magicien supplémentaire.'],
            [5, 'Emplacements de sorts de niveau 3', 'Vous pouvez désormais préparer et lancer des sorts de niveau 3.'],
        ],
        'Guerrier' => [
            [1, 'Style de combat', 'Vous adoptez un style de combat (Archerie, Défense, Duel, Arme à deux mains, Protection ou Combat à deux armes) qui vous confère un bonus permanent.'],
            [1, 'Second souffle', 'Une fois par repos court ou long, utilisez une action bonus pour récupérer 1d10 + votre niveau de guerrier en points de vie.'],
            [2, 'Sursaut d’activité', 'Une fois par repos court ou long, vous pouvez effectuer une action supplémentaire pendant votre tour.'],
            [3, 'Archétype martial', 'Vous choisissez l’archétype qui définit votre approche du combat (sous-classe).'],
            [4, 'Amélioration de caractéristiques ou don', 'Augmentez une caractéristique de 2, ou deux caractéristiques de 1 (maximum 20), ou choisissez un don.'],
            [5, 'Attaque supplémentaire', 'Vous pouvez attaquer deux fois, au lieu d’une, lorsque vous effectuez l’action Attaquer.'],
        ],
    ];

    private const SOUS_CLASSES = [
        'Barbare' => [
            'Barbare Berserk' => [
                [3, 'Frénésie', 'En entrant en rage, vous pouvez devenir frénétique : pendant la rage, vous pouvez effectuer une attaque au corps à corps par une action bonus à chaque tour. À la fin de la rage, vous subissez un niveau d’épuisement.'],
            ],
            'Barbare combattant Totem' => [
                [3, 'Chercheur spirituel', 'Vous pouvez lancer les sorts Communication avec les animaux et Sens des bêtes, uniquement sous forme de rituel.'],
                [3, 'Esprit totem', 'Choisissez un animal totem : Ours (résistance à tous les dégâts sauf psychiques pendant la rage), Aigle (Foncer par une action bonus, attaques d’opportunité avec désavantage) ou Loup (vos alliés ont l’avantage au corps à corps contre les ennemis proches de vous).'],
            ],
        ],
        'Clerc' => [
            'Domaine de la Vie' => [
                [1, 'Maîtrise supplémentaire', 'Vous gagnez la maîtrise des armures lourdes.'],
                [1, 'Disciple de la vie', 'Vos sorts de soins de niveau 1 ou plus rendent 2 + le niveau du sort points de vie supplémentaires.'],
                [2, 'Canalisation : Préserver la vie', 'Par une action, répartissez un nombre de points de vie égal à cinq fois votre niveau de clerc entre les créatures à 9 mètres, sans dépasser la moitié de leur maximum.'],
            ],
            'Domaine de la Lumière' => [
                [1, 'Sort mineur supplémentaire', 'Vous connaissez le sort mineur Lumière.'],
                [1, 'Illumination protectrice', 'Par une réaction, imposez un désavantage à une attaque d’une créature à 9 mètres qui vous cible. Utilisable un nombre de fois égal à votre modificateur de Sagesse par repos long.'],
                [2, 'Canalisation : Radiance de l’aube', 'Par une action, dissipez les ténèbres magiques à 9 mètres ; chaque créature hostile dans cette zone subit 2d10 + niveau de clerc dégâts radiants (moitié sur une sauvegarde de Constitution réussie).'],
            ],
            'Domaine de la Connaissance' => [
                [1, 'Bénédictions de la connaissance', 'Vous apprenez deux langues et gagnez la maîtrise de deux compétences parmi Arcanes, Histoire, Nature et Religion, avec un bonus de maîtrise doublé.'],
                [2, 'Canalisation : Savoir ancestral', 'Par une action, gagnez pendant 10 minutes la maîtrise d’une compétence ou d’un outil de votre choix.'],
            ],
            'Domaine de la Nature' => [
                [1, 'Acolyte de la nature', 'Vous apprenez un sort mineur de druide et gagnez la maîtrise d’une compétence parmi Dressage, Nature et Survie.'],
                [1, 'Maîtrise supplémentaire', 'Vous gagnez la maîtrise des armures lourdes.'],
                [2, 'Canalisation : Charmer animaux et plantes', 'Par une action, chaque bête ou plante qui vous voit à 9 mètres doit réussir une sauvegarde de Sagesse ou être charmée pendant 1 minute.'],
            ],
            'Domaine de la Tempête' => [
                [1, 'Maîtrises supplémentaires', 'Vous gagnez la maîtrise des armes de guerre et des armures lourdes.'],
                [1, 'Colère de la tempête', 'Par une réaction, lorsqu’une créature à 1,5 mètre vous touche, infligez-lui 2d8 dégâts de foudre ou de tonnerre (moitié sur une sauvegarde de Dextérité réussie).'],
                [2, 'Canalisation : Fureur destructrice', 'Lorsque vous infligez des dégâts de foudre ou de tonnerre, vous pouvez infliger le maximum de dégâts au lieu de lancer les dés.'],
            ],
            'Domaine de la Ruse' => [
                [1, 'Bénédiction de l’escroc', 'Par une action, accordez à une créature consentante l’avantage aux tests de Dextérité (Discrétion) pendant 1 heure.'],
                [2, 'Canalisation : Invocation de réplique', 'Par une action, créez une illusion parfaite de vous-même pendant 1 minute ; vous pouvez lancer des sorts depuis son emplacement et gagnez l’avantage contre une créature proche d’elle.'],
            ],
            'Domaine de la Guerre' => [
                [1, 'Maîtrises supplémentaires', 'Vous gagnez la maîtrise des armes de guerre et des armures lourdes.'],
                [1, 'Prêtre de guerre', 'Après l’action Attaquer, effectuez une attaque d’arme par une action bonus. Utilisable un nombre de fois égal à votre modificateur de Sagesse par repos long.'],
                [2, 'Canalisation : Frappe guidée', 'Après avoir lancé un jet d’attaque, obtenez un bonus de +10 à ce jet.'],
            ],
        ],
        'Roublard' => [
            'Voleur' => [
                [3, 'Mains lestes', 'L’action bonus de Ruse permet aussi de faire un test de Dextérité (Escamotage), d’utiliser des outils de voleur ou d’effectuer l’action Utiliser un objet.'],
                [3, 'Monte-en-l’air', 'Escalader ne coûte plus de déplacement supplémentaire et votre saut avec élan gagne une distance égale à votre modificateur de Dextérité.'],
            ],
            'Assassin' => [
                [3, 'Maîtrises supplémentaires', 'Vous gagnez la maîtrise du kit de déguisement et du kit d’empoisonneur.'],
                [3, 'Assassinat', 'Avantage aux attaques contre toute créature qui n’a pas encore agi pendant le combat ; tout coup contre une créature surprise est un coup critique.'],
            ],
            'Filou Arcanique' => [
                [3, 'Incantation', 'Vous apprenez des sorts de la liste du magicien (principalement enchantement et illusion), lancés avec l’Intelligence.'],
                [3, 'Escamotage à la main de mage', 'Votre Main de mage devient invisible et peut ranger ou prendre un objet dans un contenant porté par une créature, crocheter une serrure ou désamorcer un piège à distance.'],
            ],
        ],
        'Magicien' => [
            'École de l\'Abjuration' => [
                [2, 'Savant en abjuration', 'Le temps et l’or nécessaires pour copier un sort d’abjuration dans votre grimoire sont divisés par deux.'],
                [2, 'Protection arcanique', 'Lorsque vous lancez un sort d’abjuration de niveau 1 ou plus, créez une protection qui absorbe les dégâts, avec 2 × niveau de magicien + modificateur d’Intelligence points de vie.'],
            ],
            'École de la Conjuration' => [
                [2, 'Savant en conjuration', 'Le temps et l’or nécessaires pour copier un sort de conjuration dans votre grimoire sont divisés par deux.'],
                [2, 'Conjuration mineure', 'Par une action, faites apparaître un objet non magique d’au plus 90 cm et 5 kg, qui disparaît après 1 heure.'],
            ],
            'École de la Divination' => [
                [2, 'Savant en divination', 'Le temps et l’or nécessaires pour copier un sort de divination dans votre grimoire sont divisés par deux.'],
                [2, 'Présage', 'Après un repos long, lancez deux d20 et notez les résultats : vous pouvez remplacer n’importe quel jet d’attaque, de sauvegarde ou de caractéristique (le vôtre ou celui d’une créature visible) par l’un d’eux.'],
            ],
            'École de l\'Enchantement' => [
                [2, 'Savant en enchantement', 'Le temps et l’or nécessaires pour copier un sort d’enchantement dans votre grimoire sont divisés par deux.'],
                [2, 'Regard hypnotique', 'Par une action, une créature à 1,5 mètre qui vous voit doit réussir une sauvegarde de Sagesse ou être charmée et neutralisée jusqu’à la fin de votre prochain tour.'],
            ],
            'École de l\'Évocation' => [
                [2, 'Savant en évocation', 'Le temps et l’or nécessaires pour copier un sort d’évocation dans votre grimoire sont divisés par deux.'],
                [2, 'Sculpter les sorts', 'Lors d’un sort d’évocation, choisissez jusqu’à 1 + niveau du sort créatures : elles réussissent automatiquement leur sauvegarde et ne subissent aucun dégât.'],
            ],
            'École de l\'Illusion' => [
                [2, 'Savant en illusion', 'Le temps et l’or nécessaires pour copier un sort d’illusion dans votre grimoire sont divisés par deux.'],
                [2, 'Illusion mineure améliorée', 'Vous connaissez le sort mineur Illusion mineure et pouvez créer à la fois un son et une image avec un seul lancement.'],
            ],
            'École de la Nécromancie' => [
                [2, 'Savant en nécromancie', 'Le temps et l’or nécessaires pour copier un sort de nécromancie dans votre grimoire sont divisés par deux.'],
                [2, 'Sinistre moisson', 'Une fois par tour, lorsqu’un de vos sorts tue une créature, vous récupérez 2 × niveau du sort points de vie (3 × pour un sort de nécromancie).'],
            ],
            'École de la Transmutation' => [
                [2, 'Savant en transmutation', 'Le temps et l’or nécessaires pour copier un sort de transmutation dans votre grimoire sont divisés par deux.'],
                [2, 'Alchimie mineure', 'Transformez temporairement un objet de bois, pierre, fer, cuivre ou argent en un autre de ces matériaux, en y passant 10 minutes par 30 cm cube.'],
            ],
        ],
        'Guerrier' => [
            'Champion' => [
                [3, 'Critique amélioré', 'Vos attaques d’arme obtiennent un coup critique sur un résultat de 19 ou 20.'],
            ],
            'Maître de guerre' => [
                [3, 'Supériorité martiale', 'Vous apprenez trois manœuvres et disposez de quatre dés de supériorité (d8), récupérés après un repos court ou long, pour les alimenter.'],
                [3, 'Élève de la guerre', 'Vous gagnez la maîtrise d’un type d’outils d’artisan de votre choix.'],
            ],
            'Chevalier Eldritch' => [
                [3, 'Incantation', 'Vous apprenez des sorts de la liste du magicien (principalement abjuration et évocation), lancés avec l’Intelligence.'],
                [3, 'Lien avec une arme', 'Liez-vous à jusqu’à deux armes : vous ne pouvez pas être désarmé et pouvez rappeler une arme liée dans votre main par une action bonus.'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::CLASSES as $nomClasse => $capacites) {
            $classe = Classe::where('nom', $nomClasse)->first();
            if (! $classe) {
                continue;
            }

            foreach ($capacites as [$niveau, $nom, $description]) {
                $this->enregistrer($classe->id, null, $niveau, $nom, $description);
            }

            foreach (self::SOUS_CLASSES[$nomClasse] ?? [] as $nomSousClasse => $capacitesSousClasse) {
                $sousClasse = SousClasse::where('classe_id', $classe->id)->where('nom', $nomSousClasse)->first();
                if (! $sousClasse) {
                    continue;
                }

                foreach ($capacitesSousClasse as [$niveau, $nom, $description]) {
                    $this->enregistrer($classe->id, $sousClasse->id, $niveau, $nom, $description);
                }
            }
        }

        // Rattrape les personnages créés avant l'existence de la table
        // (leurs capacités étaient une simple liste de noms).
        $service = app(CapaciteService::class);
        Personnage::query()->each(fn (Personnage $personnage) => $service->synchroniser($personnage));
    }

    private function enregistrer(int $classeId, ?int $sousClasseId, int $niveau, string $nom, string $description): void
    {
        Capacite::updateOrCreate(
            ['classe_id' => $classeId, 'sous_classe_id' => $sousClasseId, 'nom' => $nom],
            ['niveau' => $niveau, 'description' => $description],
        );
    }
}
