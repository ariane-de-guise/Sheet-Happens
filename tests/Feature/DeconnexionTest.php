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

    /**
     * Teste que le badge d'accueil affiche le bouton de déconnexion pour un
     * utilisateur connecté.
     */
    public function test_la_page_d_accueil_affiche_le_bouton_de_deconnexion_pour_un_utilisateur_connecte(): void
    {
        $utilisateur = Utilisateur::factory()->create([
            'courriel' => 'samuel@exemple.com',
        ]);

        $this->actingAs($utilisateur)
            ->get('/')
            ->assertSee('samuel@exemple.com')
            ->assertSee('Déconnexion');
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
