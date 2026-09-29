<?php

namespace Database\Seeders;

use App\Models\Attachement;
use App\Models\Defaut;
use App\Models\Historique;
use App\Models\Ideal;
use App\Models\Personnalite;
use Illuminate\Database\Seeder;

class HistoriquesSeeder extends Seeder
{
    public function run(): void
    {
        $historiques = [
            [
                'nom' => 'Acolyte',
                'description' => 'Vous avez passé votre vie au service d\'un temple, priant et étudiant les textes sacrés.',
                'or_depart' => 15,
                'competences' => ['Intuition', 'Religion'],
                'langues' => ['Deux langues au choix'],
                'equipement_depart' => ['Symbole sacré', 'Livre de prières', 'Bâtons d\'encens (5)', 'Vêtements de cérémonie'],
                'outils_fixes' => [],
                'outils_disponibles' => [],
                'ideaux' => [
                    ['valeur' => 'Tradition', 'alignement' => 'Loyal Neutre', 'description' => 'Les coutumes anciennes de mon culte doivent être préservées.'],
                    ['valeur' => 'Charité', 'alignement' => 'Neutre Bon', 'description' => 'Je fais toujours preuve de compassion et de générosité envers les nécessiteux.'],
                    ['valeur' => 'Changement', 'alignement' => 'Chaotique Neutre', 'description' => 'Chaque nouvelle journée doit apporter son lot de transformations spirituelles.'],
                    ['valeur' => 'Pouvoir', 'alignement' => 'Neutre', 'description' => 'Je cherche à m\'élever au sommet de la hiérarchie de ma foi.'],
                    ['valeur' => 'Foi', 'alignement' => 'Loyal Neutre', 'description' => 'J\'ai une confiance absolue en la volonté de mon dieu.'],
                    ['valeur' => 'Aspiration', 'alignement' => 'Neutre', 'description' => 'Je veux me montrer digne de la grâce divine.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je juge sévèrement les autres, moi le premier.'],
                    ['valeur' => 'Je fais aveuglément confiance aux figures d\'autorité de ma foi.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Mon piété m\'aveugle face aux faiblesses des mortels.'],
                    ['valeur' => 'Je suis inflexible dans mes convictions religieuses.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Je suis hanté par la peur de décevoir ma divinité.'],
                    ['valeur' => 'Je vois de l\'hérésie dans le moindre désaccord.', 'alignement' => 'Loyal'],
                ],
                'attachements' => [
                    ['valeur' => 'Je protégerais de ma vie les autres acolytes de mon temple.', 'alignement' => 'Bon'],
                    ['valeur' => 'Je cherche à retrouver une relique sacrée dérobée à mon ordre.'],
                    ['valeur' => 'Mon temple d\'origine est le centre absolu de mon existence.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Je dois tout au prêtre qui m\'a recueilli enfant.', 'alignement' => 'Bon'],
                    ['valeur' => 'Je cherche à convertir le monde entier à ma foi.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Un écrit sacré contient une prophétie qui me concerne directement.'],
                ],
                'personnalites' => [
                    ['valeur' => 'Je cite (souvent mal) des textes sacrés dans presque toutes les situations.'],
                    ['valeur' => 'Je vois des signes et des présages divins dans les événements du quotidien.'],
                    ['valeur' => 'Je suis particulièrement tolérant envers les autres confessions.', 'alignement' => 'Bon'],
                    ['valeur' => 'Rien ne peut ébranler mon calme et mon optimisme religieux.', 'alignement' => 'Bon'],
                    ['valeur' => 'J\'ai passé tellement de temps au temple que j\'ignore les usages du monde extérieur.'],
                    ['valeur' => 'Je demande la bénédiction de mon dieu avant la moindre action d\'importance.', 'alignement' => 'Loyal'],
                ],
            ],
            [
                'nom' => 'Artiste',
                'description' => 'Vous vous épanouissez devant un public, maîtrisant l\'art de la comédie, de la musique ou de la danse.',
                'or_depart' => 15,
                'competences' => ['Acrobaties', 'Performance'],
                'langues' => [],
                'equipement_depart' => ['Instrument de musique', 'Faveur d\'un admirateur', 'Costume de scène'],
                'outils_fixes' => [],
                'outils_disponibles' => ['Luth', 'Flûte', 'Tambour', 'Cor'],
                'ideaux' => [
                    ['valeur' => 'Beauté', 'alignement' => 'Neutre Bon', 'description' => 'L\'art élève l\'âme et rend le monde plus beau.'],
                    ['valeur' => 'Liberté', 'alignement' => 'Chaotique Neutre', 'description' => 'L\'expression artistique ne doit subir aucune censure.'],
                    ['valeur' => 'Renommée', 'alignement' => 'Neutre', 'description' => 'Mon nom doit traverser les époques.'],
                    ['valeur' => 'Tradition', 'alignement' => 'Loyal Neutre', 'description' => 'Les grands classiques de la comédie et de la musique doivent être transmis intacts.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je ne résiste pas aux compliments, qu\'ils soient sincères ou manipulateurs.'],
                    ['valeur' => 'Je suis incapable de garder un secret si cela fait une bonne histoire à raconter.'],
                    ['valeur' => 'Mon ego surdimensionné me rend irritable lorsqu\'on ne remarque pas mon talent.'],
                ],
                'attachements' => [
                    ['valeur' => 'Mon instrument de musique est mon bien le plus précieux.'],
                    ['valeur' => 'Je ferai tout pour sauver la troupe itinérante qui m\'a formé.', 'alignement' => 'Bon'],
                    ['valeur' => 'Je cherche à conquérir le cœur de mon admirateur le plus fervent.'],
                ],
                'personnalites' => [
                    ['valeur' => 'J\'adore être le centre absolu de l\'attention.'],
                    ['valeur' => 'Je transforme le moindre événement de ma vie en un drame théâtral.'],
                    ['valeur' => 'Je connais une histoire ou une chanson pour chaque occasion.'],
                ],
            ],
            [
                'nom' => 'Artisan de guilde',
                'description' => 'Membre d\'une guilde marchande ou artisanale, vous maîtrisez un métier manuel précieux.',
                'or_depart' => 15,
                'competences' => ['Intuition', 'Persuasion'],
                'langues' => ['Une langue au choix'],
                'equipement_depart' => ['Outils d\'artisan', 'Lettre d\'introduction de la guilde', 'Vêtements de voyage'],
                'outils_fixes' => [],
                'outils_disponibles' => ['Kit de calligraphe', 'Kit de charpentier', 'Kit de cuisinier', 'Kit de potier'],
                'ideaux' => [
                    ['valeur' => 'Communauté', 'alignement' => 'Loyal Bon', 'description' => 'Il faut soutenir sa guilde et ses confrères pour prospérer.'],
                    ['valeur' => 'Générosité', 'alignement' => 'Neutre Bon', 'description' => 'Mes compétences doivent servir à aider ceux qui sont dans le besoin.'],
                    ['valeur' => 'Aspiration', 'alignement' => 'Neutre', 'description' => 'Je veux devenir le maître incontesté de mon art.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je ne travaille jamais gratuitement et j\'évalue tout en pièces d\'or.', 'alignement' => 'Mauvais'],
                    ['valeur' => 'Je suis obsédé par la compétition avec les guildes rivales.'],
                    ['valeur' => 'Je suis prêt à couper les ponts avec quiconque critique mon travail.'],
                ],
                'attachements' => [
                    ['valeur' => 'La guilde est ma véritable famille et je la défendrai à tout prix.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Je cherche à créer un chef-d\'œuvre ultime qui surpassera les anciens maîtres.'],
                    ['valeur' => 'Mon atelier d\'origine a une valeur sentimentale inestimable.'],
                ],
                'personnalites' => [
                    ['valeur' => 'Je perfectionne mon travail avec un souci du détail obsessionnel.'],
                    ['valeur' => 'Je compare toujours les objets fabriqués aux standards rigoureux de ma guilde.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Je suis très fier de mon appartenance à la guilde et j\'arbore son insigne.', 'alignement' => 'Loyal'],
                ],
            ],
            [
                'nom' => 'Charlatan',
                'description' => 'Vous connaissez les faiblesses humaines et utilisez l\'illusion, l\'imposture et le charme pour en tirer profit.',
                'or_depart' => 15,
                'competences' => ['Art de la duplicité', 'Escamotage'],
                'langues' => [],
                'equipement_depart' => ['Vêtements fins', 'Matériel de déguisement', 'Accessoires d\'escroquerie (dés pipés ou fausses cartes)'],
                'outils_fixes' => ['Kit de déguisement', 'Kit de faussaire'],
                'outils_disponibles' => [],
                'ideaux' => [
                    ['valeur' => 'Indépendance', 'alignement' => 'Chaotique Neutre', 'description' => 'Personne ne me dicte mes choix ou ma ligne de conduite.'],
                    ['valeur' => 'Équité', 'alignement' => 'Chaotique Bon', 'description' => 'Je ne vole que les riches qui ont accumulé leur fortune injustement.'],
                    ['valeur' => 'Charme', 'alignement' => 'Neutre', 'description' => 'La vie est un jeu de séduction et de ruse.'],
                ],
                'defauts' => [
                    ['valeur' => 'J\'ai du mal à résister à une opportunité d\'escroquer un riche arrogant.', 'alignement' => 'Chaotique'],
                    ['valeur' => 'Je mens d\'instinct, même quand dire la vérité serait plus simple.', 'alignement' => 'Chaotique'],
                    ['valeur' => 'Je fuis au moindre signe de danger sérieux.'],
                ],
                'attachements' => [
                    ['valeur' => 'Je dois une fière chandelle au mentor qui m\'a appris les ficelles du métier.', 'alignement' => 'Bon'],
                    ['valeur' => 'Je garde une fausse identité secrète pour me réfugier si les choses tournent mal.', 'alignement' => 'Chaotique'],
                    ['valeur' => 'Je cherche à récupérer un bien volé par un arnaqueur plus fort que moi.'],
                ],
                'personnalites' => [
                    ['valeur' => 'Je tombe amoureux trop facilement et change d\'intérêt romantique dans chaque ville.'],
                    ['valeur' => 'J\'ai une réplique pleine d\'esprit ou un mensonge convaincant pour chaque situation.', 'alignement' => 'Chaotique'],
                    ['valeur' => 'Je compliments abondamment les gens pour m\'attirer leurs faveurs.'],
                ],
            ],
            [
                'nom' => 'Criminel',
                'description' => 'Vous êtes un criminel expérimenté avec un passé d\'infractions à la loi.',
                'or_depart' => 15,
                'competences' => ['Discrétion', 'Escamotage'],
                'langues' => [],
                'equipement_depart' => ['Pied-de-biche', 'Vêtements sombres et communs', 'Capuche'],
                'outils_fixes' => [],
                'outils_disponibles' => ['Jeu de dés', 'Jeu de cartes'],
                'ideaux' => [
                    ['valeur' => 'Honneur', 'alignement' => 'Loyal Neutre', 'description' => 'Je respecte un code entre voleurs.'],
                    ['valeur' => 'Liberté', 'alignement' => 'Chaotique Neutre', 'description' => 'Les chaînes sont faites pour être brisées.'],
                ],
                'defauts' => [
                    ['valeur' => 'Une fois que quelqu\'un me montre de la gentillesse, j\'ai du mal à croire que c\'est sincère.'],
                    ['valeur' => 'Je ne peux résister à m\'approprier un objet de valeur laissé sans surveillance.', 'alignement' => 'Chaotique'],
                ],
                'attachements' => [
                    ['valeur' => 'Je ferais n\'importe quoi pour protéger un membre de mon ancien gang.', 'alignement' => 'Bon'],
                    ['valeur' => 'Je rembourse toujours mes dettes, même si cela demande des méthodes illégales.', 'alignement' => 'Loyal'],
                ],
                'personnalites' => [
                    ['valeur' => 'Je garde toujours un plan de secours en tête.'],
                    ['valeur' => 'La plus infime insulte se termine en violence.', 'alignement' => 'Mauvais'],
                ],
            ],
            [
                'nom' => 'Ermite',
                'description' => 'Vous avez vécu en isolement, pour la contemplation, la guérison ou la fuite.',
                'or_depart' => 5,
                'competences' => ['Médecine', 'Religion'],
                'langues' => ['Une langue au choix'],
                'equipement_depart' => ['Manuscrit de vos révélations', 'Fioles de vin ou d\'huile sacrée', 'Herbes médicinales'],
                'outils_fixes' => ['Kit d\'herboriste'],
                'outils_disponibles' => [],
                'ideaux' => [
                    ['valeur' => 'Bien suprême', 'alignement' => 'Neutre Bon', 'description' => 'Ma vie de recherche sert le bien de tous.'],
                    ['valeur' => 'Auto-suffisance', 'alignement' => 'Chaotique Neutre', 'description' => 'Je ne compte que sur moi-même.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je juge sévèrement mes semblables et moi-même, sans nuance.'],
                    ['valeur' => 'Je néglige ma propre sécurité en poursuivant mes connaissances.'],
                ],
                'attachements' => [
                    ['valeur' => 'Je protégerais les élèves qui ont étudié sous ma tutelle.', 'alignement' => 'Bon'],
                    ['valeur' => 'Ma solitude m\'a permis de percevoir une vérité troublante que je dois révéler.'],
                ],
                'personnalites' => [
                    ['valeur' => 'Je répète des mots à voix basse en travaillant sur un problème.'],
                    ['valeur' => 'Je suis mal à l\'aise autour de plus de deux personnes à la fois.'],
                ],
            ],
            [
                'nom' => 'Gamin des rues',
                'description' => 'Vous avez grandi seul dans les ruelles misérables d\'une grande cité, luttant pour chaque repas.',
                'or_depart' => 10,
                'competences' => ['Discrétion', 'Escamotage'],
                'langues' => [],
                'equipement_depart' => ['Un petit couteau', 'Carte de votre ville natale', 'Un objet souvenir de votre enfance'],
                'outils_fixes' => ['Kit de déguisement', 'Outils de voleur'],
                'outils_disponibles' => [],
                'ideaux' => [
                    ['valeur' => 'Respect', 'alignement' => 'Neutre Bon', 'description' => 'Tous les individus méritent de vivre dans la dignité.'],
                    ['valeur' => 'Survie', 'alignement' => 'Neutre', 'description' => 'Chacun doit d\'abord s\'assurer de survivre.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je cache de la nourriture dans mes poches au cas où je manquerais.'],
                    ['valeur' => 'Je me méfie d\'instinct de toute personne vêtue de riches habits.'],
                ],
                'attachements' => [
                    ['valeur' => 'Je veillerai toujours sur les orphelins des rues.', 'alignement' => 'Bon'],
                    ['valeur' => 'L\'orphelinat qui m\'a abrité est l\'endroit que je veux protéger.', 'alignement' => 'Bon'],
                ],
                'personnalites' => [
                    ['valeur' => 'Je dors toujours le dos contre un mur.'],
                    ['valeur' => 'Je parle très rapidement quand je suis nerveux.'],
                ],
            ],
            [
                'nom' => 'Héros du peuple',
                'description' => 'Issu du peuple humble, vous vous êtes dressé contre un tyran ou un monstre et êtes devenu un symbole d\'espoir.',
                'or_depart' => 10,
                'competences' => ['Dressage', 'Survie'],
                'langues' => [],
                'equipement_depart' => ['Outils d\'artisan', 'Pelle', 'Vêtements communs'],
                'outils_fixes' => [],
                'outils_disponibles' => ['Kit de calligraphe', 'Kit de charpentier', 'Kit de cuisinier', 'Kit de potier'],
                'ideaux' => [
                    ['valeur' => 'Justice', 'alignement' => 'Loyal Bon', 'description' => 'Personne n\'est au-dessus des lois, pas même les rois.'],
                    ['valeur' => 'Destin', 'alignement' => 'Neutre', 'description' => 'Rien ne peut m\'empêcher d\'accomplir ma destinée.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je suis convaincu que mon destin me protégera de tout danger.'],
                    ['valeur' => 'Mon attachement à mon village m\'empêche de voir le monde plus largement.'],
                ],
                'attachements' => [
                    ['valeur' => 'Mon village natal sera toujours ma priorité.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Je protège les opprimés où que je me trouve.', 'alignement' => 'Bon'],
                ],
                'personnalites' => [
                    ['valeur' => 'Je suis toujours prêt à aider un fermier ou un artisan en difficulté.', 'alignement' => 'Bon'],
                    ['valeur' => 'Je reste humble face aux éloges.', 'alignement' => 'Bon'],
                ],
            ],
            [
                'nom' => 'Marin',
                'description' => 'Vous avez navigué sur les mers et bravé les tempêtes à bord d\'un navire marchand ou de guerre.',
                'or_depart' => 10,
                'competences' => ['Athlétisme', 'Perception'],
                'langues' => [],
                'equipement_depart' => ['Corde de chanvre (15m)', 'Porte-bonheur de marin', 'Vêtements de mer'],
                'outils_fixes' => ['Outils de navigateur'],
                'outils_disponibles' => [],
                'ideaux' => [
                    ['valeur' => 'Camaraderie', 'alignement' => 'Neutre Bon', 'description' => 'L\'équipage est ma famille, nous veillons les uns sur les autres.'],
                    ['valeur' => 'Liberté', 'alignement' => 'Chaotique Neutre', 'description' => 'L\'océan appartient à ceux qui osent s\'y aventurer.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je ne refuse jamais un verre ou une bagarre dans une taverne.'],
                    ['valeur' => 'J\'ai une superstition absurde liée au temps et à la mer.'],
                ],
                'attachements' => [
                    ['valeur' => 'Mon navire est ma maison et mon capitaine mon guide.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Je cherche à venger le naufrage de mon ancien bâtiment.'],
                ],
                'personnalites' => [
                    ['valeur' => 'J\'utilise constamment des termes de navigation.'],
                    ['valeur' => 'Je n\'ai pas peur de la bagarre et j\'aime les histoires de taverne.'],
                ],
            ],
            [
                'nom' => 'Noble',
                'description' => 'Vous comprenez la richesse, le pouvoir et le prestige.',
                'or_depart' => 25,
                'competences' => ['Histoire', 'Persuasion'],
                'langues' => ['Une langue au choix'],
                'equipement_depart' => ['Vêtements fins', 'Anneau ou sceau familial', 'Manuscrit de généalogie'],
                'outils_fixes' => [],
                'outils_disponibles' => ['Jeu de dés', 'Jeu de cartes'],
                'ideaux' => [
                    ['valeur' => 'Devoir', 'alignement' => 'Loyal Neutre', 'description' => 'Je dois honorer mon titre et ma famille.'],
                    ['valeur' => 'Ambition', 'alignement' => 'Neutre', 'description' => 'Je cherche à obtenir plus de pouvoir.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je pense que la plupart des gens sont trop stupides pour reconnaître leur propre intérêt.', 'alignement' => 'Mauvais'],
                    ['valeur' => 'Mon orgueil familial me fera dire des choses regrettables.'],
                ],
                'attachements' => [
                    ['valeur' => 'Je ferai tout ce qui est nécessaire pour protéger la réputation de ma famille.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Je dois beaucoup à mon bienfaiteur qui a payé mon éducation.', 'alignement' => 'Bon'],
                ],
                'personnalites' => [
                    ['valeur' => 'Mes manières distinguées me suivent même dans les pires circonstances.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Je remarque toujours immédiatement le vêtement le plus fin d\'une pièce.'],
                ],
            ],
            [
                'nom' => 'Sage',
                'description' => 'Vous avez consacré des années à l\'étude des manuscrits, des arcanes et des secrets du savoir.',
                'or_depart' => 10,
                'competences' => ['Arcanes', 'Histoire'],
                'langues' => ['Deux langues au choix'],
                'equipement_depart' => ['Encre et plume', 'Petit couteau d\'étude', 'Lettre d\'un collègue érudit'],
                'outils_fixes' => [],
                'outils_disponibles' => [],
                'ideaux' => [
                    ['valeur' => 'Savoir', 'alignement' => 'Neutre', 'description' => 'La connaissance est la clé du progrès.'],
                    ['valeur' => 'Logique', 'alignement' => 'Loyal Neutre', 'description' => 'Les émotions ne doivent pas obscurcir la raison.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je m\'exprime souvent avec un jargon incompréhensible pour le commun des mortels.'],
                    ['valeur' => 'Je suis prêt à prendre des risques inconsidérés pour acquérir un livre rare.', 'alignement' => 'Chaotique'],
                ],
                'attachements' => [
                    ['valeur' => 'L\'université ou la bibliothèque où j\'ai étudié est mon temple.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Je cherche la réponse à une énigme intellectuelle majeure.'],
                ],
                'personnalites' => [
                    ['valeur' => 'J\'utilise de grands mots pour paraître plus érudit.'],
                    ['valeur' => 'Je me perds facilement dans mes pensées et mes théories.'],
                ],
            ],
            [
                'nom' => 'Sauvage',
                'description' => 'Élevé loin de la civilisation dans les grands espaces sauvages, vous connaissez la nature brute.',
                'or_depart' => 10,
                'competences' => ['Athlétisme', 'Survie'],
                'langues' => ['Une langue au choix'],
                'equipement_depart' => ['Bâton de marche', 'Piège à mâchoires', 'Trophée d\'animal chassé'],
                'outils_fixes' => [],
                'outils_disponibles' => ['Luth', 'Flûte', 'Tambour', 'Cor'],
                'ideaux' => [
                    ['valeur' => 'Nature', 'alignement' => 'Neutre', 'description' => 'Le monde sauvage doit être préservé de l\'expansion urbaine.'],
                    ['valeur' => 'Gloire', 'alignement' => 'Chaotique Neutre', 'description' => 'Je vis pour relever de grands défis physiques.'],
                ],
                'defauts' => [
                    ['valeur' => 'Je comprends mal les codes sociaux de la haute société.'],
                    ['valeur' => 'Je fais confiance aux animaux bien plus qu\'aux humanoïdes.'],
                ],
                'attachements' => [
                    ['valeur' => 'Ma tribu est ma fierté et ma responsabilité.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Un arbre sacré ou un lieu naturel est sous ma protection.', 'alignement' => 'Bon'],
                ],
                'personnalites' => [
                    ['valeur' => 'Je suis toujours en alerte face aux dangers environnants.'],
                    ['valeur' => 'La vie urbaine m\'étouffe et me rend irritable.', 'alignement' => 'Chaotique'],
                ],
            ],
            [
                'nom' => 'Soldat',
                'description' => 'La guerre a été votre vie depuis que vous avez pu porter une arme.',
                'or_depart' => 10,
                'competences' => ['Athlétisme', 'Intimidation'],
                'langues' => [],
                'equipement_depart' => ['Insigne de rang', 'Trophée de guerre', 'Dés à jouer', 'Uniforme'],
                'outils_fixes' => [],
                'outils_disponibles' => ['Jeu de dés', 'Jeu de cartes'],
                'ideaux' => [
                    ['valeur' => 'Grandeur', 'alignement' => 'Loyal Neutre', 'description' => 'Mon pays passe avant tout.'],
                    ['valeur' => 'Vivre et laisser vivre', 'alignement' => 'Neutre Bon', 'description' => 'Les rivalités me poussent à devenir plus fort, pas à haïr.'],
                ],
                'defauts' => [
                    ['valeur' => 'J\'ai du mal à faire confiance à mes propres alliés.'],
                    ['valeur' => 'Je me vante trop facilement de mes exploits militaires.'],
                ],
                'attachements' => [
                    ['valeur' => 'Je ferais n\'importe quoi pour protéger les soldats sous mes ordres.', 'alignement' => 'Loyal'],
                    ['valeur' => 'Quelqu\'un que j\'admirais durant mon service est mort, et je cherche à venger sa mort.'],
                ],
                'personnalites' => [
                    ['valeur' => 'Je peux estimer avec précision une distance, un poids ou une quantité.'],
                    ['valeur' => 'Je m\'exprime en termes militaires, même dans une conversation banale.'],
                ],
            ],
        ];

        foreach ($historiques as $donnees) {
            $ideaux = $donnees['ideaux'];
            $defauts = $donnees['defauts'];
            $attachements = $donnees['attachements'];
            $personnalites = $donnees['personnalites'];
            unset($donnees['ideaux'], $donnees['defauts'], $donnees['attachements'], $donnees['personnalites']);

            $historique = Historique::create($donnees);

            foreach ($ideaux as $ideal) {
                Ideal::create(['historique_id' => $historique->id, ...$ideal]);
            }

            foreach ($defauts as $defaut) {
                Defaut::create(['historique_id' => $historique->id, ...$defaut]);
            }

            foreach ($attachements as $attachement) {
                Attachement::create(['historique_id' => $historique->id, ...$attachement]);
            }

            foreach ($personnalites as $personnalite) {
                Personnalite::create(['historique_id' => $historique->id, ...$personnalite]);
            }
        }
    }
}
