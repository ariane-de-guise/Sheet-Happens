<?php

namespace Tests\Unit;

use Database\Factories\ClasseFactory;
use Database\Factories\SousClasseFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SousClasseTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_sous_classe_appartient_a_une_classe(): void
    {
        $classe = ClasseFactory::new()->create();
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $this->assertTrue($sousClasse->classe->is($classe));
    }
}
