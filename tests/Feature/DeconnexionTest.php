<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeconnexionTest extends TestCase
{
    // Réinitialise la base de données après chaque test pour repartir à zéro
    use RefreshDatabase;

    /**
     * Teste qu'un utilisateur connecté peut se déconnecter.
     */
    public function test_un_utilisateur_connecte_peut_se_deconnecter(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $reponse = $this->actingAs($utilisateur)->post('/deconnexion');

        $reponse->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_le_bouton_de_deconnexion_est_visible_uniquement_sur_le_profil(): void
    {
        $utilisateur = Utilisateur::factory()->create([
            'courriel' => 'samuel@exemple.com',
        ]);

        $this->actingAs($utilisateur)
            ->get('/personnages')
            ->assertSee('samuel@exemple.com')
            ->assertSee('Afficher le profil')
            ->assertDontSee('Se déconnecter');

        $this->actingAs($utilisateur)
            ->get('/profil')
            ->assertSee('samuel@exemple.com')
            ->assertSee('Se déconnecter');
    }

    /**
     * Teste que le badge d'accueil n'affiche pas le bouton de déconnexion
     * pour un invité.
     */
    public function test_la_page_d_accueil_n_affiche_pas_le_bouton_de_deconnexion_pour_un_invite(): void
    {
        $this->get('/')
            ->assertSee('Non connecté')
            ->assertDontSee('Déconnexion');
    }
}
