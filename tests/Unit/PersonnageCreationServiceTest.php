<?php

namespace Tests\Unit;

use App\Models\Capacite;
use App\Models\Classe;
use App\Models\Personnage;
use App\Models\Race;
use App\Services\PersonnageCreationService;
use Database\Factories\ClasseFactory;
use Database\Factories\RaceFactory;
use Database\Factories\SousClasseFactory;
use Database\Factories\SousRaceFactory;
use Database\Factories\UtilisateurFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PersonnageCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): PersonnageCreationService
    {
        return $this->app->make(PersonnageCreationService::class);
    }

    private function donneesDeBase(): array
    {
        $utilisateur = UtilisateurFactory::new()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);

        return [
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'sous_race_id' => null,
            'classe_id' => $classe->id,
            'sous_classe_id' => null,
            'abilites' => [
                'force' => 15,
                'dexterite' => 14,
                'constitution' => 13,
                'intelligence' => 12,
                'sagesse' => 10,
                'charisme' => 8,
            ],
            'competences' => ['Athlétisme'],
            'nom' => 'Aragorn',
            'niveau' => 1,
            'alignement' => 'Bon',
            'description' => 'Un rôdeur.',
            'point_de_vie' => 10,
        ];
    }

    public function test_cree_un_personnage_complet_avec_ses_relations(): void
    {
        $donneesPersonnage = $this->donneesDeBase();

        $personnage = $this->service()->create($donneesPersonnage);

        $this->assertInstanceOf(Personnage::class, $personnage);
        $this->assertSame('Aragorn', $personnage->nom);
        $this->assertTrue($personnage->relationLoaded('race'));
        $this->assertTrue($personnage->relationLoaded('classe'));
        $this->assertTrue($personnage->relationLoaded('abilites'));
        $this->assertTrue($personnage->relationLoaded('listeCompetence'));
        $this->assertDatabaseHas('personnages', ['nom' => 'Aragorn']);
        $this->assertDatabaseHas('abilites', ['force' => 15]);
    }

    public function test_cree_un_personnage_avec_sous_race_et_sous_classe_valides(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $race = Race::find($donneesPersonnage['race_id']);
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $classe = Classe::find($donneesPersonnage['classe_id']);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $donneesPersonnage['sous_race_id'] = $sousRace->id;
        $donneesPersonnage['sous_classe_id'] = $sousClasse->id;

        $personnage = $this->service()->create($donneesPersonnage);

        $this->assertSame($sousRace->id, $personnage->sous_race_id);
        $this->assertSame($sousClasse->id, $personnage->sous_classe_id);
    }

    public function test_la_creation_rattache_les_capacites_de_niveau_1_de_la_classe_et_de_la_sous_classe(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $classe = Classe::find($donneesPersonnage['classe_id']);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();
        $donneesPersonnage['sous_classe_id'] = $sousClasse->id;
        $incantation = Capacite::create(['classe_id' => $classe->id, 'nom' => 'Incantation', 'niveau' => 1]);
        $disciple = Capacite::create(['classe_id' => $classe->id, 'sous_classe_id' => $sousClasse->id, 'nom' => 'Disciple de la vie', 'niveau' => 1]);
        Capacite::create(['classe_id' => $classe->id, 'nom' => 'Canalisation d’énergie divine', 'niveau' => 2]);

        $personnage = $this->service()->create($donneesPersonnage);

        $this->assertEqualsCanonicalizing(
            [$incantation->id, $disciple->id],
            $personnage->capacites()->pluck('capacites.id')->all(),
        );
    }

    public function test_cree_un_personnage_sans_competences_ni_niveau_fournis(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        unset($donneesPersonnage['competences'], $donneesPersonnage['niveau'], $donneesPersonnage['alignement'], $donneesPersonnage['description'], $donneesPersonnage['sous_race_id'], $donneesPersonnage['sous_classe_id']);

        $personnage = $this->service()->create($donneesPersonnage);

        $this->assertSame(1, $personnage->niveau);
        $this->assertNull($personnage->alignement);
        $this->assertNull($personnage->description);
        $this->assertNull($personnage->sous_race_id);
        $this->assertNull($personnage->sous_classe_id);
        $this->assertSame([], $personnage->listeCompetence->competences);
    }

    public function test_refuse_une_sous_race_qui_ne_correspond_pas_a_la_race(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $autreRace = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($autreRace)->create();

        $donneesPersonnage['sous_race_id'] = $sousRace->id;

        $this->expectException(ValidationException::class);

        try {
            $this->service()->create($donneesPersonnage);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('sous_race_id', $e->errors());

            throw $e;
        }
    }

    public function test_refuse_une_race_inexistante(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $donneesPersonnage['race_id'] = 99999;

        $this->expectException(ValidationException::class);

        try {
            $this->service()->create($donneesPersonnage);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('race_id', $e->errors());

            throw $e;
        }
    }

    public function test_refuse_une_classe_inexistante(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $donneesPersonnage['classe_id'] = 99999;

        $this->expectException(ValidationException::class);

        $this->service()->create($donneesPersonnage);
    }

    public function test_refuse_une_sous_classe_qui_ne_correspond_pas_a_la_classe(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $autreClasse = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);
        $sousClasse = SousClasseFactory::new()->pourClasse($autreClasse)->create();

        $donneesPersonnage['sous_classe_id'] = $sousClasse->id;

        $this->expectException(ValidationException::class);

        try {
            $this->service()->create($donneesPersonnage);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('sous_classe_id', $e->errors());

            throw $e;
        }
    }

    public function test_refuse_une_sous_classe_si_le_niveau_est_insuffisant(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $classe = Classe::find($donneesPersonnage['classe_id']);
        $classe->update(['niveau_sous_classe' => 3]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $donneesPersonnage['sous_classe_id'] = $sousClasse->id;
        $donneesPersonnage['niveau'] = 1;

        $this->expectException(ValidationException::class);

        $this->service()->create($donneesPersonnage);
    }

    public function test_la_transaction_est_annulee_si_la_validation_echoue(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $donneesPersonnage['race_id'] = 99999;

        try {
            $this->service()->create($donneesPersonnage);
        } catch (ValidationException) {
            // Attendu.
        }

        $this->assertDatabaseCount('abilites', 0);
        $this->assertDatabaseCount('listes_competences', 0);
        $this->assertDatabaseCount('personnages', 0);
    }

    /**
     * L'équipement de départ accordé par l'historique est une simple liste
     * de noms narratifs (ex. "Symbole sacré") qui n'existent pas forcément
     * dans le catalogue d'objets de base : ils doivent tout de même se
     * retrouver dans l'inventaire du personnage, créés à la volée plutôt
     * que d'être silencieusement perdus.
     */
    public function test_l_equipement_de_depart_de_l_historique_est_ajoute_a_l_inventaire(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $donneesPersonnage['equipement'] = ['Symbole sacré', 'Symbole sacré', 'Épée'];

        $personnage = $this->service()->create($donneesPersonnage);

        $this->assertSame(2, $personnage->objets()->count());
        $this->assertSame(2, $personnage->objets()->where('nom', 'Symbole sacré')->first()->pivot->quantite);
        $this->assertSame('autre', $personnage->objets()->where('nom', 'Symbole sacré')->first()->type);
        $this->assertSame(1, $personnage->objets()->where('nom', 'Épée')->first()->pivot->quantite);
    }

    /**
     * L'outil choisi à la création n'est plus stocké comme un simple texte
     * sur le personnage (colonne `outil`, retirée) : il doit se retrouver
     * dans l'inventaire réel, comme n'importe quel autre objet de départ.
     */
    public function test_l_outil_choisi_est_ajoute_a_l_inventaire_plutot_que_stocke_en_texte(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $donneesPersonnage['outil'] = 'Kit de calligraphe';

        $personnage = $this->service()->create($donneesPersonnage);

        $this->assertFalse(array_key_exists('outil', $personnage->getAttributes()));
        $objetOutil = $personnage->objets()->where('nom', 'Kit de calligraphe')->first();
        $this->assertNotNull($objetOutil);
        $this->assertSame(1, $objetOutil->pivot->quantite);
        $this->assertSame(0, $objetOutil->pivot->quantite_equipee);
    }

    public function test_aucun_objet_n_est_ajoute_si_aucun_outil_n_est_choisi(): void
    {
        $donneesPersonnage = $this->donneesDeBase();

        $personnage = $this->service()->create($donneesPersonnage);

        $this->assertSame(0, $personnage->objets()->count());
    }

    /**
     * Si l'outil choisi correspond au même objet qu'un équipement déjà
     * accordé par l'historique (ex. l'historique impose "Kit A" en outil
     * fixe et le joueur choisit aussi "Kit A"), les deux sources ne doivent
     * pas créer deux lignes en conflit (contrainte d'unicité), mais
     * cumuler la quantité sur une seule ligne.
     */
    public function test_l_outil_choisi_qui_double_un_equipement_deja_accorde_cumule_la_quantite(): void
    {
        $donneesPersonnage = $this->donneesDeBase();
        $donneesPersonnage['equipement'] = ['Kit de calligraphe'];
        $donneesPersonnage['outil'] = 'Kit de calligraphe';

        $personnage = $this->service()->create($donneesPersonnage);

        $this->assertSame(1, $personnage->objets()->count());
        $this->assertSame(2, $personnage->objets()->where('nom', 'Kit de calligraphe')->first()->pivot->quantite);
    }
}
