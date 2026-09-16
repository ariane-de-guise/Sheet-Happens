<?php

namespace Tests\Unit;

use App\Models\Historique;
use App\Models\Ideal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdealTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_ideal_appartient_a_un_historique(): void
    {
        $historique = Historique::create(['nom' => 'Acolyte']);
        $ideal = Ideal::create([
            'historique_id' => $historique->id,
            'valeur' => 'Tradition',
            'alignement' => 'Loyal',
            'description' => 'Les coutumes anciennes doivent être préservées.',
        ]);

        $this->assertTrue($ideal->historique->is($historique));
    }
}
