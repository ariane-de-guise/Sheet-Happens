<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use App\Models\Historique;
use Database\Factories\ClasseFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\RaceFactory;
use Database\Factories\SousClasseFactory;
use Database\Factories\SousRaceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnageSheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_liste_est_vide_pour_un_utilisateur_sans_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $this->actingAs($utilisateur)
            ->get('/personnages')
            ->assertOk()
            ->assertSee("Vous n'avez pas encore de personnage.", false);
    }

    public function test_la_liste_affiche_les_personnages_de_l_utilisateur_connecte(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();

        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'nom' => 'Gimli',
        ]);

        PersonnageFactory::new()->create([
            'utilisateur_id' => $autre->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'nom' => 'Boromir',
        ]);

        $this->actingAs($utilisateur)
            ->get('/personnages')
            ->assertOk()
            ->assertSee('Gimli')
            ->assertDontSee('Boromir');
    }

    public function test_un_proprietaire_peut_afficher_la_fiche_de_son_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'alignement' => 'Loyal bon',
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}")
            ->assertOk()
            ->assertSee($personnage->nom)
            ->assertSee('Loyal bon');
    }

    public function test_un_autre_utilisateur_ne_peut_pas_voir_la_fiche(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);

        $this->actingAs($autre)
            ->get("/personnages/{$personnage->id}")
            ->assertForbidden();
    }

    public function test_un_proprietaire_peut_afficher_le_formulaire_edition(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertOk()
            ->assertDontSee('onchange="this.form.submit()"', false);
    }

    public function test_une_competence_d_historique_est_verrouillee_dans_le_formulaire(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $historique = Historique::create([
            'nom' => 'Sage',
            'competences' => ['Religion'],
        ]);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'historique_id' => $historique->id,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertOk()
            ->assertSee('value="Religion"', false)
            ->assertSee('disabled', false);
    }

    public function test_une_classe_avec_quatre_competences_peut_en_remplacer_une(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create([
            'nombre_competences' => 4,
            'competences_disponibles' => ['Acrobaties', 'Athlétisme', 'Nature', 'Perception', 'Survie'],
        ]);
        $historique = Historique::create([
            'nom' => 'Sage',
            'competences' => ['Religion', 'Histoire'],
        ]);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'historique_id' => $historique->id,
        ]);
        $competences = ['Acrobaties', 'Athlétisme', 'Nature', 'Survie', 'Religion', 'Histoire'];
        $personnage->listeCompetence->update(['competences' => $competences]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertOk()
            ->assertSee('Compétences de classe (max 4)', false)
            ->assertSee('Compétences d’historique incluses et non modifiables : Religion, Histoire', false);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'competences' => ['Acrobaties', 'Athlétisme', 'Perception', 'Survie', 'Religion', 'Histoire'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertRedirect(route('personnages.show', $personnage));
        $this->assertSame(
            ['Acrobaties', 'Athlétisme', 'Perception', 'Survie', 'Religion', 'Histoire'],
            $personnage->refresh()->listeCompetence->competences,
        );
    }

    public function test_un_autre_utilisateur_ne_peut_pas_modifier_le_personnage(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);

        $this->actingAs($autre)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertForbidden();

        $this->actingAs($autre)
            ->patch("/personnages/{$personnage->id}", [])
            ->assertForbidden();
    }

    private function donneesMiseAJour(array $overrides = []): array
    {
        return array_merge([
            'nom' => 'Aragorn Mis à jour',
            'niveau' => 1,
            'alignement' => 'Neutre bon',
            'description' => 'Roi du Gondor',
            'point_de_vie' => 10,
            'outil' => 'Outils de voleur',
            'langues' => ['Commun', 'Elfe'],
            'equipements' => ['Épée longue', 'Bouclier'],
            'force' => 10,
            'dexterite' => 10,
            'constitution' => 10,
            'intelligence' => 10,
            'sagesse' => 10,
            'charisme' => 10,
        ], $overrides);
    }

    public function test_un_proprietaire_peut_mettre_a_jour_son_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 4,
            'point_de_vie' => 20,
            'force' => 12,
            'competences' => ['Athlétisme', 'Perception'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertRedirect(route('personnages.show', $personnage));
        $response->assertSessionHas('success');

        $personnage->refresh();
        $this->assertSame('Aragorn Mis à jour', $personnage->nom);
        $this->assertSame(20, $personnage->point_de_vie);
        $this->assertSame('Outils de voleur', $personnage->outil);
        $this->assertSame(['Commun', 'Elfe'], $personnage->langues);
        $this->assertSame(['Épée longue', 'Bouclier'], $personnage->equipement);
        $this->assertSame(['Athlétisme', 'Perception'], $personnage->listeCompetence->fresh()->competences);
        $this->assertSame(12, $personnage->abilites->fresh()->force);
    }

    public function test_un_proprietaire_peut_modifier_uniquement_le_niveau(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'niveau' => 1,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", ['niveau' => 2]);

        $response->assertRedirect(route('personnages.show', $personnage));
        $this->assertSame(2, $personnage->refresh()->niveau);
    }

    public function test_un_proprietaire_peut_modifier_uniquement_les_points_de_vie(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'point_de_vie' => 8,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", ['point_de_vie' => 25]);

        $response->assertSessionHasErrors(['point_de_vie']);
        $this->assertSame(8, $personnage->refresh()->point_de_vie);
    }

    public function test_la_mise_a_jour_efface_les_listes_quand_toutes_les_options_sont_decoches(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'langues' => ['Commun'],
            'equipement' => ['Épée longue'],
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $personnage->race_id,
            'classe_id' => $personnage->classe_id,
            'langues' => [],
            'equipements' => [],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $personnage->refresh();
        $this->assertSame([], $personnage->langues);
        $this->assertSame([], $personnage->equipement);
        $this->assertSame([], $personnage->listeCompetence->fresh()->competences);
    }

    public function test_la_mise_a_jour_conserve_des_competences_existantes_au_dela_de_la_limite(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'niveau' => 1,
        ]);
        $competences = ['Athlétisme', 'Perception', 'Nature'];
        $personnage->listeCompetence->update(['competences' => $competences]);

        $donnees = $this->donneesMiseAJour([
            'nom' => 'Nom conservé',
            'race_id' => $personnage->race_id,
            'classe_id' => $personnage->classe_id,
            'competences' => $competences,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertRedirect(route('personnages.show', $personnage));
        $personnage->refresh();
        $this->assertSame('Nom conservé', $personnage->nom);
        $this->assertSame($competences, $personnage->listeCompetence->fresh()->competences);
    }

    public function test_les_statistiques_pv_et_niveau_sont_acceptes_avec_un_asi_valide(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'niveau' => 3,
            'point_de_vie' => 10,
        ]);
        $competences = ['Athlétisme', 'Perception', 'Nature'];
        $personnage->listeCompetence->update(['competences' => $competences]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $personnage->race_id,
            'classe_id' => $personnage->classe_id,
            'niveau' => 4,
            'point_de_vie' => 20,
            'force' => 12,
            'competences' => $competences,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertRedirect(route('personnages.show', $personnage));
        $personnage->refresh();
        $this->assertSame(4, $personnage->niveau);
        $this->assertSame(20, $personnage->point_de_vie);
        $this->assertSame(12, $personnage->abilites->fresh()->force);
        $this->assertSame($competences, $personnage->listeCompetence->fresh()->competences);
    }

    public function test_la_mise_a_jour_neutralise_une_sous_race_en_cas_d_incoherence_de_type(): void
    {
        // La règle de validation "exists" compare race_id via SQL (typage
        // souple), mais le contrôleur compare ensuite avec `!==` (typage
        // strict). Envoyer race_id sous forme de chaîne fait donc passer la
        // validation tout en déclenchant la vérification manuelle du
        // contrôleur, qui neutralise alors la sous-race sans lever d'erreur.
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => (string) $race->id,
            'classe_id' => $classe->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $personnage->refresh();
        $this->assertNull($personnage->sous_race_id);
    }

    public function test_la_mise_a_jour_accepte_une_sous_race_valide(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $personnage->refresh();
        $this->assertSame($sousRace->id, $personnage->sous_race_id);
    }

    public function test_la_mise_a_jour_ignore_une_sous_classe_dont_le_niveau_requis_est_trop_eleve(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 3]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
            'niveau' => 1,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $personnage->refresh();
        $this->assertNull($personnage->sous_classe_id);
    }

    public function test_la_mise_a_jour_accepte_une_sous_classe_valide_au_bon_niveau(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
            'niveau' => 1,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $personnage->refresh();
        $this->assertSame($sousClasse->id, $personnage->sous_classe_id);
    }

    public function test_la_mise_a_jour_respecte_la_limite_de_competences_selon_le_niveau(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
            'competences' => ['Athlétisme', 'Perception', 'Nature'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasErrors(['competences']);
    }

    public function test_la_mise_a_jour_respecte_la_limite_de_langues_selon_le_niveau(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
            'langues' => ['Commun', 'Elfe', 'Nain'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasErrors(['langues']);
    }
}
