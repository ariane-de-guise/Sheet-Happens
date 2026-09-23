<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Race;
use App\Models\Utilisateur;
use Database\Factories\ClasseFactory;
use Database\Factories\RaceFactory;
use Database\Factories\SousClasseFactory;
use Database\Factories\SousRaceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnageApiTest extends TestCase
{
    use RefreshDatabase;

    private function payloadValide(): array
    {
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);

        return [
            'nom' => 'Legolas',
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'point_de_vie' => 12,
            'abilites' => [
                'force' => 10,
                'dexterite' => 18,
                'constitution' => 12,
                'intelligence' => 10,
                'sagesse' => 14,
                'charisme' => 10,
            ],
        ];
    }

    public function test_un_utilisateur_connecte_peut_creer_un_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $payload = $this->payloadValide();

        $response = $this->actingAs($utilisateur)->postJson('/api/personnages', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['nom' => 'Legolas']);

        $this->assertDatabaseHas('personnages', [
            'nom' => 'Legolas',
            'utilisateur_id' => $utilisateur->id,
        ]);
    }

    public function test_la_creation_accepte_une_sous_race_et_une_sous_classe_valides(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $payload = $this->payloadValide();

        $race = Race::find($payload['race_id']);
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $classe = Classe::find($payload['classe_id']);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $payload['sous_race_id'] = $sousRace->id;
        $payload['sous_classe_id'] = $sousClasse->id;
        $payload['niveau'] = 1;
        $payload['competences'] = ['Perception', 'Discretion'];

        $response = $this->actingAs($utilisateur)->postJson('/api/personnages', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('personnages', [
            'sous_race_id' => $sousRace->id,
            'sous_classe_id' => $sousClasse->id,
        ]);
    }

    public function test_la_creation_refuse_une_sous_race_qui_ne_correspond_pas_a_la_race(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $payload = $this->payloadValide();

        $autreRace = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($autreRace)->create();
        $payload['sous_race_id'] = $sousRace->id;

        $response = $this->actingAs($utilisateur)->postJson('/api/personnages', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['sous_race_id']);
    }

    public function test_la_creation_refuse_une_sous_classe_qui_ne_correspond_pas_a_la_classe(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $payload = $this->payloadValide();

        $autreClasse = ClasseFactory::new()->create();
        $sousClasse = SousClasseFactory::new()->pourClasse($autreClasse)->create();
        $payload['sous_classe_id'] = $sousClasse->id;

        $response = $this->actingAs($utilisateur)->postJson('/api/personnages', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['sous_classe_id']);
    }

    public function test_un_invite_ne_peut_pas_creer_de_personnage(): void
    {
        $payload = $this->payloadValide();

        $response = $this->postJson('/api/personnages', $payload);

        $response->assertStatus(401);
    }
}
