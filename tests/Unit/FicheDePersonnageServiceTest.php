<?php

namespace Tests\Unit;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\ListeCompetence;
use App\Models\Objet;
use App\Models\Personnage;
use App\Models\PersonnageObjet;
use App\Models\Race;
use App\Models\SousRace;
use App\Services\FicheDePersonnageService;
use App\Services\InventaireService;
use Tests\TestCase;

class FicheDePersonnageServiceTest extends TestCase
{
    private function service(): FicheDePersonnageService
    {
        return new FicheDePersonnageService(new InventaireService);
    }

    /**
     * Construit un Personnage en mémoire (aucune écriture en base) avec ses
     * relations attachées via setRelation, pour tester la logique de calcul
     * du service de façon isolée et rapide. `objets` est elle aussi
     * préchargée (vide par défaut) pour qu'InventaireService::bonusActifs()
     * ne déclenche aucune requête.
     */
    private function personnage(array $attributs = [], array $relations = []): Personnage
    {
        $personnage = new Personnage(array_merge([
            'nom' => 'Test',
            'niveau' => 1,
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
        $personnage->setRelation('objets', $relations['objets'] ?? collect());

        return $personnage;
    }

    /**
     * Construit un Objet en mémoire avec son pivot attaché (équipé ou non),
     * pour simuler un inventaire sans écriture en base.
     */
    private function objetEquipe(array $attributs, bool $equipe = true, int $quantite = 1): Objet
    {
        $objet = new Objet($attributs);
        $objet->setRelation('pivot', new PersonnageObjet(['equipe' => $equipe, 'quantite' => $quantite]));

        return $objet;
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

    public function test_jets_de_sauvegarde_appliquent_le_bonus_global_d_un_objet_equipe(): void
    {
        $anneau = $this->objetEquipe(['nom' => 'Anneau de protection', 'type' => 'autre', 'bonus' => ['bonus_jets_sauvegarde' => 1]], equipe: true);

        $personnage = $this->personnage(
            ['niveau' => 1],
            ['classe' => new Classe(['nom' => 'Classe Maison']), 'objets' => collect([$anneau])]
        );

        $jets = collect($this->service()->jetsDeSauvegarde($personnage))->keyBy('aptitude');

        // Mod +0 (score 10), pas de maîtrise, +1 du bonus global de l'anneau.
        $this->assertSame(1, $jets['force']['bonus']);
        $this->assertSame(1, $jets['dexterite']['bonus']);
    }

    public function test_competences_appliquent_le_bonus_global_d_un_objet_equipe(): void
    {
        $cape = $this->objetEquipe(['nom' => 'Cape de virtuose', 'type' => 'autre', 'bonus' => ['bonus_competences' => 2]], equipe: true);

        $personnage = $this->personnage([], ['objets' => collect([$cape])]);

        $competences = collect($this->service()->competences($personnage))->keyBy('nom');

        // Mod +0 (score 10), pas de maîtrise, +2 du bonus global de la cape.
        $this->assertSame(2, $competences['Acrobaties']['bonus']);
        $this->assertSame(2, $competences['Persuasion']['bonus']);
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

    public function test_classe_armure_sans_armure_equipee_est_10_plus_mod_dexterite(): void
    {
        $personnage = $this->personnage([], [
            'abilites' => new Abilite(['force' => 10, 'dexterite' => 14, 'constitution' => 10, 'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10]),
        ]);

        $this->assertSame(12, $this->service()->classeArmure($personnage));
    }

    public function test_classe_armure_avec_armure_legere_et_bouclier_equipes(): void
    {
        $armure = $this->objetEquipe(['nom' => 'Armure de cuir', 'type' => 'armure', 'categorie' => 'legere', 'ca_base' => 11]);
        $bouclier = $this->objetEquipe(['nom' => 'Bouclier', 'type' => 'bouclier', 'bonus' => ['ca' => 2]]);

        $personnage = $this->personnage([], [
            'abilites' => new Abilite(['force' => 10, 'dexterite' => 14, 'constitution' => 10, 'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10]),
            'objets' => collect([$armure, $bouclier]),
        ]);

        // Légère : plein bonus de Dex (+2) ; 11 (base) + 2 (Dex) + 2 (bouclier) = 15.
        $this->assertSame(15, $this->service()->classeArmure($personnage));
    }

    public function test_classe_armure_avec_armure_lourde_ignore_le_bonus_de_dexterite(): void
    {
        $armure = $this->objetEquipe(['nom' => 'Armure de plates', 'type' => 'armure', 'categorie' => 'lourde', 'ca_base' => 18]);

        $personnage = $this->personnage([], [
            'abilites' => new Abilite(['force' => 10, 'dexterite' => 18, 'constitution' => 10, 'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10]),
            'objets' => collect([$armure]),
        ]);

        $this->assertSame(18, $this->service()->classeArmure($personnage));
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

    public function test_attaques_ne_retiennent_que_les_armes_equipees(): void
    {
        $epee = $this->objetEquipe(['nom' => 'Épée', 'type' => 'arme', 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant'], equipe: true);
        $arcNonEquipe = $this->objetEquipe(['nom' => 'Arc court', 'type' => 'arme', 'degats' => '1d6', 'caracteristique_degats' => 'dexterite', 'type_degats' => 'perforant'], equipe: false);
        $livre = $this->objetEquipe(['nom' => 'Livre', 'type' => 'autre'], equipe: true);
        $armeSansDegats = $this->objetEquipe(['nom' => 'Arme sans dégâts', 'type' => 'arme', 'degats' => null], equipe: true);

        $personnage = $this->personnage(
            ['niveau' => 1],
            [
                'abilites' => new Abilite(['force' => 14, 'dexterite' => 12, 'constitution' => 10, 'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10]),
                'classe' => new Classe(['nom' => 'Guerrier']),
                'objets' => collect([$epee, $arcNonEquipe, $livre, $armeSansDegats]),
            ]
        );

        $attaques = collect($this->service()->attaques($personnage))->keyBy('nom');

        $this->assertCount(1, $attaques);
        $this->assertSame('1d8 +2', $attaques['Épée']['degats']);
        $this->assertSame(2 + 2, $attaques['Épée']['bonusAttaque']);
        $this->assertArrayNotHasKey('Arc court', $attaques);
        $this->assertArrayNotHasKey('Livre', $attaques);
        $this->assertArrayNotHasKey('Arme sans dégâts', $attaques);
    }

    public function test_attaques_appliquent_le_bonus_d_une_arme_magique(): void
    {
        $epeeMagique = $this->objetEquipe([
            'nom' => 'Épée +1', 'type' => 'arme', 'degats' => '1d8',
            'caracteristique_degats' => 'force', 'type_degats' => 'tranchant',
            'bonus' => ['bonus_attaque' => 1, 'bonus_degats' => 1],
        ]);

        $personnage = $this->personnage(
            ['niveau' => 1],
            [
                'abilites' => new Abilite(['force' => 14, 'dexterite' => 10, 'constitution' => 10, 'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10]),
                'classe' => new Classe(['nom' => 'Guerrier']),
                'objets' => collect([$epeeMagique]),
            ]
        );

        $attaque = $this->service()->attaques($personnage)[0];

        // Mod Force +2, bonus de maîtrise +2 (niveau 1), bonus magique +1.
        $this->assertSame('1d8 +3', $attaque['degats']);
        $this->assertSame(2 + 2 + 1, $attaque['bonusAttaque']);
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
        $epee = $this->objetEquipe(['nom' => 'Épée', 'type' => 'arme', 'degats' => '1d8', 'caracteristique_degats' => 'force', 'type_degats' => 'tranchant']);

        $personnage = $this->personnage(
            [],
            ['classe' => new Classe(['nom' => 'Barbare']), 'objets' => collect([$epee])]
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
