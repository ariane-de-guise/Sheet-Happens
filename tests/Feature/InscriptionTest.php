<?php

namespace Tests\Feature;

use App\Http\Resources\UtilisateurResource;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InscriptionTest extends TestCase
{
    // Réinitialise la base de données après chaque test pour repartir à zéro
    use RefreshDatabase;

    /**
     * Teste que la page du formulaire s'affiche correctement.
     */
    public function test_la_page_d_inscription_est_accessible(): void
    {
        $reponse = $this->get('/inscription');

        $reponse->assertStatus(200);
        $reponse->assertSee('Création de compte');
    }

    /**
     * Teste qu'un utilisateur peut créer un compte avec des données valides.
     */
    public function test_un_utilisateur_peut_s_inscrire_avec_des_donnees_valides(): void
    {
        $donnees = [
            'courriel' => 'samuel@exemple.com',
            'mot_de_passe' => 'Secret1234!',
            'confirmation_mot_de_passe' => 'Secret1234!',
        ];

        $reponse = $this->post('/inscription', $donnees);

        // 1. Vérifie la redirection vers l'accueil
        $reponse->assertRedirect('/personnages');

        // 2. Vérifie que la ligne existe dans la table 'utilisateurs'
        $this->assertDatabaseHas('utilisateurs', [
            'courriel' => 'samuel@exemple.com',
        ]);

        // 3. Vérifie que le mot de passe est bien haché et vérifiable
        $utilisateur = Utilisateur::where('courriel', 'samuel@exemple.com')->first();
        $this->assertNotNull($utilisateur);
        $this->assertTrue(Hash::check('Secret1234!', $utilisateur->mot_de_passe));
    }

    /**
     * Teste que l'inscription échoue si la confirmation ne correspond pas.
     */
    public function test_l_inscription_echoue_si_les_mots_de_passe_ne_correspondent_pas(): void
    {
        $donnees = [
            'courriel' => 'erreur@exemple.com',
            'mot_de_passe' => 'Secret1234!',
            'confirmation_mot_de_passe' => 'AutreChose123!',
        ];

        $reponse = $this->post('/inscription', $donnees);

        // Vérifie la présence d'une erreur de validation sur mot_de_passe
        $reponse->assertSessionHasErrors(['mot_de_passe']);

        // Vérifie qu'aucun enregistrement n'a été inséré
        $this->assertDatabaseMissing('utilisateurs', [
            'courriel' => 'erreur@exemple.com',
        ]);
    }

    /**
     * Teste le rejet d'un courriel déjà utilisé (contrainte unique).
     */
    public function test_l_inscription_echoue_si_le_courriel_existe_deja(): void
    {
        // On crée un premier utilisateur
        Utilisateur::factory()->create([
            'courriel' => 'existant@exemple.com',
        ]);

        $donnees = [
            'courriel' => 'existant@exemple.com',
            'mot_de_passe' => 'Secret1234!',
            'confirmation_mot_de_passe' => 'Secret1234!',
        ];

        $reponse = $this->post('/inscription', $donnees);

        $reponse->assertSessionHasErrors(['courriel']);
    }

    public function test_utilisateur_resource_retourne_la_bonne_structure(): void
    {
        $utilisateur = Utilisateur::factory()->create([
            'courriel' => 'ressource@exemple.com',
        ]);

        $resource = new UtilisateurResource($utilisateur);
        $donnees = $resource->toArray(request());

        $this->assertEquals($utilisateur->id, $donnees['id']);
        $this->assertEquals('ressource@exemple.com', $donnees['courriel']);

        // Clé par défaut générée par Laravel dans la ressource
        $this->assertArrayHasKey('created_at', $donnees);
    }

    public function test_inscription_via_api_retourne_utilisateur_resource(): void
    {
        $utilisateur = Utilisateur::factory()->create([
            'courriel' => 'api@exemple.com',
        ]);

        $reponse = (new UtilisateurResource($utilisateur))->response();

        // Laravel renvoie 201 Created car le modèle a été créé juste avant
        $this->assertEquals(201, $reponse->getStatusCode());
        $this->assertEquals('api@exemple.com', $reponse->getData()->data->courriel);
    }

    public function test_get_auth_password_retourne_le_mot_de_passe_hache(): void
    {
        $utilisateur = new \App\Models\Utilisateur([
            'mot_de_passe' => 'Secret1234!',
        ]);

        // 1. Vérifie que la chaîne hachée valide bien le mot de passe d'origine
        $this->assertTrue(Hash::check('Secret1234!', $utilisateur->getAuthPassword()));

        // 2. Ou vérifie directement que getAuthPassword() retourne bien l'attribut 'mot_de_passe'
        $this->assertSame($utilisateur->mot_de_passe, $utilisateur->getAuthPassword());
    }

}
