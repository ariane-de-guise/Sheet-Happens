<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnexionTest extends TestCase
{
    // Réinitialise la base de données après chaque test pour repartir à zéro
    use RefreshDatabase;

    /**
     * Teste que la page du formulaire de connexion s'affiche correctement.
     */
    public function test_la_page_de_connexion_est_accessible(): void
    {
        $reponse = $this->get('/connexion');

        $reponse->assertStatus(200);
        $reponse->assertSee('Connexion');
    }

    /**
     * Teste qu'un utilisateur peut se connecter avec des identifiants valides.
     */
    public function test_un_utilisateur_peut_se_connecter_avec_des_identifiants_valides(): void
    {
        $utilisateur = Utilisateur::factory()->create([
            'courriel' => 'samuel@exemple.com',
            'mot_de_passe' => 'Secret1234!',
        ]);

        $donnees = [
            'courriel' => 'samuel@exemple.com',
            'mot_de_passe' => 'Secret1234!',
        ];

        $reponse = $this->post('/connexion', $donnees);

        $reponse->assertRedirect('/');
        $this->assertAuthenticatedAs($utilisateur);
    }

    /**
     * Teste que la connexion échoue si le mot de passe est incorrect.
     */
    public function test_la_connexion_echoue_si_le_mot_de_passe_est_incorrect(): void
    {
        Utilisateur::factory()->create([
            'courriel' => 'samuel@exemple.com',
            'mot_de_passe' => 'Secret1234!',
        ]);

        $donnees = [
            'courriel' => 'samuel@exemple.com',
            'mot_de_passe' => 'MauvaisMotDePasse!',
        ];

        $reponse = $this->post('/connexion', $donnees);

        $reponse->assertSessionHasErrors(['connexion']);
        $this->assertGuest();
    }

    /**
     * Teste que la connexion échoue si le courriel n'existe pas.
     */
    public function test_la_connexion_echoue_si_le_courriel_est_inconnu(): void
    {
        $donnees = [
            'courriel' => 'inconnu@exemple.com',
            'mot_de_passe' => 'Secret1234!',
        ];

        $reponse = $this->post('/connexion', $donnees);

        $reponse->assertSessionHasErrors(['connexion']);
        $this->assertGuest();
    }

    /**
     * Teste que le courriel et le mot de passe sont requis.
     */
    public function test_la_connexion_echoue_si_les_champs_sont_vides(): void
    {
        $reponse = $this->post('/connexion', []);

        $reponse->assertSessionHasErrors(['courriel', 'mot_de_passe']);
        $this->assertGuest();
    }
}
