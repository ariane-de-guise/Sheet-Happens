<?php

namespace Tests\Unit;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\ListeCompetence;
use App\Models\Personnage;
use App\Models\Race;
use App\Models\SousRace;
use App\Services\FicheDePersonnageService;
use Tests\TestCase;

class FicheDePersonnageServiceTest extends TestCase
{
    private function service(): FicheDePersonnageService
    {
        return new FicheDePersonnageService;
    }

    /**
     * Construit un Personnage en mémoire (aucune écriture en base) avec ses
     * relations attachées via setRelation, pour tester la logique de calcul
     * du service de façon isolée et rapide.
     */
    private function personnage(array $attributs = [], array $relations = []): Personnage
    {
        $personnage = new Personnage(array_merge([
            'nom' => 'Test',
            'niveau' => 1,
            'equipement' => [],
        ], $attributs));

        $abilites = $relations['abilites'] ?? new Abilite([
            'force' => 10, 'dexterite' => 10, 'constitution' => 10,
            'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10,
        ]);
        $personnage->setRelation('abilites', $abilites);

        $personnage->setRelation('classe', $relations['classe'] ?? new Classe(['nom' => 'Guerrier']));
        $personnage->setRelation('race', $relations['race'] ?? null);
        $personnage->setRelation('sousRace', $relations['sousRace'] ?? null);
        $personnage->setRelation('sousClasse', $relations['sousClasse'] ?? null);
        $personnage->setRelation('listeCompetence', $relations['listeCompetence'] ?? new ListeCompetence(['competences' => []]));

        return $personnage;
    }

    public function test_modificateur_suit_la_table_5e(): void
    {
        $service = $this->service();

        $this->assertSame(-5, $service->modificateur(1));
        $this->assertSame(-1, $service->modificateur(8));
        $this->assertSame(-1, $service->modificateur(9));
        $this->assertSame(0, $service->modificateur(10));
        $this->assertSame(0, $service->modificateur(11));
        $this->assertSame(2, $service->modificateur(15));
        $this->assertSame(5, $service->modificateur(20));
    }

    public function test_modificateur_formate_ajoute_le_signe_plus(): void
    {
        $service = $this->service();

        $this->assertSame('+2', $service->modificateurFormate(15));
        $this->assertSame('+0', $service->modificateurFormate(10));
        $this->assertSame('-1', $service->modificateurFormate(8));
    }

    public function test_formater_bonus_gere_positif_negatif_et_zero(): void
    {
        $service = $this->service();

        $this->assertSame('+3', $service->formaterBonus(3));
        $this->assertSame('+0', $service->formaterBonus(0));
        $this->assertSame('-2', $service->formaterBonus(-2));
    }

    public function test_bonus_de_maitrise_suit_les_paliers_de_niveau(): void
    {
        $service = $this->service();

        $this->assertSame(2, $service->bonusMaitrise(1));
        $this->assertSame(2, $service->bonusMaitrise(4));
        $this->assertSame(3, $service->bonusMaitrise(5));
        $this->assertSame(3, $service->bonusMaitrise(8));
        $this->assertSame(4, $service->bonusMaitrise(9));
        $this->assertSame(4, $service->bonusMaitrise(12));
        $this->assertSame(5, $service->bonusMaitrise(13));
        $this->assertSame(5, $service->bonusMaitrise(16));
        $this->assertSame(6, $service->bonusMaitrise(17));
        $this->assertSame(6, $service->bonusMaitrise(20));
    }

    public function test_bonus_de_maitrise_est_borne_entre_1_et_20(): void
    {
        $service = $this->service();

        $this->assertSame(2, $service->bonusMaitrise(0));
        $this->assertSame(6, $service->bonusMaitrise(25));
    }

    public function test_jets_de_sauvegarde_appliquent_la_maitrise_de_la_classe(): void
    {
        $personnage = $this->personnage(
            ['niveau' => 1],
            [
                'abilites' => new Abilite(['force' => 16, 'dexterite' => 10, 'constitution' => 14, 'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10]),
                'classe' => new Classe(['nom' => 'Guerrier']),
            ]
        );

        $jets = collect($this->service()->jetsDeSauvegarde($personnage))->keyBy('aptitude');

        // Guerrier : maîtrise Force et Constitution (5e SRD).
        $this->assertTrue($jets['force']['maitrise']);
        $this->assertSame(3 + 2, $jets['force']['bonus']); // mod +3, bonus de maîtrise +2 au niveau 1
        $this->assertTrue($jets['constitution']['maitrise']);
        $this->assertSame(2 + 2, $jets['constitution']['bonus']);

        $this->assertFalse($jets['dexterite']['maitrise']);
        $this->assertSame(0, $jets['dexterite']['bonus']);
    }

    public function test_jets_de_sauvegarde_sans_maitrise_pour_une_classe_inconnue(): void
    {
        $personnage = $this->personnage([], ['classe' => new Classe(['nom' => 'Classe Maison'])]);

        $jets = $this->service()->jetsDeSauvegarde($personnage);

        $this->assertTrue(collect($jets)->every(fn ($jet) => $jet['maitrise'] === false));
    }

    public function test_competences_appliquent_la_maitrise_choisie_par_le_joueur(): void
    {
        $personnage = $this->personnage(
            ['niveau' => 5],
            [
                'abilites' => new Abilite(['force' => 16, 'dexterite' => 10, 'constitution' => 10, 'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10]),
                'listeCompetence' => new ListeCompetence(['competences' => ['Athlétisme']]),
            ]
        );

        $competences = collect($this->service()->competences($personnage))->keyBy('nom');

        $this->assertTrue($competences['Athlétisme']['maitrise']);
        $this->assertSame(3 + 3, $competences['Athlétisme']['bonus']); // mod Force +3, bonus de maîtrise +3 au niveau 5

        $this->assertFalse($competences['Acrobaties']['maitrise']);
        $this->assertSame(0, $competences['Acrobaties']['bonus']);
    }

    public function test_perception_passive_ajoute_le_bonus_de_perception_a_10(): void
    {
        $personnage = $this->personnage(
            ['niveau' => 1],
            [
                'abilites' => new Abilite(['force' => 10, 'dexterite' => 10, 'constitution' => 10, 'intelligence' => 10, 'sagesse' => 14, 'charisme' => 10]),
                'listeCompetence' => new ListeCompetence(['competences' => ['Perception']]),
            ]
        );

        // Sagesse 14 -> mod +2, maîtrisé -> +2 de bonus de maîtrise au niveau 1.
        $this->assertSame(14, $this->service()->perceptionPassive($personnage));
    }

    public function test_les_classes_martiales_ne_sont_pas_lanceuses_de_sorts(): void
    {
        $service = $this->service();

        foreach (['Guerrier', 'Barbare', 'Roublard', 'Classe Inconnue'] as $nom) {
            $personnage = $this->personnage([], ['classe' => new Classe(['nom' => $nom])]);
            $this->assertFalse($service->estLanceurDeSorts($personnage), "$nom ne devrait pas être lanceur de sorts");
            $this->assertNull($service->statistiquesIncantation($personnage));
        }
    }

    public function test_les_classes_lanceuses_de_sorts_ont_des_statistiques_d_incantation(): void
    {
        $personnage = $this->personnage(
            ['niveau' => 5],
            [
                'abilites' => new Abilite(['force' => 10, 'dexterite' => 10, 'constitution' => 10, 'intelligence' => 16, 'sagesse' => 10, 'charisme' => 10]),
                'classe' => new Classe(['nom' => 'Magicien']),
            ]
        );

        $this->assertTrue($this->service()->estLanceurDeSorts($personnage));

        $stats = $this->service()->statistiquesIncantation($personnage);

        $this->assertSame('Intelligence', $stats['caracteristique']);
        $this->assertSame(3, $stats['modificateur']);
        $this->assertSame(8 + 3 + 3, $stats['dd']);
        $this->assertSame(3 + 3, $stats['bonusAttaque']);
    }

    public function test_attaques_ne_retiennent_que_les_armes_reconnues_de_l_equipement(): void
    {
        $personnage = $this->personnage(
            ['niveau' => 1, 'equipement' => ['Épée', 'Livre', 'Arc court']],
            [
                'abilites' => new Abilite(['force' => 14, 'dexterite' => 12, 'constitution' => 10, 'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10]),
                'classe' => new Classe(['nom' => 'Guerrier']),
            ]
        );

        $attaques = collect($this->service()->attaques($personnage))->keyBy('nom');

        $this->assertCount(2, $attaques);
        $this->assertSame('1d8 +2', $attaques['Épée']['degats']);
        $this->assertSame(2 + 2, $attaques['Épée']['bonusAttaque']);
        $this->assertSame('1d6 +1', $attaques['Arc court']['degats']);
        $this->assertArrayNotHasKey('Livre', $attaques);
    }

    public function test_actions_retourne_les_sorts_pour_une_classe_lanceuse(): void
    {
        $personnage = $this->personnage([], ['classe' => new Classe(['nom' => 'Clerc'])]);

        $actions = $this->service()->actions($personnage);

        $this->assertSame('sorts', $actions['type']);
        $this->assertIsArray($actions['incantation']);
        $this->assertSame([], $actions['attaques']);
    }

    public function test_actions_retourne_les_attaques_pour_une_classe_martiale(): void
    {
        $personnage = $this->personnage(
            ['equipement' => ['Épée']],
            ['classe' => new Classe(['nom' => 'Barbare'])]
        );

        $actions = $this->service()->actions($personnage);

        $this->assertSame('attaques', $actions['type']);
        $this->assertNull($actions['incantation']);
        $this->assertCount(1, $actions['attaques']);
    }

    public function test_traits_rassemble_les_descriptions_disponibles(): void
    {
        $personnage = $this->personnage([], [
            'race' => new Race(['nom' => 'Elfe', 'description' => 'Gracieux et immortel.']),
            'classe' => new Classe(['nom' => 'Guerrier', 'description' => 'Maître des armes.']),
            'sousRace' => new SousRace(['nom' => 'Haut Elfe', 'description' => 'Maîtrise une base de magie.']),
            'sousClasse' => null,
        ]);

        $traits = collect($this->service()->traits($personnage))->keyBy('label');

        $this->assertSame('Gracieux et immortel.', $traits['Race']['texte']);
        $this->assertSame('Maître des armes.', $traits['Classe']['texte']);
        $this->assertSame('Maîtrise une base de magie.', $traits['Haut Elfe']['texte']);
        $this->assertCount(3, $traits);
    }

    public function test_traits_ignore_les_relations_absentes_ou_sans_description(): void
    {
        $personnage = $this->personnage([], [
            'race' => new Race(['nom' => 'Humain', 'description' => null]),
            'classe' => new Classe(['nom' => 'Guerrier', 'description' => null]),
        ]);

        $this->assertSame([], $this->service()->traits($personnage));
    }
}
