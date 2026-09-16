<?php

namespace Tests\Unit;

use App\Models\Historique;
use App\Models\Personnalite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnaliteTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_personnalite_appartient_a_un_historique(): void
    {
        $historique = Historique::create(['nom' => 'Acolyte']);
        $personnalite = Personnalite::create([
            'historique_id' => $historique->id,
            'valeur' => 'Je cite (souvent mal) des textes sacrés dans presque toutes les situations.',
        ]);

        $this->assertTrue($personnalite->historique->is($historique));
    }
}
