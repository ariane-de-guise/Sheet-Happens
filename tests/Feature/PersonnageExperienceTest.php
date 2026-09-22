<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Personnage;
use App\Models\Utilisateur;
use Database\Factories\PersonnageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnageExperienceTest extends TestCase
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

    private function personnage(Utilisateur $u, Classe $classe, int $niveau = 1, int $experience = 0): Personnage
    {
        return PersonnageFactory::new()->create([
            'utilisateur_id' => $u->id,
            'classe_id' => $classe->id,
            'niveau' => $niveau,
            'experience' => $experience,
        ]);
    }

    public function test_proprietaire_peut_ajouter_de_l_experience(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1, 50);

        $this->actingAs($u)->post(route('personnages.experience.ajouter', $p), ['experience' => 100])
            ->assertRedirect(route('personnages.show', $p));

        $this->assertSame(150, $p->fresh()->experience);
    }

    public function test_franchir_le_seuil_redirige_vers_la_montee_de_niveau(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1, 250);

        $this->actingAs($u)->post(route('personnages.experience.ajouter', $p), ['experience' => 50])
            ->assertRedirect(route('personnages.montee-niveau', $p));

        $this->assertSame(300, $p->fresh()->experience);
        $this->assertSame(1, $p->fresh()->niveau);
    }

    public function test_ne_pas_franchir_le_seuil_reste_sur_la_fiche(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 1, 0);

        $this->actingAs($u)->post(route('personnages.experience.ajouter', $p), ['experience' => 299])
            ->assertRedirect(route('personnages.show', $p));
    }

    public function test_autre_utilisateur_ne_peut_pas_ajouter_de_l_experience(): void
    {
        $owner = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $p = $this->personnage($owner, $this->classe());

        $this->actingAs($autre)->post(route('personnages.experience.ajouter', $p), ['experience' => 100])
            ->assertForbidden();

        $this->assertSame(0, $p->fresh()->experience);
    }

    public function test_l_experience_doit_etre_un_entier_positif(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe());

        $this->actingAs($u)->post(route('personnages.experience.ajouter', $p), ['experience' => 0])
            ->assertSessionHasErrors('experience');

        $this->actingAs($u)->post(route('personnages.experience.ajouter', $p), ['experience' => 'abc'])
            ->assertSessionHasErrors('experience');
    }

    public function test_la_fiche_affiche_le_seuil_d_experience_du_niveau_actuel(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 2, 400);

        $this->actingAs($u)->get(route('personnages.show', $p))
            ->assertOk()
            ->assertSee('400/900');
    }

    public function test_la_fiche_indique_le_niveau_maximum_sans_seuil(): void
    {
        $u = Utilisateur::factory()->create();
        $p = $this->personnage($u, $this->classe(), 5, 6500);

        $this->actingAs($u)->get(route('personnages.show', $p))
            ->assertOk()
            ->assertSee('niveau maximum');
    }
}
