<?php

namespace Tests\Unit;

use Database\Factories\ObjetFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\PersonnageObjetFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnageObjetTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_ligne_d_inventaire_appartient_a_un_personnage_et_a_un_objet(): void
    {
        $personnage = PersonnageFactory::new()->create();
        $objet = ObjetFactory::new()->create();
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $objet->id,
        ]);

        $this->assertTrue($ligne->personnage->is($personnage));
        $this->assertTrue($ligne->objet->is($objet));
    }
}
