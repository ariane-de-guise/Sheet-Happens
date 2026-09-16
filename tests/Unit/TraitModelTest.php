<?php

namespace Tests\Unit;

use App\Models\TraitModel;
use Database\Factories\RaceFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraitModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_trait_utilise_la_table_et_les_attributs_attendus(): void
    {
        $trait = TraitModel::create([
            'nom' => 'Vision dans le noir',
            'niveau' => '2',
            'description' => 'Une créature voit dans les ténèbres.',
        ]);

        $this->assertSame('traits', $trait->getTable());
        $this->assertSame('Vision dans le noir', $trait->nom);
        $this->assertSame(2, $trait->niveau);
        $this->assertSame('Une créature voit dans les ténèbres.', $trait->description);
        $this->assertDatabaseHas('traits', [
            'id' => $trait->id,
            'nom' => 'Vision dans le noir',
            'niveau' => 2,
        ]);
    }

    public function test_un_trait_possede_une_relation_vers_plusieurs_races(): void
    {
        $trait = TraitModel::create([
            'nom' => 'Résistance au feu',
            'niveau' => 1,
            'description' => 'La créature résiste aux dégâts de feu.',
        ]);
        $race = RaceFactory::new()->create();

        $trait->races()->attach($race);

        $this->assertInstanceOf(BelongsToMany::class, $trait->races());
        $this->assertTrue($trait->races->contains($race));
        $this->assertTrue($race->traits->contains($trait));
        $this->assertDatabaseHas('race_trait', [
            'race_id' => $race->id,
            'trait_id' => $trait->id,
        ]);
    }
}
