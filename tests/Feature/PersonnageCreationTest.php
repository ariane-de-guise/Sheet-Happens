<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnageCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_refuse_un_payload_incomplet(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $response = $this->actingAs($utilisateur)->postJson('/api/personnages', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'nom',
                'race_id',
                'classe_id',
                'point_de_vie',
                'abilites',
            ]);
    }
}
