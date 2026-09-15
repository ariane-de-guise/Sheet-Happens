<?php

namespace Tests\Unit;

use Database\Factories\RaceFactory;
use Database\Factories\SousRaceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SousRaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_sous_race_appartient_a_une_race(): void
    {
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();

        $this->assertTrue($sousRace->race->is($race));
    }
}
