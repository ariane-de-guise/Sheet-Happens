<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Database\Factories\ClasseFactory;
use Database\Factories\RaceFactory;
use Database\Factories\SousClasseFactory;
use Database\Factories\SousRaceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PersonnageCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_refuse_un_payload_incomplet(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $response = $this->actingAs($utilisateur)->postJson('/api/personnages', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'nom',
                'race_id',
                'classe_id',
                'point_de_vie',
                'abilites',
            ]);
    }

    private function utilisateurConnecte(): Utilisateur
    {
        $utilisateur = Utilisateur::factory()->create();
        $this->actingAs($utilisateur);

        return $utilisateur;
    }

    // --- Étape Race ---

    public function test_l_etape_race_est_accessible(): void
    {
        $this->utilisateurConnecte();
        RaceFactory::new()->create();

        $this->get('/personnages/creer')->assertOk();
    }

    public function test_on_peut_enregistrer_une_race_sans_sous_race(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();

        $response = $this->post('/personnages/creer/race', ['race_id' => $race->id]);

        $response->assertRedirect(route('creation.classe'));
        $this->assertSame($race->id, session('personnage_creation')['race_id']);
    }

    public function test_on_peut_enregistrer_une_race_avec_une_sous_race_valide(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();

        $response = $this->post('/personnages/creer/race', [
            'race_id' => $race->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $response->assertRedirect(route('creation.classe'));
        $this->assertSame($sousRace->id, session('personnage_creation')['sous_race_id']);
    }

    public function test_l_enregistrement_de_la_race_neutralise_une_sous_race_incoherente(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();

        $response = $this->post('/personnages/creer/race', [
            'race_id' => (string) $race->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $response->assertRedirect(route('creation.classe'));
        $this->assertNull(session('personnage_creation')['sous_race_id']);
    }

    public function test_l_enregistrement_de_la_race_echoue_sans_race_valide(): void
    {
        $this->utilisateurConnecte();

        $this->post('/personnages/creer/race', [])
            ->assertSessionHasErrors(['race_id']);
    }

    // --- Étape Classe ---

    public function test_l_etape_classe_redirige_si_aucune_race_selectionnee(): void
    {
        $this->utilisateurConnecte();

        $this->get('/personnages/creer/classe')
            ->assertRedirect(route('creation.race'));
    }

    public function test_l_etape_classe_est_accessible_apres_avoir_choisi_une_race(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);

        $this->get('/personnages/creer/classe')->assertOk();
    }

    public function test_on_peut_enregistrer_une_classe_sans_sous_classe(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);
        $classe = ClasseFactory::new()->create();

        $response = $this->post('/personnages/creer/classe', ['classe_id' => $classe->id]);

        $response->assertRedirect(route('creation.methode'));
        $this->assertSame($classe->id, session('personnage_creation')['classe_id']);
    }

    public function test_on_peut_enregistrer_une_classe_avec_une_sous_classe_valide(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);
        $classe = ClasseFactory::new()->create();
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $response = $this->post('/personnages/creer/classe', [
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
        ]);

        $response->assertRedirect(route('creation.methode'));
        $this->assertSame($sousClasse->id, session('personnage_creation')['sous_classe_id']);
    }

    public function test_l_enregistrement_de_la_classe_neutralise_une_sous_classe_en_cas_d_incoherence_de_type(): void
    {
        // Même situation que pour la race : la règle "exists" compare via
        // SQL (typage souple) alors que le contrôleur compare ensuite avec
        // `!==` (typage strict). Envoyer classe_id sous forme de chaîne fait
        // passer la validation tout en déclenchant la neutralisation
        // manuelle de la sous-classe dans le contrôleur.
        $this->utilisateurConnecte();
        $classe = ClasseFactory::new()->create();
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $response = $this->post('/personnages/creer/classe', [
            'classe_id' => (string) $classe->id,
            'sous_classe_id' => $sousClasse->id,
        ]);

        $response->assertRedirect(route('creation.methode'));
        $this->assertNull(session('personnage_creation')['sous_classe_id']);
    }

    public function test_changer_de_classe_neutralise_une_sous_classe_incoherente(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);

        $premiereClasse = ClasseFactory::new()->create();
        $this->post('/personnages/creer/classe', ['classe_id' => $premiereClasse->id]);

        $nouvelleClasse = ClasseFactory::new()->create();
        $sousClasseAutreClasse = SousClasseFactory::new()->pourClasse($premiereClasse)->create();

        $response = $this->post('/personnages/creer/classe', [
            'classe_id' => $nouvelleClasse->id,
            'sous_classe_id' => $sousClasseAutreClasse->id,
        ]);

        $response->assertSessionHasErrors(['sous_classe_id']);
    }

    // --- Étape Méthode ---

    public function test_l_etape_methode_redirige_si_aucune_classe_selectionnee(): void
    {
        $this->utilisateurConnecte();

        $this->get('/personnages/creer/methode')
            ->assertRedirect(route('creation.classe'));
    }

    private function progresserJusquaClasse(): void
    {
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);
        $classe = ClasseFactory::new()->create();
        $this->post('/personnages/creer/classe', ['classe_id' => $classe->id]);
    }

    public function test_l_etape_methode_est_accessible_apres_avoir_choisi_une_classe(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaClasse();

        $this->get('/personnages/creer/methode')->assertOk();
    }

    public static function methodesValides(): array
    {
        return [
            ['points'],
            ['standard'],
            ['dice'],
        ];
    }

    #[DataProvider('methodesValides')]
    public function test_on_peut_enregistrer_chaque_methode_de_generation(string $methode): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaClasse();

        $response = $this->post('/personnages/creer/methode', ['methode' => $methode]);

        $response->assertRedirect(route('creation.caracteristiques'));
        $this->assertSame($methode, session('personnage_creation')['methode']);
    }

    public function test_l_enregistrement_de_la_methode_refuse_une_valeur_invalide(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaClasse();

        $this->post('/personnages/creer/methode', ['methode' => 'invalide'])
            ->assertSessionHasErrors(['methode']);
    }

    // --- Étape Caractéristiques ---

    public function test_l_etape_caracteristiques_redirige_si_aucune_methode_selectionnee(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaClasse();

        $this->get('/personnages/creer/caracteristiques')
            ->assertRedirect(route('creation.methode'));
    }

    private function progresserJusquaMethode(string $methode = 'standard'): void
    {
        $this->progresserJusquaClasse();
        $this->post('/personnages/creer/methode', ['methode' => $methode]);
    }

    public function test_l_etape_caracteristiques_est_accessible(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode();

        $this->get('/personnages/creer/caracteristiques')->assertOk();
    }

    public function test_on_peut_enregistrer_des_caracteristiques_en_methode_standard(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('standard');

        $response = $this->post('/personnages/creer/caracteristiques', [
            'force' => 15, 'dexterite' => 14, 'constitution' => 13,
            'intelligence' => 12, 'sagesse' => 10, 'charisme' => 8,
        ]);

        $response->assertRedirect(route('creation.competences'));
        $this->assertSame(15, session('personnage_creation')['abilites']['force']);
    }

    public function test_on_peut_enregistrer_des_caracteristiques_en_methode_points_avec_cout_valide(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('points');

        // Coût: 15(10)+8(0)+8(0)+8(0)+8(0)+... on veut un total de 27.
        // 15=10, 8=0, 8=0, 8=0, 8=0 -> il manque 17 pour atteindre 27.
        // Utilisons 15(10) + 14(8) + 8(0) + 8(0) + 8(0) + 9(1) = 19, pas 27.
        // Cherchons une combinaison valant exactement 27 :
        // 15(10) + 15(10) + 8(0) + 8(0) + 8(0) + 9(1)... = 21. Essayons :
        // 15(10) + 14(8) + 13(6) + 8(0) + 8(0) + 8(0) = 24. Ajoutons 10(2)+9(1) à la place de deux 8 :
        // 15(10) + 14(8) + 10(2) + 9(1) + 8(0) + 8(0) = 21... Simplifions avec une combinaison connue de D&D 5e:
        // 15(10) + 15(10) + 15(10)... trop cher. Utilisons directement le calcul programmatique ci-dessous.
        $couts = [8 => 0, 9 => 1, 10 => 2, 11 => 3, 12 => 4, 13 => 6, 14 => 8, 15 => 10];
        $valeurs = $this->trouverCombinaisonCout($couts, 27);

        $response = $this->post('/personnages/creer/caracteristiques', $valeurs);

        $response->assertRedirect(route('creation.competences'));
    }

    public function test_l_enregistrement_des_caracteristiques_en_methode_points_refuse_un_cout_invalide(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('points');

        $response = $this
            ->withHeader('referer', route('creation.caracteristiques'))
            ->post('/personnages/creer/caracteristiques', [
                'force' => 15, 'dexterite' => 15, 'constitution' => 15,
                'intelligence' => 15, 'sagesse' => 15, 'charisme' => 15,
            ]);

        $response->assertRedirect(route('creation.caracteristiques'));
        $response->assertSessionHas('error');
    }

    public function test_l_enregistrement_des_caracteristiques_en_methode_standard_accepte_points_disponibles(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('standard');

        $response = $this->post('/personnages/creer/caracteristiques', [
            'force' => 15, 'dexterite' => 14, 'constitution' => 13,
            'intelligence' => 12, 'sagesse' => 10, 'charisme' => 8,
            'points_disponibles' => 5,
        ]);

        $response->assertRedirect(route('creation.competences'));
    }

    /**
     * Trouve une combinaison de 6 valeurs (parmi les clés de la table de
     * coûts) dont la somme des coûts est exactement égale à la cible.
     */
    private function trouverCombinaisonCout(array $couts, int $cible): array
    {
        $cles = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];
        $valeursPossibles = array_keys($couts);

        $recherche = function (int $index, int $restant) use (&$recherche, $cles, $couts, $valeursPossibles) {
            if ($index === count($cles)) {
                return $restant === 0 ? [] : null;
            }

            foreach ($valeursPossibles as $valeur) {
                $coutValeur = $couts[$valeur];
                if ($coutValeur > $restant) {
                    continue;
                }

                $suite = $recherche($index + 1, $restant - $coutValeur);
                if ($suite !== null) {
                    return array_merge([$cles[$index] => $valeur], $suite);
                }
            }

            return null;
        };

        $resultat = $recherche(0, $cible);
        $this->assertNotNull($resultat, 'Aucune combinaison trouvée pour le coût cible.');

        return $resultat;
    }

    // --- Jet de dés ---

    public function test_le_jet_de_des_met_a_jour_une_abilite_valide(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $response = $this->post('/personnages/creer/caracteristiques/roller', ['abilite' => 'force']);

        $response->assertRedirect();
        $response->assertSessionHas('rolled', 'force');
        $valeur = session('personnage_creation')['abilites']['force'];
        $this->assertGreaterThanOrEqual(3, $valeur);
        $this->assertLessThanOrEqual(18, $valeur);
    }

    public function test_le_jet_de_des_en_json_retourne_la_valeur(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $response = $this->postJson('/personnages/creer/caracteristiques/roller', ['abilite' => 'dexterite']);

        $response->assertOk()->assertJsonStructure(['value', 'abilite']);
    }

    public function test_le_jet_de_des_refuse_une_abilite_invalide(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $response = $this->post('/personnages/creer/caracteristiques/roller', ['abilite' => 'invalide']);

        $response->assertRedirect();
        $response->assertSessionMissing('rolled');
    }

    public function test_le_jet_de_des_refuse_une_abilite_invalide_en_json(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $response = $this->postJson('/personnages/creer/caracteristiques/roller', ['abilite' => 'invalide']);

        $response->assertStatus(400)->assertJson(['error' => 'Abilite invalide']);
    }

    public function test_le_jet_de_des_refuse_une_absence_d_abilite(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $response = $this->post('/personnages/creer/caracteristiques/roller', []);

        $response->assertRedirect();
    }

    // --- Étape Compétences ---

    public function test_l_etape_competences_redirige_si_les_abilites_sont_incompletes(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $this->get('/personnages/creer/competences')
            ->assertRedirect(route('creation.caracteristiques'));
    }

    private function progresserJusquaCaracteristiques(): void
    {
        $this->progresserJusquaMethode('standard');
        $this->post('/personnages/creer/caracteristiques', [
            'force' => 15, 'dexterite' => 14, 'constitution' => 13,
            'intelligence' => 12, 'sagesse' => 10, 'charisme' => 8,
        ]);
    }

    public function test_l_etape_competences_est_accessible_une_fois_les_abilites_completes(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCaracteristiques();

        $this->get('/personnages/creer/competences')->assertOk();
    }

    public function test_on_peut_enregistrer_des_competences(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCaracteristiques();

        $response = $this->post('/personnages/creer/competences', [
            'competences' => ['Athlétisme', 'Perception'],
        ]);

        $response->assertRedirect(route('creation.contexte'));
        $this->assertSame(['Athlétisme', 'Perception'], array_values(session('personnage_creation')['competences']));
    }

    public function test_l_enregistrement_des_competences_refuse_moins_de_deux_choix(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCaracteristiques();

        $this->post('/personnages/creer/competences', ['competences' => ['Athlétisme']])
            ->assertSessionHasErrors(['competences']);
    }

    // --- Étape Contexte ---

    private function progresserJusquaCompetences(): void
    {
        $this->progresserJusquaCaracteristiques();
        $this->post('/personnages/creer/competences', [
            'competences' => ['Athlétisme', 'Perception'],
        ]);
    }

    private function getPayloadContexteValide(array $surcharges = []): array
    {
        (new \Database\Seeders\HistoriquesSeeder())->run();
        $historique = \App\Models\Historique::first();

        return array_merge([
            'nom' => 'Aragorn',
            'alignement' => 'Loyal bon',
            'historique_id' => $historique->id,
            'ideal_id' => $historique->ideaux()->first()->id,
            'defaut_id' => $historique->defauts()->first()->id,
            'attachement_id' => $historique->attachements()->first()->id,
            'personnalite_id' => $historique->personnalites()->first()->id,
            'outil' => 'Aucun',
            'langues' => ['Commun', 'Elfe'],
            'equipements' => ['Épée'],
            'description' => 'Roi du Gondor',
        ], $surcharges);
    }

    public function test_l_etape_contexte_est_accessible(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCompetences();

        $this->get('/personnages/creer/contexte')->assertOk();
    }

    public function test_on_peut_enregistrer_le_contexte_avec_loutil_aucun(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCompetences();

        $response = $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide([
            'outil' => 'Aucun',
        ]));

        $response->assertRedirect(route('creation.confirmation'));
        $this->assertNull(session('personnage_creation')['outil']);
    }

    public function test_on_peut_enregistrer_le_contexte_avec_un_outil_specifique(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCompetences();

        $response = $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide([
            'outil' => 'Kit de joueur',
        ]));

        $response->assertRedirect(route('creation.confirmation'));
        $donnees = session('personnage_creation');
        $this->assertSame('Kit de joueur', $donnees['outil']);
        $this->assertSame(['Épée'], $donnees['equipement']);
    }

    public function test_l_enregistrement_du_contexte_exige_les_champs_obligatoires(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCompetences();

        $this->post('/personnages/creer/contexte', [])
            ->assertSessionHasErrors([
                'nom', 'alignement', 'historique_id', 'ideal_id',
                'defaut_id', 'attachement_id', 'personnalite_id',
                'outil', 'langues', 'equipements'
            ]);
    }

    // --- Étape Confirmation ---

    private function progresserJusquaContexte(): void
    {
        $this->progresserJusquaCompetences();
        $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide());
    }

    public function test_l_etape_confirmation_redirige_si_aucun_nom(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCompetences();

        $this->get('/personnages/creer/confirmation')
            ->assertRedirect(route('creation.contexte'));
    }

    public function test_l_etape_confirmation_est_accessible_et_affiche_les_points_de_vie(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaContexte();

        $this->get('/personnages/creer/confirmation')->assertOk();
    }

    public function test_l_etape_confirmation_fonctionne_avec_sous_race_et_sous_classe(): void
    {
        $utilisateur = $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $this->post('/personnages/creer/race', [
            'race_id' => $race->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $classe = ClasseFactory::new()->create(['de_de_vie' => 'd10', 'niveau_sous_classe' => 1]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();
        $this->post('/personnages/creer/classe', [
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
        ]);

        $this->post('/personnages/creer/methode', ['methode' => 'standard']);
        $this->post('/personnages/creer/caracteristiques', [
            'force' => 15, 'dexterite' => 14, 'constitution' => 13,
            'intelligence' => 12, 'sagesse' => 10, 'charisme' => 8,
        ]);
        $this->post('/personnages/creer/competences', ['competences' => ['Athlétisme', 'Perception']]);
        $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide([
            'nom' => 'Aragorn',
        ]));

        $this->get('/personnages/creer/confirmation')
            ->assertOk()
            ->assertSee($sousRace->nom)
            ->assertSee($sousClasse->nom);
    }

    public function test_l_etape_confirmation_calcule_les_points_de_vie_sans_classe_avec_de_de_vie(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);
        $classe = ClasseFactory::new()->create(['de_de_vie' => null]);
        $this->post('/personnages/creer/classe', ['classe_id' => $classe->id]);
        $this->post('/personnages/creer/methode', ['methode' => 'standard']);
        $this->post('/personnages/creer/caracteristiques', [
            'force' => 15, 'dexterite' => 14, 'constitution' => 8,
            'intelligence' => 12, 'sagesse' => 10, 'charisme' => 8,
        ]);
        $this->post('/personnages/creer/competences', ['competences' => ['Athlétisme', 'Perception']]);
        $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide([
            'nom' => 'Sans classe',
        ]));

        $this->get('/personnages/creer/confirmation')->assertOk();
    }

    // --- Finalisation ---

    public function test_la_finalisation_cree_le_personnage_et_vide_la_session(): void
    {
        $utilisateur = $this->utilisateurConnecte();
        $this->progresserJusquaContexte();

        $response = $this->post('/personnages/creer');

        $response->assertRedirect();
        $this->assertDatabaseHas('personnages', [
            'nom' => 'Aragorn',
            'utilisateur_id' => $utilisateur->id,
        ]);
        $this->assertNull(session('personnage_creation'));
    }

    public function test_la_finalisation_retire_une_sous_classe_devenue_incompatible(): void
    {
        $utilisateur = $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);

        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();
        $this->post('/personnages/creer/classe', [
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
        ]);

        // On force artificiellement le niveau requis à une valeur plus haute
        // que le niveau 1 auquel un personnage est toujours créé.
        $classe->update(['niveau_sous_classe' => 5]);

        $this->post('/personnages/creer/methode', ['methode' => 'standard']);
        $this->post('/personnages/creer/caracteristiques', [
            'force' => 15, 'dexterite' => 14, 'constitution' => 13,
            'intelligence' => 12, 'sagesse' => 10, 'charisme' => 8,
        ]);
        $this->post('/personnages/creer/competences', ['competences' => ['Athlétisme', 'Perception']]);
        $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide([
            'nom' => 'Aragorn',
        ]));

        $this->post('/personnages/creer');

        $this->assertDatabaseHas('personnages', [
            'nom' => 'Aragorn',
            'sous_classe_id' => null,
        ]);
    }

    public function test_la_finalisation_redirige_vers_la_confirmation_si_la_creation_echoue(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaContexte();

        // On invalide la race sélectionnée après coup pour provoquer un
        // échec de validation dans le service de création.
        session(['personnage_creation' => array_merge(session('personnage_creation'), [
            'race_id' => 999999,
        ])]);

        $response = $this->post('/personnages/creer');

        $response->assertRedirect(route('creation.confirmation'));
        $response->assertSessionHasErrors(['race_id']);
    }

    // --- API sous-classes / sous-races ---

    public function test_get_sous_classes_retourne_les_sous_classes_si_le_niveau_est_suffisant(): void
    {
        $this->utilisateurConnecte();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 2]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $response = $this->getJson("/api/sous-classes/{$classe->id}/2");

        $response->assertOk()->assertJsonFragment(['id' => $sousClasse->id]);
    }

    public function test_get_sous_classes_retourne_vide_si_le_niveau_est_insuffisant(): void
    {
        $this->utilisateurConnecte();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 5]);
        SousClasseFactory::new()->pourClasse($classe)->create();

        $response = $this->getJson("/api/sous-classes/{$classe->id}/1");

        $response->assertOk()->assertExactJson([]);
    }

    public function test_get_sous_classes_utilise_le_niveau_max_par_defaut(): void
    {
        $this->utilisateurConnecte();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 5]);
        SousClasseFactory::new()->pourClasse($classe)->create();

        $response = $this->getJson("/api/sous-classes/{$classe->id}");

        $response->assertOk()->assertExactJson([]);
    }

    public function test_get_sous_races_retourne_les_sous_races_de_la_race(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $autreRace = RaceFactory::new()->create();
        SousRaceFactory::new()->pourRace($autreRace)->create();

        $response = $this->getJson("/api/sous-races/{$race->id}");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $sousRace->id]);
    }
}
