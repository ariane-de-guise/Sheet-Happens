<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Personnage;
use App\Models\Utilisateur;
use Database\Factories\PersonnageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonteeNiveauTest extends TestCase
{
    use RefreshDatabase;

    private function classe(string $nom = 'Barbare', string $de = '12', int $niveauSousClasse = 3): Classe
    {
        return Classe::create([
            'nom' => $nom,
            'de_de_vie' => $de,
            'description' => 'Classe de test',
            'niveau_sous_classe' => $niveauSousClasse,
            'competences_disponibles' => [],
            'nombre_competences' => 2,
        ]);
    }

    private function personnage(Utilisateur $u, Classe $classe, int $niveau = 1): Personnage
    {
        return PersonnageFactory::new()->create([
            'utilisateur_id' => $u->id,
            'classe_id' => $classe->id,
            'niveau' => $niveau,
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
        $owner = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $p = $this->personnage($owner, $this->classe());

        $this->actingAs($autre)->get(route('personnages.montee-niveau', $p))->assertForbidden();
    }

    public function test_autre_utilisateur_ne_peut_pas_monter_personnage(): void
    {
        $owner = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $p = $this->personnage($owner, $this->classe());

        $this->actingAs($autre)->post(route('personnages.montee-niveau.store', $p), [])->assertForbidden();
    }

    public function test_autre_utilisateur_ne_peut_pas_lancer_le_de_de_vie(): void
    {
        $owner = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $p = $this->personnage($owner, $this->classe());

        $this->actingAs($autre)->postJson(route('personnages.montee-niveau.roll-hit-die', $p))->assertForbidden();
    }

    public function test_confirmation_fait_passer_au_niveau_suivant(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);

        $this->actingAs($u)->post(route('personnages.montee-niveau.store', $p), [])
            ->assertRedirect(route('personnages.show', $p))
            ->assertSessionHas('success');

        $this->assertSame(2, $p->fresh()->niveau);
    }

    public function test_niveau_cinq_redirige_vers_fiche(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe('Guerrier', '10'), 5);

        $this->actingAs($u)->get(route('personnages.montee-niveau', $p))
            ->assertRedirect(route('personnages.show', $p))
            ->assertSessionHas('success');
    }

    public function test_confirmation_permet_de_lancer_le_de_de_vie(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1);
        $pvAvant = $p->point_de_vie;

        $rollResponse = $this->actingAs($u)->postJson(route('personnages.montee-niveau.roll-hit-die', $p));
        $rollResponse->assertOk()->assertJsonStructure(['roll', 'faces']);
        $jet = $rollResponse->json('roll');
        $this->assertGreaterThanOrEqual(1, $jet);
        $this->assertLessThanOrEqual(12, $jet);
        $this->assertSame(12, $rollResponse->json('faces'));

        $this->actingAs($u)->post(route('personnages.montee-niveau.store', $p), ['mode_pv' => 'de'])
            ->assertRedirect(route('personnages.show', $p))
            ->assertSessionHas('success');

        $montee = $p->monteesNiveau()->latest('id')->first();
        $this->assertSame('de', $montee->choix['mode_pv']);
        $this->assertSame($jet, $montee->changements['jet_de_vie']);
        $this->assertGreaterThan($pvAvant, $p->fresh()->point_de_vie);
    }

    public function test_jet_de_vie_est_conserve_si_validation_asi_echoue(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 3);

        $rollResponse = $this->actingAs($u)->postJson(route('personnages.montee-niveau.roll-hit-die', $p));
        $rollResponse->assertOk();
        $jet = $rollResponse->json('roll');

        $this->actingAs($u)
            ->from(route('personnages.montee-niveau', $p))
            ->post(route('personnages.montee-niveau.store', $p), [
                'mode_pv' => 'de',
                'type_amelioration' => 'asi',
                'asi' => ['force' => 1],
            ])
            ->assertRedirect(route('personnages.montee-niveau', $p))
            ->assertSessionHasErrors('asi')
            ->assertSessionHas('montee_niveau.jet_de_vie.'.$p->id, $jet);

        $this->get(route('personnages.montee-niveau', $p))
            ->assertOk()
            ->assertSee('Résultat :')
            ->assertSee('>'.$jet.'<', false);
    }
}
