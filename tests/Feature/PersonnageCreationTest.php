<?php

namespace Tests\Feature;

use Tests\TestCase;

class PersonnageCreationTest extends TestCase
{
    public function test_creation_refuse_un_payload_incomplet(): void
    {
        $response = $this->postJson('/api/personnages', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'utilisateur_id',
                'nom',
                'race_id',
                'classe_id',
                'point_de_vie',
                'abilites',
            ]);
    }
}