<?php

namespace Tests\Unit;

use App\Models\Defaut;
use App\Models\Historique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefautTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_defaut_appartient_a_un_historique(): void
    {
        $historique = Historique::create(['nom' => 'Acolyte']);
        $defaut = Defaut::create([
            'historique_id' => $historique->id,
            'valeur' => 'Je juge sévèrement les autres, moi le premier.',
        ]);

        $this->assertTrue($defaut->historique->is($historique));
    }
}
