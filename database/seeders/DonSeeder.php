<?php

namespace Database\Seeders;

use App\Models\Don;
use Illuminate\Database\Seeder;

class DonSeeder extends Seeder
{
    /**
     * Dons PHB 2014 proposés à la montée de niveau. Seuls `stat`,
     * `choix_stat` et `effets` sont appliqués automatiquement à la fiche ;
     * le reste de la description est conservé comme effet passif.
     */
    public function run(): void
    {
        $toutes = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];

        $dons = [
            [
                'nom' => 'Alerte',
                'resume' => 'Améliore fortement l’initiative et la vigilance.',
                'description' => 'Toujours à l’affût du danger : +5 aux jets d’initiative, impossible d’être surpris tant que vous êtes conscient, et les créatures cachées n’obtiennent pas l’avantage à leurs jets d’attaque contre vous.',
                'effets' => ['initiative' => 5],
            ],
            [
                'nom' => 'Athlète',
                'resume' => 'Améliore la mobilité et augmente Force ou Dextérité de 1.',
                'description' => 'Augmente la Force ou la Dextérité de 1 (maximum 20). Se relever ne coûte que 1,5 mètre de déplacement, escalader ne coûte plus de déplacement supplémentaire, et un saut avec élan ne demande que 1,5 mètre de course.',
                'choix_stat' => ['force', 'dexterite'],
            ],
            [
                'nom' => 'Acteur',
                'resume' => 'Augmente Charisme de 1 et améliore l’imitation et le déguisement.',
                'description' => 'Augmente le Charisme de 1 (maximum 20). Avantage aux tests de Charisme (Tromperie et Représentation) pour se faire passer pour quelqu’un d’autre, et possibilité d’imiter la voix d’une personne ou le cri d’une créature après l’avoir entendu au moins une minute.',
                'stat' => 'charisme',
            ],
            [
                'nom' => 'Résilient',
                'resume' => 'Augmente une caractéristique de 1 et donne la maîtrise de ses jets de sauvegarde.',
                'description' => 'Choisissez une caractéristique : elle augmente de 1 (maximum 20) et vous gagnez la maîtrise des jets de sauvegarde qui l’utilisent.',
                'choix_stat' => $toutes,
            ],
            [
                'nom' => 'Robuste',
                'resume' => 'Augmente le maximum de points de vie de 2 par niveau.',
                'description' => 'Le maximum de points de vie augmente d’un montant égal à deux fois votre niveau au moment d’obtenir ce don, puis de 2 à chaque niveau supplémentaire.',
                'effets' => ['pv_par_niveau' => 2],
            ],
            [
                'nom' => 'Durable',
                'resume' => 'Augmente Constitution de 1 et améliore la récupération avec les dés de vie.',
                'description' => 'Augmente la Constitution de 1 (maximum 20). Lorsque vous lancez un dé de vie pour récupérer des points de vie, le minimum récupéré est égal à deux fois votre modificateur de Constitution (minimum 2).',
                'stat' => 'constitution',
            ],
            [
                'nom' => 'Observateur',
                'resume' => 'Augmente Intelligence ou Sagesse de 1 et améliore l’observation.',
                'description' => 'Augmente l’Intelligence ou la Sagesse de 1 (maximum 20). Permet de lire sur les lèvres d’une créature dont vous connaissez la langue, et donne +5 à la Sagesse (Perception) passive et à l’Intelligence (Investigation) passive.',
                'choix_stat' => ['intelligence', 'sagesse'],
            ],
            [
                'nom' => 'Maître des armures lourdes',
                'resume' => 'Augmente Force de 1 et réduit certains dégâts physiques en armure lourde.',
                'description' => 'Prérequis : maîtrise des armures lourdes. Augmente la Force de 1 (maximum 20). En armure lourde, les dégâts contondants, perforants et tranchants d’armes non magiques que vous subissez sont réduits de 3.',
                'stat' => 'force',
            ],
            [
                'nom' => 'Maître des armures intermédiaires',
                'resume' => 'Augmente Force ou Dextérité de 1 et améliore l’usage des armures intermédiaires.',
                'description' => 'Prérequis : maîtrise des armures intermédiaires. Augmente la Force ou la Dextérité de 1 (maximum 20). Porter une armure intermédiaire n’impose plus de désavantage aux tests de Dextérité (Discrétion), et le bonus de Dextérité à la CA peut aller jusqu’à +3.',
                'choix_stat' => ['force', 'dexterite'],
            ],
            [
                'nom' => 'Maître des armes',
                'resume' => 'Augmente Force ou Dextérité de 1 et accorde des maîtrises d’armes.',
                'description' => 'Augmente la Force ou la Dextérité de 1 (maximum 20) et accorde la maîtrise de quatre armes, courantes ou de guerre, de votre choix.',
                'choix_stat' => ['force', 'dexterite'],
            ],
            [
                'nom' => 'Maître des armes à deux mains',
                'resume' => 'Améliore l’efficacité avec les armes lourdes à deux mains.',
                'description' => 'Après un coup critique ou avoir réduit une créature à 0 PV avec une arme de corps à corps, vous pouvez effectuer une attaque d’arme par une action bonus. Avec une arme lourde, vous pouvez prendre -5 au jet d’attaque pour infliger +10 dégâts.',
            ],
            [
                'nom' => 'Maître du bouclier',
                'resume' => 'Ajoute des options défensives et offensives avec un bouclier.',
                'description' => 'Après l’action Attaquer, vous pouvez bousculer une créature avec votre bouclier par une action bonus. Le bonus de CA du bouclier s’ajoute aux jets de sauvegarde de Dextérité contre un effet ciblant uniquement vous, et une réussite à un tel jet annule tous les dégâts (réaction).',
            ],
            [
                'nom' => 'Duelliste défensif',
                'resume' => 'Permet d’utiliser sa réaction pour renforcer sa CA avec une arme de finesse.',
                'description' => 'Prérequis : Dextérité 13 ou plus. En maniant une arme de finesse que vous maîtrisez, vous pouvez utiliser votre réaction lorsqu’une attaque au corps à corps vous touche pour ajouter votre bonus de maîtrise à votre CA contre cette attaque.',
            ],
            [
                'nom' => 'Mobile',
                'resume' => 'Augmente la vitesse et facilite les déplacements au corps à corps.',
                'description' => 'La vitesse augmente de 3 mètres. Sprinter sur un terrain difficile ne coûte pas de déplacement supplémentaire, et une créature que vous attaquez au corps à corps ne peut pas vous porter d’attaque d’opportunité pendant ce tour.',
                'effets' => ['vitesse' => 10],
            ],
            [
                'nom' => 'Chanceux',
                'resume' => 'Accorde trois points de chance après un repos long.',
                'description' => 'Vous disposez de 3 points de chance, récupérés après un repos long. Dépensez-en un pour lancer un d20 supplémentaire lors d’une attaque, d’un test ou d’une sauvegarde (ou d’une attaque contre vous) et choisir le résultat conservé.',
            ],
            [
                'nom' => 'Initié à la magie',
                'resume' => 'Accorde une petite initiation à la magie d’une autre classe.',
                'description' => 'Choisissez une classe (barde, clerc, druide, ensorceleur, magicien ou occultiste) : vous apprenez deux sorts mineurs de sa liste, ainsi qu’un sort de niveau 1 que vous pouvez lancer une fois par repos long.',
            ],
            [
                'nom' => 'Lanceur de sorts de guerre',
                'resume' => 'Aide à maintenir la concentration et à lancer des sorts au combat.',
                'description' => 'Prérequis : pouvoir lancer au moins un sort. Avantage aux sauvegardes de Constitution pour maintenir la concentration, composantes somatiques possibles les mains occupées, et possibilité de lancer un sort au lieu d’une attaque d’opportunité.',
            ],
            [
                'nom' => 'Tireur d’élite',
                'resume' => 'Améliore les attaques à distance à longue portée et contre le couvert.',
                'description' => 'Aucun désavantage à longue portée, les abris partiels et importants sont ignorés, et vous pouvez prendre -5 au jet d’attaque d’une arme à distance que vous maîtrisez pour infliger +10 dégâts.',
            ],
            [
                'nom' => 'Bagarreur de tavernes',
                'resume' => 'Augmente Force ou Constitution de 1 et améliore le combat improvisé.',
                'description' => 'Augmente la Force ou la Constitution de 1 (maximum 20). Maîtrise des armes improvisées, les attaques à mains nues infligent 1d4, et après avoir touché une créature à mains nues ou avec une arme improvisée, vous pouvez tenter de l’agripper par une action bonus.',
                'choix_stat' => ['force', 'constitution'],
            ],
        ];

        foreach ($dons as $don) {
            Don::updateOrCreate(
                ['nom' => $don['nom']],
                [
                    'resume' => $don['resume'],
                    'description' => $don['description'],
                    'stat' => $don['stat'] ?? null,
                    'choix_stat' => $don['choix_stat'] ?? null,
                    'effets' => $don['effets'] ?? null,
                ],
            );
        }
    }
}
