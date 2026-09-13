<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
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
            ->assertOk();
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
            'point_de_vie' => 20,
            'force' => 16,
            'dexterite' => 12,
            'constitution' => 14,
            'intelligence' => 10,
            'sagesse' => 12,
            'charisme' => 14,
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
            'competences' => ['Athlétisme', 'Perception'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertRedirect(route('personnages.show', $personnage));
        $response->assertSessionHas('success');

        $personnage->refresh();
        $this->assertSame('Aragorn Mis à jour', $personnage->nom);
        $this->assertSame(20, $personnage->point_de_vie);
        $this->assertSame(['Athlétisme', 'Perception'], $personnage->listeCompetence->fresh()->competences);
        $this->assertSame(16, $personnage->abilites->fresh()->force);
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
