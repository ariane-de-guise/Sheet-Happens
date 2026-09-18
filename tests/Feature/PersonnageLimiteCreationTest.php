<?php

namespace Tests\Feature;

use App\Models\Personnage;
use App\Models\Utilisateur;
use Database\Factories\ClasseFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\RaceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnageLimiteCreationTest extends TestCase
{
    use RefreshDatabase;

    private function payloadValide(): array
    {
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);

        return [
            'nom' => 'Nouveau personnage',
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'point_de_vie' => 12,
            'abilites' => [
                'force' => 10,
                'dexterite' => 12,
                'constitution' => 12,
                'intelligence' => 10,
                'sagesse' => 10,
                'charisme' => 10,
            ],
        ];
    }

    private function creerPersonnagesPour(Utilisateur $utilisateur, int $nombre): void
    {
        PersonnageFactory::new()->count($nombre)->create([
            'utilisateur_id' => $utilisateur->id,
        ]);
    }

    // --- Accès à l'assistant de création (middleware) ---

    public function test_un_utilisateur_avec_moins_de_cinq_personnages_peut_acceder_a_la_creation(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $this->creerPersonnagesPour($utilisateur, 4);

        $this->actingAs($utilisateur)
            ->get('/personnages/creer')
            ->assertOk();
    }

    public function test_un_utilisateur_avec_cinq_personnages_est_bloque_a_l_entree_de_la_creation(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $this->creerPersonnagesPour($utilisateur, Personnage::MAX_PAR_UTILISATEUR);

        $reponse = $this->actingAs($utilisateur)->get('/personnages/creer');

        $reponse->assertRedirect(route('personnages.index'));
        $reponse->assertSessionHas('error');
    }

    public function test_toutes_les_etapes_de_lassistant_sont_bloquees_quand_la_limite_est_atteinte(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $this->creerPersonnagesPour($utilisateur, Personnage::MAX_PAR_UTILISATEUR);

        $this->actingAs($utilisateur)
            ->get('/personnages/creer/classe')
            ->assertRedirect(route('personnages.index'));

        $this->actingAs($utilisateur)
            ->get('/personnages/creer/confirmation')
            ->assertRedirect(route('personnages.index'));
    }

    public function test_la_limite_est_evaluee_par_utilisateur(): void
    {
        $utilisateurAuMax = Utilisateur::factory()->create();
        $this->creerPersonnagesPour($utilisateurAuMax, Personnage::MAX_PAR_UTILISATEUR);

        $autreUtilisateur = Utilisateur::factory()->create();

        $this->actingAs($autreUtilisateur)
            ->get('/personnages/creer')
            ->assertOk();
    }

    // --- Point de création effectif (service, via l'API) ---

    public function test_le_cinquieme_personnage_peut_etre_cree(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $this->creerPersonnagesPour($utilisateur, Personnage::MAX_PAR_UTILISATEUR - 1);

        $reponse = $this->actingAs($utilisateur)
            ->postJson('/api/personnages', $this->payloadValide());

        $reponse->assertStatus(201);
        $this->assertSame(
            Personnage::MAX_PAR_UTILISATEUR,
            Personnage::where('utilisateur_id', $utilisateur->id)->count()
        );
    }

    public function test_le_sixieme_personnage_est_refuse(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $this->creerPersonnagesPour($utilisateur, Personnage::MAX_PAR_UTILISATEUR);

        $reponse = $this->actingAs($utilisateur)
            ->postJson('/api/personnages', $this->payloadValide());

        $reponse->assertStatus(422)->assertJsonValidationErrors(['utilisateur_id']);
        $this->assertSame(
            Personnage::MAX_PAR_UTILISATEUR,
            Personnage::where('utilisateur_id', $utilisateur->id)->count()
        );
    }

    public function test_supprimer_un_personnage_libere_a_nouveau_la_creation(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $this->creerPersonnagesPour($utilisateur, Personnage::MAX_PAR_UTILISATEUR);
        $personnage = Personnage::where('utilisateur_id', $utilisateur->id)->first();

        $this->actingAs($utilisateur)->delete("/personnages/{$personnage->id}");

        $this->actingAs($utilisateur)
            ->get('/personnages/creer')
            ->assertOk();

        $reponse = $this->actingAs($utilisateur)
            ->postJson('/api/personnages', $this->payloadValide());

        $reponse->assertStatus(201);
    }

    // --- Affichage sur la liste ---

    public function test_le_bouton_de_creation_est_remplace_quand_la_limite_est_atteinte(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $this->creerPersonnagesPour($utilisateur, Personnage::MAX_PAR_UTILISATEUR);

        $this->actingAs($utilisateur)
            ->get('/personnages')
            ->assertOk()
            ->assertSee('Limite de personnages atteinte')
            ->assertDontSee('Créez un personnage');
    }

    public function test_le_bouton_de_creation_est_visible_sous_la_limite(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $this->creerPersonnagesPour($utilisateur, Personnage::MAX_PAR_UTILISATEUR - 1);

        $this->actingAs($utilisateur)
            ->get('/personnages')
            ->assertOk()
            ->assertSee('Créez un personnage')
            ->assertDontSee('Limite de personnages atteinte');
    }
}
