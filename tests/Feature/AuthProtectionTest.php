<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invite_est_redirige_vers_la_connexion_pour_les_pages_protegees(): void
    {
        $this->get('/personnages')->assertRedirect(route('connexion.create'));
        $this->get('/profil')->assertRedirect(route('connexion.create'));
    }

    public function test_un_utilisateur_connecte_ne_peut_pas_revenir_aux_formulaires_auth(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $this->actingAs($utilisateur)
            ->get('/connexion')
            ->assertRedirect(route('personnages.index'));

        $this->actingAs($utilisateur)
            ->get('/inscription')
            ->assertRedirect(route('personnages.index'));
    }

    public function test_un_utilisateur_connecte_qui_visite_l_accueil_est_redirige_vers_personnages(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $this->actingAs($utilisateur)
            ->get('/')
            ->assertRedirect(route('personnages.index'));
    }
}
