<?php

namespace Tests\Unit;

use App\Models\ListeCompetence;
use Database\Factories\ListeCompetenceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListeCompetenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_competences_sont_castees_en_tableau(): void
    {
        $liste = ListeCompetenceFactory::new()->create([
            'competences' => ['Athlétisme', 'Perception'],
        ]);

        $liste = ListeCompetence::find($liste->id);

        $this->assertIsArray($liste->competences);
        $this->assertSame(['Athlétisme', 'Perception'], $liste->competences);
    }
}
