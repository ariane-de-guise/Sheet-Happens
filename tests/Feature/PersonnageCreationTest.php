<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Historique;
use App\Models\Utilisateur;
use Database\Factories\ClasseFactory;
use Database\Factories\RaceFactory;
use Database\Factories\SousClasseFactory;
use Database\Factories\SousRaceFactory;
use Database\Seeders\HistoriquesSeeder;
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

    public function test_l_enregistrement_de_la_race_conserve_une_sous_race_valide_malgre_des_types_mixtes(): void
    {
        // Un vrai formulaire HTML envoie toutes ses valeurs sous forme de
        // chaînes de caractères. La règle "exists" (comparaison SQL, typage
        // souple) confirme déjà que la sous-race appartient à la race
        // envoyée ; le contrôleur ne doit pas perdre ce choix à cause d'une
        // comparaison stricte (int vs string) entre l'id validé et celui
        // renvoyé par Eloquent.
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();

        $response = $this->post('/personnages/creer/race', [
            'race_id' => (string) $race->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $response->assertRedirect(route('creation.classe'));
        $this->assertSame($sousRace->id, session('personnage_creation')['sous_race_id']);
    }

    public function test_l_enregistrement_de_la_race_echoue_sans_race_valide(): void
    {
        $this->utilisateurConnecte();

        $this->post('/personnages/creer/race', [])
            ->assertSessionHasErrors(['race_id']);
    }

    public function test_l_enregistrement_de_la_race_exige_une_sous_race_si_la_race_en_propose(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        SousRaceFactory::new()->pourRace($race)->create();

        $this->post('/personnages/creer/race', ['race_id' => $race->id])
            ->assertSessionHasErrors(['sous_race_id']);
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

    public function test_l_enregistrement_de_la_classe_conserve_une_sous_classe_valide_malgre_des_types_mixtes(): void
    {
        // Même situation que pour la race : un vrai formulaire HTML envoie
        // classe_id sous forme de chaîne. La règle "exists" confirme déjà
        // que la sous-classe appartient à la classe envoyée ; ça ne doit
        // pas être perdu à cause d'une comparaison stricte (int vs string).
        $this->utilisateurConnecte();
        $classe = ClasseFactory::new()->create();
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $response = $this->post('/personnages/creer/classe', [
            'classe_id' => (string) $classe->id,
            'sous_classe_id' => $sousClasse->id,
        ]);

        $response->assertRedirect(route('creation.methode'));
        $this->assertSame($sousClasse->id, session('personnage_creation')['sous_classe_id']);
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

    public function test_l_enregistrement_de_la_classe_exige_une_sous_classe_disponible_des_le_niveau_1(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);
        SousClasseFactory::new()->pourClasse($classe)->create();

        $this->post('/personnages/creer/classe', ['classe_id' => $classe->id])
            ->assertSessionHasErrors(['sous_classe_id']);
    }

    public function test_l_enregistrement_de_la_classe_n_exige_pas_une_sous_classe_indisponible_au_niveau_1(): void
    {
        $this->utilisateurConnecte();
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 2]);
        SousClasseFactory::new()->pourClasse($classe)->create();

        $response = $this->post('/personnages/creer/classe', ['classe_id' => $classe->id]);

        $response->assertRedirect(route('creation.methode'));
        $this->assertNull(session('personnage_creation')['sous_classe_id']);
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

    public function test_l_enregistrement_des_caracteristiques_en_methode_standard_refuse_une_repartition_incorrecte(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('standard');

        $response = $this
            ->withHeader('referer', route('creation.caracteristiques'))
            ->post('/personnages/creer/caracteristiques', [
                'force' => 15, 'dexterite' => 14, 'constitution' => 13,
                'intelligence' => 12, 'sagesse' => 10, 'charisme' => 9,
            ]);

        $response->assertRedirect(route('creation.caracteristiques'));
        $response->assertSessionHas('error');
    }

    public function test_l_enregistrement_des_caracteristiques_en_methode_dice_refuse_sans_avoir_lance_les_des(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $response = $this->post('/personnages/creer/caracteristiques', [
            'force' => 15, 'dexterite' => 14, 'constitution' => 13,
            'intelligence' => 12, 'sagesse' => 10, 'charisme' => 8,
        ]);

        $response->assertRedirect(route('creation.caracteristiques'));
        $response->assertSessionHas('error');
    }

    public function test_l_enregistrement_des_caracteristiques_en_methode_dice_refuse_une_repartition_incorrecte(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');
        $this->post('/personnages/creer/caracteristiques/roller', []);
        $pool = session('personnage_creation')['dice_pool'];

        // On modifie une valeur pour qu'elle ne corresponde plus au lancer.
        $valeurs = [
            'force' => $pool[0], 'dexterite' => $pool[1], 'constitution' => $pool[2],
            'intelligence' => $pool[3], 'sagesse' => $pool[4], 'charisme' => $pool[5] + 1,
        ];

        $response = $this
            ->withHeader('referer', route('creation.caracteristiques'))
            ->post('/personnages/creer/caracteristiques', $valeurs);

        $response->assertRedirect(route('creation.caracteristiques'));
        $response->assertSessionHas('error');
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

    public function test_le_jet_de_des_genere_six_valeurs_valides(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $response = $this->post('/personnages/creer/caracteristiques/roller', []);

        $response->assertRedirect();
        $rolls = session('personnage_creation')['dice_pool'];
        $this->assertCount(6, $rolls);
        foreach ($rolls as $valeur) {
            $this->assertGreaterThanOrEqual(3, $valeur);
            $this->assertLessThanOrEqual(18, $valeur);
        }
    }

    public function test_le_jet_de_des_reinitialise_les_abilites_deja_attribuees(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $this->post('/personnages/creer/caracteristiques', [
            'force' => 15, 'dexterite' => 14, 'constitution' => 13,
            'intelligence' => 12, 'sagesse' => 10, 'charisme' => 8,
        ]);

        $this->post('/personnages/creer/caracteristiques/roller', []);

        $abilites = session('personnage_creation')['abilites'];
        foreach ($abilites as $valeur) {
            $this->assertSame(0, $valeur);
        }
    }

    public function test_le_jet_de_des_en_json_retourne_les_six_resultats(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaMethode('dice');

        $response = $this->postJson('/personnages/creer/caracteristiques/roller', []);

        $response->assertOk()->assertJsonStructure(['rolls']);
        $this->assertCount(6, $response->json('rolls'));
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

    // En 5e, chaque classe propose sa propre liste de compétences (et un
    // nombre de choix qui lui est propre, ex. 4 pour le Roublard au lieu de
    // 2) : ces trois tests vérifient que l'étape respecte bien la classe
    // choisie plus tôt dans l'assistant, plutôt que d'offrir un choix libre
    // parmi toutes les compétences du jeu.
    private function progresserJusquaCaracteristiquesAvecClasse(Classe $classe): void
    {
        $race = RaceFactory::new()->create();
        $this->post('/personnages/creer/race', ['race_id' => $race->id]);
        $this->post('/personnages/creer/classe', ['classe_id' => $classe->id]);
        $this->post('/personnages/creer/methode', ['methode' => 'standard']);
        $this->post('/personnages/creer/caracteristiques', [
            'force' => 15, 'dexterite' => 14, 'constitution' => 13,
            'intelligence' => 12, 'sagesse' => 10, 'charisme' => 8,
        ]);
    }

    public function test_l_etape_competences_n_offre_que_les_competences_de_la_classe(): void
    {
        $this->utilisateurConnecte();
        $classe = ClasseFactory::new()->create([
            'competences_disponibles' => ['Arcanes', 'Histoire', 'Intuition', 'Investigation', 'Médecine', 'Religion'],
            'nombre_competences' => 2,
        ]);
        $this->progresserJusquaCaracteristiquesAvecClasse($classe);

        $this->get('/personnages/creer/competences')
            ->assertOk()
            ->assertViewHas('competencesDisponibles', ['Arcanes', 'Histoire', 'Intuition', 'Investigation', 'Médecine', 'Religion'])
            ->assertDontSee('Athlétisme');
    }

    public function test_on_ne_peut_pas_choisir_une_competence_hors_de_la_liste_de_la_classe(): void
    {
        $this->utilisateurConnecte();
        $classe = ClasseFactory::new()->create([
            'competences_disponibles' => ['Arcanes', 'Histoire', 'Intuition', 'Investigation', 'Médecine', 'Religion'],
            'nombre_competences' => 2,
        ]);
        $this->progresserJusquaCaracteristiquesAvecClasse($classe);

        // "Athlétisme" n'est pas dans la liste du Magicien : ce n'est pas
        // une compétence que sa classe lui permet de choisir.
        $this->post('/personnages/creer/competences', ['competences' => ['Arcanes', 'Athlétisme']])
            ->assertSessionHasErrors(['competences.1']);
    }

    public function test_le_nombre_de_competences_exige_suit_la_classe(): void
    {
        $this->utilisateurConnecte();
        // Le Roublard choisit 4 compétences en 5e, pas 2.
        $classe = ClasseFactory::new()->create([
            'competences_disponibles' => ['Acrobaties', 'Athlétisme', 'Art de la duplicité', 'Intuition', 'Intimidation', 'Investigation', 'Perception', 'Performance', 'Persuasion', 'Escamotage', 'Discrétion'],
            'nombre_competences' => 4,
        ]);
        $this->progresserJusquaCaracteristiquesAvecClasse($classe);

        $this->post('/personnages/creer/competences', ['competences' => ['Acrobaties', 'Discrétion']])
            ->assertSessionHasErrors(['competences']);

        $response = $this->post('/personnages/creer/competences', [
            'competences' => ['Acrobaties', 'Discrétion', 'Escamotage', 'Perception'],
        ]);

        $response->assertRedirect(route('creation.contexte'));
        $this->assertSame(
            ['Acrobaties', 'Discrétion', 'Escamotage', 'Perception'],
            array_values(session('personnage_creation')['competences'])
        );
    }

    // --- Étape Contexte ---

    private function progresserJusquaCompetences(): void
    {
        $this->progresserJusquaCaracteristiques();
        $this->post('/personnages/creer/competences', [
            'competences' => ['Athlétisme', 'Perception'],
        ]);
    }

    // L'historique choisi vient avec son propre équipement de départ, et
    // l'idéal choisi détermine l'alignement ; la race détermine les langues
    // parlées : ces valeurs sont désormais dérivées par le contrôleur et ne
    // font plus partie du payload envoyé par le joueur (voir
    // CreationPersonnageController::saveContexte()).
    private function getIdentifiantsHistoriqueValide(): array
    {
        (new HistoriquesSeeder)->run();
        $historique = Historique::first();

        return [
            'historique_id' => $historique->id,
            'ideal_id' => $historique->ideaux()->first()->id,
            'defaut_id' => $historique->defauts()->first()->id,
            'attachement_id' => $historique->attachements()->first()->id,
            'personnalite_id' => $historique->personnalites()->first()->id,
        ];
    }

    private function getPayloadContexteValide(array $surcharges = []): array
    {
        return array_merge([
            'nom' => 'Aragorn',
            'outil' => 'Aucun',
            'description' => 'Roi du Gondor',
        ], $this->getIdentifiantsHistoriqueValide(), $surcharges);
    }

    public function test_l_etape_contexte_est_accessible(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCompetences();

        $this->get('/personnages/creer/contexte')->assertOk();
    }

    public function test_l_etape_contexte_redirige_si_aucune_race_selectionnee(): void
    {
        $this->utilisateurConnecte();

        $this->get('/personnages/creer/contexte')
            ->assertRedirect(route('creation.race'));
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

    public function test_un_outil_envoye_pour_un_historique_sans_choix_est_ignore(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCompetences();

        $response = $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide([
            'outil' => 'Kit de joueur',
        ]));

        // L'historique par défaut de getIdentifiantsHistoriqueValide()
        // (Acolyte) n'offre aucun choix d'outil en 5e : toute valeur envoyée
        // pour "outil" est donc ignorée et le personnage n'en reçoit aucun,
        // plutôt que d'accepter un outil qui ne fait partie d'aucune liste.
        $response->assertRedirect(route('creation.confirmation'));
        $this->assertNull(session('personnage_creation')['outil']);
    }

    // En 5e, l'outil de départ dépend de l'historique : certains proposent
    // une liste précise dans laquelle choisir (ex. Criminel : un type de
    // jeu).
    public function test_on_peut_choisir_un_outil_parmi_ceux_offerts_par_l_historique(): void
    {
        $this->utilisateurConnecte();
        (new HistoriquesSeeder)->run();
        $this->progresserJusquaCompetences();
        $criminel = Historique::where('nom', 'Criminel')->firstOrFail();

        $response = $this->post('/personnages/creer/contexte', [
            'nom' => 'Aragorn',
            'outil' => 'Jeu de dés',
            'historique_id' => $criminel->id,
            'ideal_id' => $criminel->ideaux()->first()->id,
            'defaut_id' => $criminel->defauts()->first()->id,
            'attachement_id' => $criminel->attachements()->first()->id,
            'personnalite_id' => $criminel->personnalites()->first()->id,
        ]);

        $response->assertRedirect(route('creation.confirmation'));
        $donnees = session('personnage_creation');

        $this->assertSame('Jeu de dés', $donnees['outil']);
        $this->assertSame($criminel->equipement_depart, $donnees['equipement']);
    }

    // Certains historiques imposent au contraire un outil précis sans
    // aucun choix (ex. l'Ermite reçoit toujours un kit d'herboriste) : il
    // est alors fusionné avec l'équipement de départ plutôt que stocké
    // dans "outil", qui reste vide.
    public function test_un_outil_impose_par_l_historique_est_ajoute_a_l_equipement_sans_etre_un_choix(): void
    {
        $this->utilisateurConnecte();
        (new HistoriquesSeeder)->run();
        $this->progresserJusquaCompetences();
        $ermite = Historique::where('nom', 'Ermite')->firstOrFail();

        $response = $this->post('/personnages/creer/contexte', [
            'nom' => 'Aragorn',
            'historique_id' => $ermite->id,
            'ideal_id' => $ermite->ideaux()->first()->id,
            'defaut_id' => $ermite->defauts()->first()->id,
            'attachement_id' => $ermite->attachements()->first()->id,
            'personnalite_id' => $ermite->personnalites()->first()->id,
        ]);

        $response->assertRedirect(route('creation.confirmation'));
        $donnees = session('personnage_creation');

        $this->assertNull($donnees['outil']);
        $this->assertContains('Kit d\'herboriste', $donnees['equipement']);
        $this->assertSame(
            array_values(array_merge($ermite->equipement_depart, $ermite->outils_fixes)),
            $donnees['equipement']
        );
    }

    public function test_on_ne_peut_pas_choisir_un_outil_hors_de_la_liste_de_l_historique(): void
    {
        $this->utilisateurConnecte();
        (new HistoriquesSeeder)->run();
        $this->progresserJusquaCompetences();
        $criminel = Historique::where('nom', 'Criminel')->firstOrFail();

        $response = $this->post('/personnages/creer/contexte', [
            'nom' => 'Aragorn',
            // "Kit de potier" est un outil d'artisan, pas un jeu : il ne
            // fait pas partie des outils offerts par le Criminel.
            'outil' => 'Kit de potier',
            'historique_id' => $criminel->id,
            'ideal_id' => $criminel->ideaux()->first()->id,
            'defaut_id' => $criminel->defauts()->first()->id,
            'attachement_id' => $criminel->attachements()->first()->id,
            'personnalite_id' => $criminel->personnalites()->first()->id,
        ]);

        $response->assertSessionHasErrors(['outil']);
    }

    public function test_l_enregistrement_du_contexte_exige_les_champs_obligatoires(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCompetences();

        $this->post('/personnages/creer/contexte', [])
            ->assertSessionHasErrors([
                'nom', 'historique_id', 'ideal_id',
                'defaut_id', 'attachement_id', 'personnalite_id',
            ]);
    }

    // Cliquer sur "Retour" depuis l'étape contexte ne doit pas exiger que
    // l'étape soit complète (contrairement à "Suivant"), et ne doit surtout
    // pas effacer ce qui a déjà été rempli/sélectionné : avant ce
    // correctif, rien n'était sauvegardé tant que "Suivant" n'avait pas été
    // soumis avec succès, donc revenir en arrière puis ravancer perdait
    // tous les choix faits sur cette page.
    public function test_le_bouton_retour_du_contexte_sauvegarde_les_choix_partiels_sans_exiger_les_champs_obligatoires(): void
    {
        $this->utilisateurConnecte();
        $this->progresserJusquaCompetences();
        $identifiants = $this->getIdentifiantsHistoriqueValide();

        $response = $this->post('/personnages/creer/contexte', [
            'direction' => 'back',
            'nom' => 'Aragorn',
            'description' => 'Roi du Gondor',
            'historique_id' => $identifiants['historique_id'],
            'ideal_id' => $identifiants['ideal_id'],
            // Défaut, attachement et personnalité volontairement non
            // remplis : l'étape n'est pas terminée, mais on doit quand même
            // pouvoir reculer.
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect(route('creation.competences'));

        $donnees = session('personnage_creation');
        $this->assertSame('Aragorn', $donnees['nom']);
        $this->assertSame('Roi du Gondor', $donnees['description']);
        $this->assertSame($identifiants['historique_id'], $donnees['historique_id']);
        $this->assertSame($identifiants['ideal_id'], $donnees['ideal_id']);
        $this->assertNull($donnees['defaut_id']);
        $this->assertNull($donnees['attachement_id']);
        $this->assertNull($donnees['personnalite_id']);

        // Revenir sur l'étape contexte doit maintenant réafficher les choix
        // déjà faits plutôt qu'un formulaire vide.
        $this->get('/personnages/creer/contexte')
            ->assertOk()
            ->assertSee('Aragorn', false)
            ->assertSee('value="'.$identifiants['historique_id'].'" selected', false);
    }

    // En 5e, si l'historique choisi accorde une compétence déjà choisie pour
    // la classe, les deux ne se cumulent pas : il faut reprendre un choix
    // différent pour la classe plutôt que de perdre silencieusement une
    // maîtrise.
    public function test_le_contexte_refuse_un_historique_qui_fait_doublon_avec_les_competences_de_classe(): void
    {
        $this->utilisateurConnecte();
        (new HistoriquesSeeder)->run();
        // "Athlétisme" est choisi pour la classe (voir progresserJusquaCompetences)
        // et l'historique Soldat accorde justement Athlétisme + Intimidation.
        $this->progresserJusquaCompetences();
        $soldat = Historique::where('nom', 'Soldat')->firstOrFail();

        $response = $this->post('/personnages/creer/contexte', [
            'nom' => 'Aragorn',
            'outil' => 'Aucun',
            'historique_id' => $soldat->id,
            'ideal_id' => $soldat->ideaux()->first()->id,
            'defaut_id' => $soldat->defauts()->first()->id,
            'attachement_id' => $soldat->attachements()->first()->id,
            'personnalite_id' => $soldat->personnalites()->first()->id,
        ]);

        $response->assertRedirect(route('creation.competences'));
        $this->assertStringContainsString('Athlétisme', session('error'));
        // Rien n'a été écrasé : la classe garde ses compétences choisies,
        // l'historique n'a pas été enregistré.
        $this->assertSame(['Athlétisme', 'Perception'], array_values(session('personnage_creation')['competences']));
        $this->assertNull(session('personnage_creation')['historique_id']);
    }

    // L'alignement final combine désormais les 4 traits choisis (idéal,
    // défaut, attachement, personnalité) au lieu de ne dépendre que de
    // l'idéal : chaque trait tagué "vote" sur les deux axes et les votes
    // s'additionnent (voir CreationPersonnageController::calculateAlignement).
    public function test_l_alignement_final_combine_les_votes_de_plusieurs_traits_sur_le_meme_axe(): void
    {
        $this->utilisateurConnecte();
        (new HistoriquesSeeder)->run();
        $this->progresserJusquaCompetences();

        $criminel = Historique::where('nom', 'Criminel')->firstOrFail();
        // Idéal "Liberté" (Chaotique Neutre) + défaut "Je ne peux résister
        // à m'approprier..." (Chaotique) votent tous les deux Chaotique sur
        // l'axe loi ; l'attachement "Je rembourse toujours mes dettes..."
        // (Loyal) fait pencher une fois dans l'autre sens sans renverser la
        // tendance (+1+1-1 = +1, toujours Chaotique). Aucun des 4 traits ne
        // porte d'étiquette sur l'axe bien.
        $ideal = $criminel->ideaux()->where('valeur', 'Liberté')->firstOrFail();
        $defaut = $criminel->defauts()->where('valeur', 'like', '%approprier%')->firstOrFail();
        $attachement = $criminel->attachements()->where('valeur', 'like', '%dettes%')->firstOrFail();
        $personnalite = $criminel->personnalites()->where('valeur', 'like', '%plan de secours%')->firstOrFail();

        $response = $this->post('/personnages/creer/contexte', [
            'nom' => 'Aragorn',
            'outil' => 'Jeu de dés',
            'historique_id' => $criminel->id,
            'ideal_id' => $ideal->id,
            'defaut_id' => $defaut->id,
            'attachement_id' => $attachement->id,
            'personnalite_id' => $personnalite->id,
        ]);

        $response->assertRedirect(route('creation.confirmation'));
        // idéal (Chaotique) + défaut (Chaotique) => Chaotique sur l'axe loi ;
        // aucun des 4 traits ne porte d'étiquette sur l'axe bien => Neutre.
        $this->assertSame('Chaotique Neutre', session('personnage_creation')['alignement']);
    }

    public function test_l_alignement_final_peut_s_annuler_sur_un_axe_et_rester_tranche_sur_l_autre(): void
    {
        $this->utilisateurConnecte();
        (new HistoriquesSeeder)->run();
        $this->progresserJusquaCompetences();

        $criminel = Historique::where('nom', 'Criminel')->firstOrFail();
        // Idéal "Liberté" (Chaotique) et attachement "Je rembourse toujours
        // mes dettes..." (Loyal) s'annulent sur l'axe loi ; seule la
        // personnalité "La plus infime insulte..." (Mauvais) vote sur l'axe
        // bien.
        $ideal = $criminel->ideaux()->where('valeur', 'Liberté')->firstOrFail();
        $defaut = $criminel->defauts()->where('valeur', 'like', '%gentillesse%')->firstOrFail();
        $attachement = $criminel->attachements()->where('valeur', 'like', '%dettes%')->firstOrFail();
        $personnalite = $criminel->personnalites()->where('valeur', 'like', '%insulte%')->firstOrFail();

        $response = $this->post('/personnages/creer/contexte', [
            'nom' => 'Aragorn',
            'outil' => 'Jeu de cartes',
            'historique_id' => $criminel->id,
            'ideal_id' => $ideal->id,
            'defaut_id' => $defaut->id,
            'attachement_id' => $attachement->id,
            'personnalite_id' => $personnalite->id,
        ]);

        $response->assertRedirect(route('creation.confirmation'));
        $this->assertSame('Neutre Mauvais', session('personnage_creation')['alignement']);
    }

    public function test_l_alignement_final_est_neutre_si_aucun_trait_choisi_ne_penche_d_un_cote(): void
    {
        $this->utilisateurConnecte();
        (new HistoriquesSeeder)->run();
        $this->progresserJusquaCompetences();

        $artiste = Historique::where('nom', 'Artiste')->firstOrFail();
        // "Renommée" est le seul idéal de l'Artiste sans étiquette
        // Loyal/Chaotique/Bon/Mauvais, et les autres traits choisis n'en
        // portent pas non plus.
        $ideal = $artiste->ideaux()->where('valeur', 'Renommée')->firstOrFail();
        $defaut = $artiste->defauts()->where('valeur', 'like', '%compliments%')->firstOrFail();
        $attachement = $artiste->attachements()->where('valeur', 'like', '%admirateur%')->firstOrFail();
        $personnalite = $artiste->personnalites()->where('valeur', 'like', '%centre absolu%')->firstOrFail();

        $response = $this->post('/personnages/creer/contexte', [
            'nom' => 'Aragorn',
            'outil' => 'Luth',
            'historique_id' => $artiste->id,
            'ideal_id' => $ideal->id,
            'defaut_id' => $defaut->id,
            'attachement_id' => $attachement->id,
            'personnalite_id' => $personnalite->id,
        ]);

        $response->assertRedirect(route('creation.confirmation'));
        $this->assertSame('Neutre', session('personnage_creation')['alignement']);
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
        $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide());

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
        $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide(['nom' => 'Sans classe']));

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

    // Le personnage final cumule les compétences choisies pour sa classe et
    // celles, fixes, accordées par son historique (ici Acolyte : Intuition
    // et Religion — voir getIdentifiantsHistoriqueValide()) : ce ne sont
    // jamais seulement les 2 compétences choisies à l'étape Compétences.
    public function test_la_finalisation_cumule_les_competences_de_classe_et_d_historique(): void
    {
        $utilisateur = $this->utilisateurConnecte();
        $this->progresserJusquaContexte();

        $this->post('/personnages/creer');

        $personnage = $utilisateur->personnages()->where('nom', 'Aragorn')->firstOrFail();
        $personnage->load('listeCompetence');

        $this->assertEqualsCanonicalizing(
            ['Athlétisme', 'Perception', 'Intuition', 'Religion'],
            $personnage->listeCompetence->competences
        );
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
        $this->post('/personnages/creer/contexte', $this->getPayloadContexteValide());

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

    // --- API détails d'un historique ---

    public function test_get_historique_details_retourne_lhistorique_avec_ses_relations(): void
    {
        $this->utilisateurConnecte();
        (new HistoriquesSeeder)->run();
        $historique = Historique::first();

        $response = $this->getJson("/api/historiques/{$historique->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $historique->id, 'nom' => $historique->nom])
            ->assertJsonFragment(['id' => $historique->ideaux()->first()->id])
            ->assertJsonFragment(['id' => $historique->defauts()->first()->id])
            ->assertJsonFragment(['id' => $historique->attachements()->first()->id])
            ->assertJsonFragment(['id' => $historique->personnalites()->first()->id]);
    }

    public function test_get_historique_details_retourne_404_si_introuvable(): void
    {
        $this->utilisateurConnecte();

        $response = $this->getJson('/api/historiques/999999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Historique introuvable']);
    }
}
