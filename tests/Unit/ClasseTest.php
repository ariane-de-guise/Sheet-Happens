<?php

namespace Tests\Unit;

use Database\Factories\ClasseFactory;
use Database\Factories\SousClasseFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClasseTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_classe_possede_plusieurs_sous_classes(): void
    {
        $classe = ClasseFactory::new()->create();
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $this->assertTrue($classe->sousClasses->contains($sousClasse));
    }
}
