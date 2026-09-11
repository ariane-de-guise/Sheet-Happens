<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_connecte_peut_consulter_son_profil(): void
    {
        $utilisateur = Utilisateur::factory()->create(['courriel' => 'moi@exemple.com']);

        $this->actingAs($utilisateur)
            ->get('/profil')
            ->assertOk()
            ->assertSee('moi@exemple.com')
            ->assertSee('Se déconnecter');
    }

    public function test_le_profil_affiche_seulement_les_donnees_de_l_utilisateur_connecte(): void
    {
        $utilisateur = Utilisateur::factory()->create(['courriel' => 'connecte@exemple.com']);
        Utilisateur::factory()->create(['courriel' => 'autre@exemple.com']);

        $this->actingAs($utilisateur)
            ->get('/profil')
            ->assertSee('connecte@exemple.com')
            ->assertDontSee('autre@exemple.com');
    }

    public function test_un_utilisateur_non_connecte_ne_peut_pas_consulter_un_profil(): void
    {
        $this->get('/profil')->assertRedirect(route('connexion.create'));
    }


    public function test_un_utilisateur_connecte_ne_peut_pas_revenir_a_l_inscription(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $reponse = $this->actingAs($utilisateur)->get('/inscription');

        $reponse->assertRedirect(route('personnages.index'));
    }

    public function test_la_deconnexion_retourne_a_l_inscription(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $this->actingAs($utilisateur)
            ->post('/deconnexion')
            ->assertRedirect(route('welcome'));

        $this->assertGuest();
    }

    public function test_le_profil_permet_de_revenir_aux_personnages(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $this->actingAs($utilisateur)
            ->get('/profil')
            ->assertSee(route('personnages.index', absolute: false), false);
    }
}
