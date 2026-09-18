<?php

namespace Tests\Unit;

use App\Models\MonteeNiveau;
use Database\Factories\ClasseFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\RaceFactory;
use Database\Factories\SousClasseFactory;
use Database\Factories\SousRaceFactory;
use Database\Factories\UtilisateurFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnageTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_personnage_possede_toutes_ses_relations(): void
    {
        $utilisateur = UtilisateurFactory::new()->create();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $classe = ClasseFactory::new()->create();
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'sous_race_id' => $sousRace->id,
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
        ]);

        $this->assertTrue($personnage->utilisateur->is($utilisateur));
        $this->assertTrue($personnage->race->is($race));
        $this->assertTrue($personnage->sousRace->is($sousRace));
        $this->assertTrue($personnage->classe->is($classe));
        $this->assertTrue($personnage->sousClasse->is($sousClasse));
        $this->assertNotNull($personnage->abilites);
        $this->assertNotNull($personnage->listeCompetence);
    }

    public function test_un_personnage_possede_plusieurs_montees_de_niveau_ordonnees(): void
    {
        $personnage = PersonnageFactory::new()->create();

        MonteeNiveau::create([
            'personnage_id' => $personnage->id,
            'niveau_depart' => 2,
            'niveau_arrivee' => 3,
            'choix' => [],
            'changements' => [],
        ]);

        MonteeNiveau::create([
            'personnage_id' => $personnage->id,
            'niveau_depart' => 1,
            'niveau_arrivee' => 2,
            'choix' => [],
            'changements' => [],
        ]);

        $montees = $personnage->monteesNiveau;

        $this->assertCount(2, $montees);
        $this->assertSame([2, 3], $montees->pluck('niveau_arrivee')->all());
    }
}
