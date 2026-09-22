<?php

use App\Models\Personnage;
use App\Models\Utilisateur;
use Database\Factories\PersonnageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonnageDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_proprietaire_peut_supprimer_son_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
        ]);

        $reponse = $this->actingAs($utilisateur)
            ->delete("/personnages/{$personnage->id}");

        $reponse->assertRedirect(route('personnages.index'));
        $reponse->assertSessionHas('success');

        $this->assertDatabaseMissing('personnages', ['id' => $personnage->id]);
    }

    public function test_la_suppression_efface_aussi_les_donnees_associees_au_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
        ]);

        $abilitesId = $personnage->abilites_id;
        $listeCompetenceId = $personnage->liste_competence_id;

        $this->actingAs($utilisateur)
            ->delete("/personnages/{$personnage->id}");

        $this->assertDatabaseMissing('abilites', ['id' => $abilitesId]);
        $this->assertDatabaseMissing('listes_competences', ['id' => $listeCompetenceId]);
    }

    public function test_le_personnage_supprime_n_apparait_plus_dans_la_liste(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'nom' => 'Personnage à supprimer',
        ]);

        $this->actingAs($utilisateur)
            ->delete("/personnages/{$personnage->id}");

        $this->actingAs($utilisateur)
            ->get('/personnages')
            ->assertOk()
            ->assertDontSee('Personnage à supprimer');
    }

    public function test_un_autre_utilisateur_ne_peut_pas_supprimer_le_personnage(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $proprietaire->id,
        ]);

        $reponse = $this->actingAs($autre)
            ->delete("/personnages/{$personnage->id}");

        $reponse->assertForbidden();
        $this->assertDatabaseHas('personnages', ['id' => $personnage->id]);
    }

    public function test_un_invite_ne_peut_pas_supprimer_un_personnage(): void
    {
        $personnage = PersonnageFactory::new()->create();

        $reponse = $this->delete("/personnages/{$personnage->id}");

        $reponse->assertRedirect('/connexion');
        $this->assertDatabaseHas('personnages', ['id' => $personnage->id]);
    }

    public function test_la_suppression_d_un_personnage_inexistant_retourne_une_erreur_404(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $reponse = $this->actingAs($utilisateur)
            ->delete('/personnages/999999');

        $reponse->assertNotFound();
    }

    public function test_la_suppression_libere_de_l_espace_pour_creer_un_nouveau_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
        ]);

        $this->actingAs($utilisateur)->delete("/personnages/{$personnage->id}");

        $nouveauPersonnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
        ]);

        $this->assertDatabaseHas('personnages', ['id' => $nouveauPersonnage->id]);
        $this->assertSame(1, Personnage::where('utilisateur_id', $utilisateur->id)->count());
    }

    public function test_la_suppression_supprime_aussi_la_photo_du_personnage(): void
    {
        Storage::fake('public');

        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)->patch(route('personnages.photo', $personnage), [
            'photo' => UploadedFile::fake()->image('portrait.jpg'),
        ]);
        $chemin = $personnage->refresh()->photo;

        $this->actingAs($utilisateur)->delete("/personnages/{$personnage->id}");

        Storage::disk('public')->assertMissing($chemin);
    }

    public function test_la_suppression_d_un_personnage_ne_supprime_pas_les_donnees_d_un_autre_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnageASupprimer = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
        ]);
        $autrePersonnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
        ]);

        $this->actingAs($utilisateur)
            ->delete("/personnages/{$personnageASupprimer->id}");

        $this->assertDatabaseHas('personnages', ['id' => $autrePersonnage->id]);
        $this->assertDatabaseHas('abilites', ['id' => $autrePersonnage->abilites_id]);
        $this->assertDatabaseHas('listes_competences', ['id' => $autrePersonnage->liste_competence_id]);
    }
}
