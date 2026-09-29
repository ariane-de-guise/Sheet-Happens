<?php

namespace Tests\Unit;

use App\Models\Capacite;
use App\Models\ClasseSort;
use App\Models\Degat;
use App\Models\Don;
use App\Models\PersonnageSort;
use App\Models\Sort;
use Database\Factories\ClasseFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\SousClasseFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationsSortsDonsCapacitesTest extends TestCase
{
    use RefreshDatabase;

    private function sort(?Degat $degat = null): Sort
    {
        return Sort::create([
            'id_Degat' => $degat?->id,
            'Nom' => 'Rayon de givre',
            'Description' => 'Un rayon glacé.',
            'Niveau' => 0,
            'TempsActivation' => '1 action',
            'Duree' => 'Instantanée',
            'Ecole' => 'Évocation',
            'Distance' => '18 mètres',
        ]);
    }

    public function test_un_sort_et_son_type_de_degat_sont_relies_dans_les_deux_sens(): void
    {
        $degat = Degat::create(['Nom' => 'Froid']);
        $sort = $this->sort($degat);

        $this->assertTrue($sort->degat->is($degat));
        $this->assertTrue($degat->sorts->first()->is($sort));
    }

    public function test_un_lien_classe_sort_expose_sa_classe_et_son_sort(): void
    {
        $classe = ClasseFactory::new()->create();
        $sort = $this->sort();
        $lien = ClasseSort::create(['id_Classe' => $classe->id, 'id_Sort' => $sort->id]);

        $this->assertTrue($lien->classe->is($classe));
        $this->assertTrue($lien->sort->is($sort));
    }

    public function test_un_sort_de_personnage_expose_son_personnage_et_son_sort(): void
    {
        $personnage = PersonnageFactory::new()->create();
        $sort = $this->sort();
        $lien = PersonnageSort::create(['id_Personnage' => $personnage->id, 'id_Sort' => $sort->id, 'Prepare' => 1]);

        $this->assertTrue($lien->personnage->is($personnage));
        $this->assertTrue($lien->sort->is($sort));
        $this->assertTrue($personnage->sorts->first()->is($lien));
    }

    public function test_une_capacite_est_reliee_a_sa_classe_sa_sous_classe_et_ses_personnages(): void
    {
        $classe = ClasseFactory::new()->create();
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();
        $deClasse = Capacite::create(['classe_id' => $classe->id, 'nom' => 'Rage', 'niveau' => 1]);
        $deSousClasse = Capacite::create(['classe_id' => $classe->id, 'sous_classe_id' => $sousClasse->id, 'nom' => 'Frénésie', 'niveau' => 3]);
        $personnage = PersonnageFactory::new()->create(['classe_id' => $classe->id]);
        $personnage->capacites()->attach($deClasse);

        $this->assertTrue($deSousClasse->classe->is($classe));
        $this->assertTrue($deSousClasse->sousClasse->is($sousClasse));
        $this->assertTrue($deClasse->personnages->first()->is($personnage));
        // La relation de la classe exclut les capacités de ses sous-classes.
        $this->assertSame([$deClasse->id], $classe->capacites->modelKeys());
        $this->assertSame([$deSousClasse->id], $sousClasse->capacites->modelKeys());
    }

    public function test_un_don_connait_les_personnages_qui_le_possedent(): void
    {
        $don = Don::create(['nom' => 'Alerte', 'effets' => ['initiative' => 5], 'choix_stat' => ['force']]);
        $personnage = PersonnageFactory::new()->create();
        $personnage->dons()->attach($don);

        $this->assertTrue($don->personnages->first()->is($personnage));
        $this->assertSame(['initiative' => 5], $don->fresh()->effets);
        $this->assertSame(['force'], $don->fresh()->choix_stat);
    }
}
