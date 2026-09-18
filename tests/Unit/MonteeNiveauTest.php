<?php

namespace Tests\Unit;

use App\Models\MonteeNiveau;
use Database\Factories\PersonnageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonteeNiveauTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_montee_de_niveau_appartient_a_un_personnage(): void
    {
        $personnage = PersonnageFactory::new()->create();

        $montee = MonteeNiveau::create([
            'personnage_id' => $personnage->id,
            'niveau_depart' => 1,
            'niveau_arrivee' => 2,
            'choix' => ['test' => true],
            'changements' => ['niveau' => 2],
        ]);

        $this->assertTrue($montee->personnage->is($personnage));
    }
}
