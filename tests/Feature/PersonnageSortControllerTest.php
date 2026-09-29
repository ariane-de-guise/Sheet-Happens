<?php

namespace Tests\Feature;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\ClasseSort;
use App\Models\LimiteMagie;
use App\Models\LimiteMagieClasse;
use App\Models\LimiteMagieSousClasse;
use App\Models\Personnage;
use App\Models\PersonnageSort;
use App\Models\Sort;
use App\Models\SousClasse;
use App\Models\SousClasseSort;
use App\Models\Utilisateur;
use Database\Factories\ClasseFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\SousClasseFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PersonnageSortControllerTest extends TestCase
{
    use RefreshDatabase;

    private Utilisateur $utilisateur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->utilisateur = Utilisateur::factory()->create();
    }

    private function personnage(Classe $classe, array $attributs = [], array $abilites = []): Personnage
    {
        return PersonnageFactory::new()->create(array_merge([
            'utilisateur_id' => $this->utilisateur->id,
            'classe_id' => $classe->id,
            'abilites_id' => Abilite::create(array_merge([
                'force' => 10, 'dexterite' => 10, 'constitution' => 10,
                'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10,
            ], $abilites))->id,
            'niveau' => 1,
        ], $attributs));
    }

    private function sort(string $nom, int $niveau = 1): Sort
    {
        return Sort::create([
            'nom' => $nom,
            'description' => "Description de $nom",
            'niveau' => $niveau,
            'temps_activation' => '1 action',
            'duree' => 'Instantanée',
            'ecole' => 'Évocation',
            'distance' => '18 mètres',
        ]);
    }

    private function limiteClasse(Classe $classe, array $valeurs, int $niveau = 1, string $abilite = 'Sagesse'): LimiteMagie
    {
        $limite = LimiteMagie::create(array_merge(['tours_de_magie' => 0, 'sorts_prepares' => null, 'emplacements_niveau_1' => 0, 'emplacements_niveau_2' => 0, 'emplacements_niveau_3' => 0], $valeurs));
        LimiteMagieClasse::create(['classe_id' => $classe->id, 'limite_magie_id' => $limite->id, 'niveau' => $niveau, 'caracteristique_magie' => $abilite]);

        return $limite;
    }

    private function lier(Personnage $personnage, Sort $sort, int $prepare): PersonnageSort
    {
        return PersonnageSort::create(['personnage_id' => $personnage->id, 'sort_id' => $sort->id, 'preparation' => $prepare]);
    }

    private function patcherPreparation(Personnage $personnage, PersonnageSort $personnageSort, int $prepare)
    {
        return $this->actingAs($this->utilisateur)->patchJson(
            route('personnages.sorts.preparer', [$personnage, $personnageSort]),
            ['prepare' => $prepare],
        );
    }

    private function envoyerPreparation(Personnage $personnage, array $donnees)
    {
        return $this->actingAs($this->utilisateur)->postJson(route('personnages.sorts.ajouter', $personnage), $donnees);
    }

    // ---------------------------------------------------------------
    // Accès réservé au propriétaire
    // ---------------------------------------------------------------

    public function test_un_autre_utilisateur_ne_peut_pas_voir_les_sorts(): void
    {
        $personnage = $this->personnage(ClasseFactory::new()->create(['nom' => 'Clerc']));

        $this->actingAs(Utilisateur::factory()->create())
            ->get(route('personnages.sorts.index', $personnage))
            ->assertForbidden();
    }

    public function test_un_autre_utilisateur_ne_peut_pas_preparer_un_sort(): void
    {
        $personnage = $this->personnage(ClasseFactory::new()->create(['nom' => 'Clerc']));
        $lien = $this->lier($personnage, $this->sort('Soins'), 0);

        $this->actingAs(Utilisateur::factory()->create())
            ->patchJson(route('personnages.sorts.preparer', [$personnage, $lien]), ['prepare' => 1])
            ->assertForbidden();

        $this->assertSame(0, (int) $lien->fresh()->preparation);
    }

    public function test_un_autre_utilisateur_ne_peut_pas_ajouter_un_sort(): void
    {
        $personnage = $this->personnage(ClasseFactory::new()->create(['nom' => 'Clerc']));

        $this->actingAs(Utilisateur::factory()->create())
            ->postJson(route('personnages.sorts.ajouter', $personnage), ['sort_id' => $this->sort('Soins')->id, 'prepare' => 0])
            ->assertForbidden();

        $this->assertDatabaseMissing('personnage_sorts', ['personnage_id' => $personnage->id]);
    }

    public function test_un_visiteur_non_connecte_est_redirige_vers_la_connexion(): void
    {
        $personnage = $this->personnage(ClasseFactory::new()->create(['nom' => 'Clerc']));

        $this->get(route('personnages.sorts.index', $personnage))->assertRedirect(route('connexion.create'));
    }

    // ---------------------------------------------------------------
    // index
    // ---------------------------------------------------------------

    public function test_la_page_liste_les_sorts_de_classe_de_sous_classe_et_ceux_du_personnage(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();
        $personnage = $this->personnage($classe, ['sous_classe_id' => $sousClasse->id], ['sagesse' => 16]);
        $this->limiteClasse($classe, ['tours_de_magie' => 3, 'sorts_prepares' => 4, 'emplacements_niveau_1' => 2]);

        $lumiere = $this->sort('Lumière', 0);
        $benediction = $this->sort('Bénédiction');
        $soins = $this->sort('Soins');
        $domaine = $this->sort('Sort de domaine');
        foreach ([$lumiere, $benediction, $soins] as $sort) {
            ClasseSort::create(['classe_id' => $classe->id, 'sort_id' => $sort->id]);
        }
        SousClasseSort::create(['sous_classe_id' => $sousClasse->id, 'sort_id' => $domaine->id]);
        $this->lier($personnage, $benediction, 1);

        $reponse = $this->actingAs($this->utilisateur)->get(route('personnages.sorts.index', $personnage));

        $reponse->assertOk()
            ->assertSee('Lumière')
            ->assertSee('Bénédiction')
            ->assertSee('Soins')
            ->assertSee('Sort de domaine')
            ->assertSee('Limite tour de magie : 3')
            ->assertSee('Limite sort : 4');

        $disponibles = $reponse->viewData('disponibles')->keyBy(fn ($s) => $s->sort->nom);
        $this->assertSame('personnage', $disponibles['Bénédiction']->origine);
        $this->assertSame(1, $disponibles['Bénédiction']->prepare);
        $this->assertSame('classe', $disponibles['Soins']->origine);
        $this->assertSame(0, $disponibles['Soins']->prepare);
        $this->assertSame('sous_classe', $disponibles['Sort de domaine']->origine);
        $this->assertSame(2, $disponibles['Sort de domaine']->prepare);
        $this->assertSame(['Lumière'], $reponse->viewData('niveau0')->map(fn ($s) => $s->sort->nom)->all());
        $this->assertSame('classe', $reponse->viewData('abiliteSource'));
        $this->assertSame('Sagesse', $reponse->viewData('abiliteMagie'));
        $this->assertSame(['emplacements_niveau_1' => 2, 'emplacements_niveau_2' => 0, 'emplacements_niveau_3' => 0], $reponse->viewData('emplacements'));
    }

    public function test_une_sous_classe_arcanique_utilise_la_liste_du_magicien_et_ses_propres_limites(): void
    {
        $magicien = ClasseFactory::new()->create(['nom' => 'Magicien']);
        $roublard = ClasseFactory::new()->create(['nom' => 'Roublard']);
        // Reconnue par son nom, quel que soit son identifiant en base.
        SousClasse::create(['classe_id' => $roublard->id, 'nom' => 'Voleur']);
        $filou = SousClasse::create(['classe_id' => $roublard->id, 'nom' => 'Filou Arcanique']);
        $personnage = $this->personnage($roublard, ['sous_classe_id' => $filou->id, 'niveau' => 3]);

        $projectile = $this->sort('Projectile magique');
        ClasseSort::create(['classe_id' => $magicien->id, 'sort_id' => $projectile->id]);
        $limite = LimiteMagie::create(['tours_de_magie' => 3, 'sorts_prepares' => 3, 'emplacements_niveau_1' => 2]);
        LimiteMagieSousClasse::create(['sous_classe_id' => $filou->id, 'limite_magie_id' => $limite->id, 'niveau' => 3, 'caracteristique_magie' => 'Intelligence']);

        $reponse = $this->actingAs($this->utilisateur)->get(route('personnages.sorts.index', $personnage));

        $reponse->assertOk()->assertSee('Projectile magique');
        $this->assertSame('sous_classe', $reponse->viewData('abiliteSource'));
        $this->assertSame(3, $reponse->viewData('limiteSort'));
    }

    public function test_une_classe_sans_magie_n_a_ni_sorts_ni_limites(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Barbare']);
        $personnage = $this->personnage($classe);

        $reponse = $this->actingAs($this->utilisateur)->get(route('personnages.sorts.index', $personnage));

        $reponse->assertOk();
        $this->assertTrue($reponse->viewData('disponibles')->isEmpty());
        $this->assertNull($reponse->viewData('limiteTour'));
        $this->assertNull($reponse->viewData('limiteSort'));
        $this->assertNull($reponse->viewData('emplacements'));
        $this->assertSame([1, 2, 3], $reponse->viewData('niveaux'));
    }

    public function test_un_lien_vers_un_sort_inexistant_est_ignore(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        ClasseSort::create(['classe_id' => $classe->id, 'sort_id' => $this->sort('Soins')->id]);

        // Données incohérentes, impossibles avec les clés étrangères : on
        // reporte leur vérification au commit, qui n'a jamais lieu puisque
        // RefreshDatabase annule la transaction du test.
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('Simulation propre à SQLite.');
        }
        DB::statement('PRAGMA defer_foreign_keys = ON');
        ClasseSort::create(['classe_id' => $classe->id, 'sort_id' => 9999]);

        $reponse = $this->actingAs($this->utilisateur)->get(route('personnages.sorts.index', $personnage));

        $reponse->assertOk();
        $this->assertSame(['Soins'], $reponse->viewData('disponibles')->map(fn ($s) => $s->sort->nom)->all());
    }

    // ---------------------------------------------------------------
    // modifierPreparation
    // ---------------------------------------------------------------

    public function test_modifier_un_sort_d_un_autre_personnage_est_refuse(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $autre = $this->personnage($classe);
        $lien = $this->lier($autre, $this->sort('Soins'), 0);

        $this->patcherPreparation($personnage, $lien, 1)->assertForbidden();
    }

    public function test_un_sort_accorde_par_la_sous_classe_ne_peut_pas_etre_modifie(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $lien = $this->lier($personnage, $this->sort('Sort de domaine'), 2);

        $this->patcherPreparation($personnage, $lien, 0)->assertForbidden();
        $this->assertSame(2, (int) $lien->fresh()->preparation);
    }

    public function test_un_magicien_ne_peut_preparer_qu_un_sort_inscrit_au_grimoire(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Magicien']);
        $personnage = $this->personnage($classe);
        $lien = $this->lier($personnage, $this->sort('Bouclier'), 0);

        $this->patcherPreparation($personnage, $lien, 1)->assertStatus(400);
    }

    public function test_un_magicien_peut_inscrire_puis_preparer_un_sort(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Magicien']);
        $personnage = $this->personnage($classe);
        $this->limiteClasse($classe, ['tours_de_magie' => 3, 'sorts_prepares' => 4], 1, 'Intelligence');
        $lien = $this->lier($personnage, $this->sort('Bouclier'), 0);

        $this->patcherPreparation($personnage, $lien, 2)->assertOk()->assertJson(['prepare' => 2]);
        $this->patcherPreparation($personnage, $lien, 1)->assertOk()->assertJson(['prepare' => 1]);
    }

    public function test_preparer_un_sort_mineur_respecte_la_limite_de_tours_de_magie(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $this->limiteClasse($classe, ['tours_de_magie' => 1, 'sorts_prepares' => 5]);
        $this->lier($personnage, $this->sort('Lumière', 0), 1);
        $lien = $this->lier($personnage, $this->sort('Flamme sacrée', 0), 0);

        $this->patcherPreparation($personnage, $lien, 1)
            ->assertStatus(400)
            ->assertJson(['message' => 'Limite de sorts cantrips préparés atteinte.']);
    }

    public function test_preparer_un_sort_respecte_la_limite_de_sorts_prepares(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $this->limiteClasse($classe, ['tours_de_magie' => 3, 'sorts_prepares' => 1]);
        $this->lier($personnage, $this->sort('Bénédiction'), 1);
        $lien = $this->lier($personnage, $this->sort('Soins'), 0);

        $this->patcherPreparation($personnage, $lien, 1)
            ->assertStatus(400)
            ->assertJson(['message' => 'Limite de sorts préparés (niveaux 1-3) atteinte.']);
    }

    public function test_preparer_un_sort_sous_la_limite_reussit(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $this->limiteClasse($classe, ['tours_de_magie' => 3, 'sorts_prepares' => 2]);
        $tourDeMagie = $this->lier($personnage, $this->sort('Lumière', 0), 0);
        $lien = $this->lier($personnage, $this->sort('Soins'), 0);

        $this->patcherPreparation($personnage, $tourDeMagie, 1)->assertOk()->assertJson(['succes' => true, 'prepare' => 1]);
        $this->patcherPreparation($personnage, $lien, 1)->assertOk()->assertJson(['succes' => true, 'prepare' => 1]);
    }

    public function test_sans_limite_de_sorts_la_limite_vaut_niveau_plus_modificateur_de_la_classe(): void
    {
        // Clerc sans ligne de limite : Sagesse 12 → +1, niveau 1 → limite 2.
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe, [], ['sagesse' => 12]);
        $this->lier($personnage, $this->sort('Bénédiction'), 1);
        $this->lier($personnage, $this->sort('Soins'), 1);
        $lien = $this->lier($personnage, $this->sort('Injonction'), 0);

        $this->patcherPreparation($personnage, $lien, 1)->assertStatus(400);
    }

    public function test_sans_limite_de_sorts_l_abilite_de_la_ligne_de_limite_est_prioritaire(): void
    {
        // Limite sans sorts_prepares mais avec caracteristique_magie Charisme 14 → +2 : limite 3.
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe, [], ['sagesse' => 8, 'charisme' => 14]);
        $this->limiteClasse($classe, ['tours_de_magie' => 3, 'sorts_prepares' => null], 1, 'Charisme');
        $this->lier($personnage, $this->sort('Bénédiction'), 1);
        $this->lier($personnage, $this->sort('Soins'), 1);
        $lien = $this->lier($personnage, $this->sort('Injonction'), 0);

        $this->patcherPreparation($personnage, $lien, 1)->assertOk();
    }

    public function test_sans_limite_ni_classe_connue_le_modificateur_est_nul(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Guerrier']);
        $personnage = $this->personnage($classe);
        $this->lier($personnage, $this->sort('Bénédiction'), 1);
        $lien = $this->lier($personnage, $this->sort('Soins'), 0);

        $this->patcherPreparation($personnage, $lien, 1)->assertStatus(400);
    }

    public function test_depreparer_un_sort_reussit(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $lien = $this->lier($personnage, $this->sort('Soins'), 1);

        $this->patcherPreparation($personnage, $lien, 0)->assertOk()->assertJson(['prepare' => 0]);
    }

    public function test_une_valeur_de_preparation_invalide_est_ramenee_a_zero(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $lien = $this->lier($personnage, $this->sort('Soins'), 1);

        // Hors magicien, 2 (grimoire) n'a pas de sens : ramené à « non préparé ».
        $this->patcherPreparation($personnage, $lien, 2)->assertOk()->assertJson(['prepare' => 0]);
    }

    // ---------------------------------------------------------------
    // ajouterPreparation
    // ---------------------------------------------------------------

    public function test_ajouter_un_sort_sans_identifiant_est_refuse(): void
    {
        $personnage = $this->personnage(ClasseFactory::new()->create(['nom' => 'Clerc']));

        $this->envoyerPreparation($personnage, ['prepare' => 1])->assertStatus(400);
    }

    public function test_ajouter_un_sort_prepare_cree_le_lien(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $this->limiteClasse($classe, ['tours_de_magie' => 3, 'sorts_prepares' => 2]);
        $sort = $this->sort('Soins');

        $this->envoyerPreparation($personnage, ['sort_id' => $sort->id, 'prepare' => 1])
            ->assertOk()
            ->assertJson(['succes' => true, 'prepare' => 1]);

        $this->assertDatabaseHas('personnage_sorts', ['personnage_id' => $personnage->id, 'sort_id' => $sort->id, 'preparation' => 1]);
    }

    public function test_ajouter_un_sort_mineur_prepare_reussit_sous_la_limite(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $this->limiteClasse($classe, ['tours_de_magie' => 2, 'sorts_prepares' => 2]);

        $this->envoyerPreparation($personnage, ['sort_id' => $this->sort('Lumière', 0)->id, 'prepare' => 1])->assertOk();
    }

    public function test_ajouter_un_sort_mineur_au_dela_de_la_limite_est_refuse(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $this->limiteClasse($classe, ['tours_de_magie' => 1, 'sorts_prepares' => 2]);
        $this->lier($personnage, $this->sort('Lumière', 0), 1);

        $this->envoyerPreparation($personnage, ['sort_id' => $this->sort('Flamme sacrée', 0)->id, 'prepare' => 1])
            ->assertStatus(400);
    }

    public function test_ajouter_un_sort_au_dela_de_la_limite_est_refuse(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $this->limiteClasse($classe, ['tours_de_magie' => 2, 'sorts_prepares' => 1]);
        $this->lier($personnage, $this->sort('Bénédiction'), 1);
        $sort = $this->sort('Soins');

        $this->envoyerPreparation($personnage, ['sort_id' => $sort->id, 'prepare' => 1])->assertStatus(400);
        $this->assertDatabaseMissing('personnage_sorts', ['sort_id' => $sort->id]);
    }

    public function test_ajouter_un_sort_utilise_la_limite_calculee_avec_l_abilite_de_la_limite(): void
    {
        // sorts_prepares absent, caracteristique_magie Charisme 12 → +1 : limite 2.
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe, [], ['charisme' => 12]);
        $this->limiteClasse($classe, ['tours_de_magie' => 2, 'sorts_prepares' => null], 1, 'Charisme');
        $this->lier($personnage, $this->sort('Bénédiction'), 1);

        $this->envoyerPreparation($personnage, ['sort_id' => $this->sort('Soins')->id, 'prepare' => 1])->assertOk();
    }

    public function test_ajouter_un_sort_sans_limite_utilise_l_abilite_de_la_classe(): void
    {
        // Aucune limite : Clerc → Sagesse 10 → +0, limite = niveau 1.
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $this->lier($personnage, $this->sort('Bénédiction'), 1);

        $this->envoyerPreparation($personnage, ['sort_id' => $this->sort('Soins')->id, 'prepare' => 1])->assertStatus(400);
    }

    public function test_un_magicien_ne_peut_pas_preparer_directement_un_sort_hors_grimoire(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Magicien']);
        $personnage = $this->personnage($classe);

        $this->envoyerPreparation($personnage, ['sort_id' => $this->sort('Bouclier')->id, 'prepare' => 1])->assertStatus(400);
    }

    public function test_un_magicien_peut_inscrire_un_sort_au_grimoire(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Magicien']);
        $personnage = $this->personnage($classe);
        $sort = $this->sort('Bouclier');

        $this->envoyerPreparation($personnage, ['sort_id' => $sort->id, 'prepare' => 2])->assertOk()->assertJson(['prepare' => 2]);
        $this->envoyerPreparation($personnage, ['sort_id' => $sort->id, 'prepare' => 0])->assertOk()->assertJson(['prepare' => 0]);
    }

    public function test_mettre_a_jour_un_lien_existant_accorde_par_la_sous_classe_est_refuse(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $sort = $this->sort('Sort de domaine');
        $this->lier($personnage, $sort, 2);

        $this->envoyerPreparation($personnage, ['sort_id' => $sort->id, 'prepare' => 0])->assertForbidden();
    }

    public function test_mettre_a_jour_un_lien_existant_reussit(): void
    {
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $personnage = $this->personnage($classe);
        $sort = $this->sort('Soins');
        $lien = $this->lier($personnage, $sort, 1);

        $this->envoyerPreparation($personnage, ['sort_id' => $sort->id, 'prepare' => 0])
            ->assertOk()
            ->assertJson(['personnage_sort_id' => $lien->id, 'prepare' => 0]);
    }
}
