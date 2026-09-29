<?php

namespace Tests\Unit;

use App\Models\LimiteMagie;
use App\Models\LimiteMagieSousClasse;
use App\Models\SousClasse;
use App\Services\SortService;
use Database\Factories\ClasseFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\SousClasseFactory;
use Database\Seeders\ClassesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SortServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_sous_classes_utilisant_la_liste_du_magicien_existent_dans_le_seeder(): void
    {
        // Garde-fou : SortService les retrouve par leur nom ; un renommage
        // dans ClassesSeeder leur ferait perdre silencieusement leurs sorts.
        $this->seed(ClassesSeeder::class);

        foreach (SortService::SOUS_CLASSES_LISTE_MAGICIEN as $nom) {
            $this->assertTrue(SousClasse::where('nom', $nom)->exists(), "Sous-classe « $nom » absente du seeder.");
        }
    }

    public function test_une_sous_classe_magique_donne_des_sorts_a_une_classe_non_magique(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Guerrier']);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();
        $limite = LimiteMagie::create(['TourMagie' => 2, 'Niveau1' => 0]);
        LimiteMagieSousClasse::create(['id_SousClasse' => $sousClasse->id, 'id_LimiteMagie' => $limite->id, 'Niveau' => 3, 'AbiliteMagie' => 'Intelligence']);
        $service = app(SortService::class);

        $avant = PersonnageFactory::new()->create(['classe_id' => $classe->id, 'sous_classe_id' => $sousClasse->id, 'niveau' => 2]);
        $apres = PersonnageFactory::new()->create(['classe_id' => $classe->id, 'sous_classe_id' => $sousClasse->id, 'niveau' => 3]);

        $this->assertFalse($service->aDesSorts($avant));
        $this->assertTrue($service->aDesSorts($apres));
        $this->assertSame('intelligence', $service->caracteristiqueIncantation($apres, $service->limites($apres)));
    }

    public function test_un_sort_de_niveau_superieur_a_trois_n_est_pas_limite(): void
    {
        $personnage = PersonnageFactory::new()->create();
        $service = app(SortService::class);

        $this->assertNull($service->depassementLimite($personnage, 4, $service->limites($personnage)));
    }

    public function test_sans_limite_de_tours_de_magie_les_sorts_mineurs_ne_sont_pas_limites(): void
    {
        $personnage = PersonnageFactory::new()->create();
        $service = app(SortService::class);

        $this->assertNull($service->depassementLimite($personnage, 0, $service->limites($personnage)));
    }
}
