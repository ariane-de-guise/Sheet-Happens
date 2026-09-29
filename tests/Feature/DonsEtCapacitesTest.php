<?php

namespace Tests\Feature;

use App\Models\Capacite;
use App\Models\Don;
use App\Models\Utilisateur;
use App\Services\CapaciteService;
use Database\Factories\ClasseFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\SousClasseFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonsEtCapacitesTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_synchronisation_rattache_les_capacites_de_classe_et_de_sous_classe_jusqu_au_niveau(): void
    {
        $classe = ClasseFactory::new()->create();
        $sousClasse = SousClasseFactory::new()->create(['classe_id' => $classe->id]);
        $autreSousClasse = SousClasseFactory::new()->create(['classe_id' => $classe->id]);
        $niveau1 = Capacite::create(['classe_id' => $classe->id, 'nom' => 'Rage', 'niveau' => 1]);
        $niveau2 = Capacite::create(['classe_id' => $classe->id, 'nom' => 'Attaque téméraire', 'niveau' => 2]);
        Capacite::create(['classe_id' => $classe->id, 'nom' => 'Voie primitive', 'niveau' => 3]);
        $deSousClasse = Capacite::create(['classe_id' => $classe->id, 'sous_classe_id' => $sousClasse->id, 'nom' => 'Frénésie', 'niveau' => 2]);
        Capacite::create(['classe_id' => $classe->id, 'sous_classe_id' => $autreSousClasse->id, 'nom' => 'Esprit totem', 'niveau' => 2]);

        $personnage = PersonnageFactory::new()->create([
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
            'niveau' => 2,
        ]);

        app(CapaciteService::class)->synchroniser($personnage);

        $this->assertEqualsCanonicalizing(
            [$niveau1->id, $niveau2->id, $deSousClasse->id],
            $personnage->capacites()->pluck('capacites.id')->all(),
        );
    }

    public function test_la_fiche_affiche_les_capacites_et_les_dons_avec_leur_description_au_survol(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create();
        Capacite::create([
            'classe_id' => $classe->id,
            'nom' => 'Rage',
            'niveau' => 1,
            'description' => 'Entrez dans une fureur primitive.',
        ]);
        $don = Don::create(['nom' => 'Alerte', 'resume' => 'Vigilance accrue.', 'description' => 'Bonus de +5 à l’initiative.']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
        ]);
        app(CapaciteService::class)->synchroniser($personnage);
        $personnage->dons()->attach($don);

        $this->actingAs($utilisateur)
            ->get(route('personnages.show', $personnage))
            ->assertOk()
            ->assertSee('info-bulle', false)
            ->assertSee('Rage')
            ->assertSee('Entrez dans une fureur primitive.')
            ->assertSee('Alerte')
            ->assertSee('Bonus de +5 à l’initiative.');
    }

    public function test_la_page_des_traits_liste_les_capacites_et_les_dons(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create();
        Capacite::create(['classe_id' => $classe->id, 'nom' => 'Second souffle', 'niveau' => 1, 'description' => 'Récupérez des PV.']);
        $don = Don::create(['nom' => 'Robuste', 'description' => '+2 PV par niveau.']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
        ]);
        app(CapaciteService::class)->synchroniser($personnage);
        $personnage->dons()->attach($don);

        $this->actingAs($utilisateur)
            ->get(route('personnages.traits', $personnage))
            ->assertOk()
            ->assertSee('Capacités de classe')
            ->assertSee('Second souffle')
            ->assertSee('Récupérez des PV.')
            ->assertSee('Robuste')
            ->assertSee('+2 PV par niveau.');
    }

    public function test_supprimer_un_personnage_supprime_ses_liens_de_dons_et_capacites(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create();
        Capacite::create(['classe_id' => $classe->id, 'nom' => 'Rage', 'niveau' => 1]);
        $don = Don::create(['nom' => 'Chanceux']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
        ]);
        app(CapaciteService::class)->synchroniser($personnage);
        $personnage->dons()->attach($don);

        $this->actingAs($utilisateur)->delete(route('personnages.destroy', $personnage));

        $this->assertDatabaseMissing('personnage_capacite', ['personnage_id' => $personnage->id]);
        $this->assertDatabaseMissing('personnage_don', ['personnage_id' => $personnage->id]);
        $this->assertDatabaseHas('dons', ['id' => $don->id]);
    }
}
