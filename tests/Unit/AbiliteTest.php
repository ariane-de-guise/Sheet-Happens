<?php

namespace Tests\Unit;

use Database\Factories\AbiliteFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbiliteTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_abilite_peut_etre_creee_avec_ses_caracteristiques(): void
    {
        $abilite = AbiliteFactory::new()->create([
            'force' => 15,
            'dexterite' => 14,
            'constitution' => 13,
            'intelligence' => 12,
            'sagesse' => 10,
            'charisme' => 8,
        ]);

        $this->assertDatabaseHas('abilites', [
            'id' => $abilite->id,
            'force' => 15,
            'dexterite' => 14,
            'constitution' => 13,
            'intelligence' => 12,
            'sagesse' => 10,
            'charisme' => 8,
        ]);
    }
}
