<?php

namespace Tests\Unit;

use Database\Factories\RaceFactory;
use Database\Factories\SousRaceFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_race_possede_plusieurs_sous_races(): void
    {
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();

        $this->assertTrue($race->sousRaces->contains($sousRace));
        $this->assertInstanceOf(HasMany::class, $race->sousRaces());
    }
}
