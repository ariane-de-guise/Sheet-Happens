<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Personnage;
use App\Models\Utilisateur;
use Database\Factories\PersonnageFactory;
use Database\Seeders\CapaciteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonteeNiveauTest extends TestCase
{
    use RefreshDatabase;

    private function classe(string $nom = 'Barbare', string $de = '12', int $niveauSousClasse = 3): Classe
    {
        $classe = Classe::create([
            'nom' => $nom,
            'de_de_vie' => $de,
            'description' => 'Classe de test',
            'niveau_sous_classe' => $niveauSousClasse,
            'competences_disponibles' => [],
            'nombre_competences' => 2,
        ]);
        $this->seed(CapaciteSeeder::class);

        return $classe;
    }

    private function personnage(Utilisateur $u, Classe $classe, int $niveau = 1): Personnage
    {
        return PersonnageFactory::new()->create([
            'utilisateur_id' => $u->id,
            'classe_id' => $classe->id,
            'niveau' => $niveau,
            'experience' => 6500, // assez pour atteindre le niveau 5
        ]);
    }

    public function test_proprietaire_peut_ouvrir_ecran_de_montee(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);

        $this->actingAs($u)->get(route('personnages.montee-niveau', $p))
            ->assertOk()
            ->assertSee('Montée de niveau')
            ->assertSee('Attaque téméraire')
            ->assertSee('Valeur fixe')
            ->assertSee('Lancer le dé')
            ->assertSee('Lancer le d12')
            ->assertSee('Résultat :')
            ->assertSee('1d12');
    }

    public function test_autre_utilisateur_ne_peut_pas_ouvrir_ecran_de_montee(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $p = $this->personnage($proprietaire, $this->classe());

        $this->actingAs($autre)->get(route('personnages.montee-niveau', $p))->assertForbidden();
    }

    public function test_autre_utilisateur_ne_peut_pas_monter_personnage(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $p = $this->personnage($proprietaire, $this->classe());

        $this->actingAs($autre)->post(route('personnages.montee-niveau.store', $p), [])->assertForbidden();
    }

    public function test_autre_utilisateur_ne_peut_pas_lancer_le_de_de_vie(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $p = $this->personnage($proprietaire, $this->classe());

        $this->actingAs($autre)->postJson(route('personnages.montee-niveau.lancer-de-vie', $p))->assertForbidden();
    }

    public function test_confirmation_fait_passer_au_niveau_suivant(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);
        $p->update(['experience' => 300]); // juste assez pour le niveau 2

        $this->actingAs($u)->post(route('personnages.montee-niveau.store', $p), [])
            ->assertRedirect(route('personnages.show', $p))
            ->assertSessionHas('succes');

        $this->assertSame(2, $p->fresh()->niveau);
    }

    public function test_assez_d_experience_pour_deux_niveaux_enchaine_les_montees(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);
        $p->update(['experience' => 1000]); // seuils : 300 (niv. 2) puis 900 (niv. 3)

        $this->actingAs($u)->post(route('personnages.montee-niveau.store', $p), [])
            ->assertRedirect(route('personnages.montee-niveau', $p))
            ->assertSessionHas('succes');
        $this->assertSame(2, $p->fresh()->niveau);

        // Niveau 3 = choix de sous-classe pour le Barbare : aucune n'existe
        // ici, on vérifie seulement que l'écran suivant s'ouvre bien.
        $this->actingAs($u)->get(route('personnages.montee-niveau', $p))
            ->assertOk()
            ->assertSee('Niveau 2 → 3');
    }

    public function test_la_montee_s_arrete_quand_l_experience_ne_suffit_plus(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);
        $p->update(['experience' => 500]);

        $this->actingAs($u)->post(route('personnages.montee-niveau.store', $p), [])
            ->assertRedirect(route('personnages.show', $p));
        $this->assertSame(2, $p->fresh()->niveau);
    }

    public function test_l_ecran_de_montee_est_refuse_sans_assez_d_experience(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);
        $p->update(['experience' => 100]);

        $this->actingAs($u)->get(route('personnages.montee-niveau', $p))
            ->assertRedirect(route('personnages.show', $p))
            ->assertSessionHasErrors('experience');
    }

    public function test_confirmer_une_montee_sans_assez_d_experience_est_refuse(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);
        $p->update(['experience' => 100]);

        $this->actingAs($u)->post(route('personnages.montee-niveau.store', $p), [])
            ->assertSessionHasErrors('experience');

        $this->assertSame(1, $p->fresh()->niveau);
    }

    public function test_la_fiche_n_affiche_plus_de_bouton_de_montee(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);

        $this->actingAs($u)->get(route('personnages.show', $p))
            ->assertOk()
            ->assertDontSee('Monter de niveau');
    }

    public function test_niveau_cinq_redirige_vers_fiche(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe('Guerrier', '10'), 5);

        $this->actingAs($u)->get(route('personnages.montee-niveau', $p))
            ->assertRedirect(route('personnages.show', $p))
            ->assertSessionHas('succes');
    }

    public function test_confirmation_permet_de_lancer_le_de_de_vie(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);
        $p->update(['experience' => 300]); // juste assez pour le niveau 2
        $pvAvant = $p->point_de_vie;

        $reponseLancer = $this->actingAs($u)->postJson(route('personnages.montee-niveau.lancer-de-vie', $p));
        $reponseLancer->assertOk()->assertJsonStructure(['lancer', 'faces']);
        $jet = $reponseLancer->json('lancer');
        $this->assertGreaterThanOrEqual(1, $jet);
        $this->assertLessThanOrEqual(12, $jet);
        $this->assertSame(12, $reponseLancer->json('faces'));

        $this->actingAs($u)->post(route('personnages.montee-niveau.store', $p), ['mode_pv' => 'de'])
            ->assertRedirect(route('personnages.show', $p))
            ->assertSessionHas('succes');

        $montee = $p->monteesNiveau()->latest('id')->first();
        $this->assertSame('de', $montee->choix['mode_pv']);
        $this->assertSame($jet, $montee->changements['jet_de_vie']);
        $this->assertGreaterThan($pvAvant, $p->fresh()->point_de_vie);
    }

    public function test_jet_de_vie_est_conserve_si_validation_asi_echoue(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 3);

        $reponseLancer = $this->actingAs($u)->postJson(route('personnages.montee-niveau.lancer-de-vie', $p));
        $reponseLancer->assertOk();
        $jet = $reponseLancer->json('lancer');

        $this->actingAs($u)
            ->from(route('personnages.montee-niveau', $p))
            ->post(route('personnages.montee-niveau.store', $p), [
                'mode_pv' => 'de',
                'type_amelioration' => 'caracteristiques',
                'caracteristiques' => ['force' => 1],
            ])
            ->assertRedirect(route('personnages.montee-niveau', $p))
            ->assertSessionHasErrors('caracteristiques')
            ->assertSessionHas('montee_niveau.jet_de_vie.'.$p->id, $jet);

        $this->get(route('personnages.montee-niveau', $p))
            ->assertOk()
            ->assertSee('Résultat :')
            ->assertSee('>'.$jet.'<', false);
    }
}
