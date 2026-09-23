<?php

namespace Tests\Unit;

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

    private function payloadDeBase(): array
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
        $payload = $this->payloadDeBase();

        $personnage = $this->service()->create($payload);

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
        $payload = $this->payloadDeBase();
        $race = Race::find($payload['race_id']);
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $classe = Classe::find($payload['classe_id']);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $payload['sous_race_id'] = $sousRace->id;
        $payload['sous_classe_id'] = $sousClasse->id;

        $personnage = $this->service()->create($payload);

        $this->assertSame($sousRace->id, $personnage->sous_race_id);
        $this->assertSame($sousClasse->id, $personnage->sous_classe_id);
    }

    public function test_cree_un_personnage_sans_competences_ni_niveau_fournis(): void
    {
        $payload = $this->payloadDeBase();
        unset($payload['competences'], $payload['niveau'], $payload['alignement'], $payload['description'], $payload['sous_race_id'], $payload['sous_classe_id']);

        $personnage = $this->service()->create($payload);

        $this->assertSame(1, $personnage->niveau);
        $this->assertNull($personnage->alignement);
        $this->assertNull($personnage->description);
        $this->assertNull($personnage->sous_race_id);
        $this->assertNull($personnage->sous_classe_id);
        $this->assertSame([], $personnage->listeCompetence->competences);
    }

    public function test_refuse_une_sous_race_qui_ne_correspond_pas_a_la_race(): void
    {
        $payload = $this->payloadDeBase();
        $autreRace = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($autreRace)->create();

        $payload['sous_race_id'] = $sousRace->id;

        $this->expectException(ValidationException::class);

        try {
            $this->service()->create($payload);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('sous_race_id', $e->errors());

            throw $e;
        }
    }

    public function test_refuse_une_race_inexistante(): void
    {
        $payload = $this->payloadDeBase();
        $payload['race_id'] = 99999;

        $this->expectException(ValidationException::class);

        try {
            $this->service()->create($payload);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('race_id', $e->errors());

            throw $e;
        }
    }

    public function test_refuse_une_classe_inexistante(): void
    {
        $payload = $this->payloadDeBase();
        $payload['classe_id'] = 99999;

        $this->expectException(ValidationException::class);

        $this->service()->create($payload);
    }

    public function test_refuse_une_sous_classe_qui_ne_correspond_pas_a_la_classe(): void
    {
        $payload = $this->payloadDeBase();
        $autreClasse = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);
        $sousClasse = SousClasseFactory::new()->pourClasse($autreClasse)->create();

        $payload['sous_classe_id'] = $sousClasse->id;

        $this->expectException(ValidationException::class);

        try {
            $this->service()->create($payload);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('sous_classe_id', $e->errors());

            throw $e;
        }
    }

    public function test_refuse_une_sous_classe_si_le_niveau_est_insuffisant(): void
    {
        $payload = $this->payloadDeBase();
        $classe = Classe::find($payload['classe_id']);
        $classe->update(['niveau_sous_classe' => 3]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $payload['sous_classe_id'] = $sousClasse->id;
        $payload['niveau'] = 1;

        $this->expectException(ValidationException::class);

        $this->service()->create($payload);
    }

    public function test_la_transaction_est_annulee_si_la_validation_echoue(): void
    {
        $payload = $this->payloadDeBase();
        $payload['race_id'] = 99999;

        try {
            $this->service()->create($payload);
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
        $payload = $this->payloadDeBase();
        $payload['equipement'] = ['Symbole sacré', 'Symbole sacré', 'Épée'];

        $personnage = $this->service()->create($payload);

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
        $payload = $this->payloadDeBase();
        $payload['outil'] = 'Kit de calligraphe';

        $personnage = $this->service()->create($payload);

        $this->assertFalse(array_key_exists('outil', $personnage->getAttributes()));
        $objetOutil = $personnage->objets()->where('nom', 'Kit de calligraphe')->first();
        $this->assertNotNull($objetOutil);
        $this->assertSame(1, $objetOutil->pivot->quantite);
        $this->assertSame(0, $objetOutil->pivot->quantite_equipee);
    }

    public function test_aucun_objet_n_est_ajoute_si_aucun_outil_n_est_choisi(): void
    {
        $payload = $this->payloadDeBase();

        $personnage = $this->service()->create($payload);

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
        $payload = $this->payloadDeBase();
        $payload['equipement'] = ['Kit de calligraphe'];
        $payload['outil'] = 'Kit de calligraphe';

        $personnage = $this->service()->create($payload);

        $this->assertSame(1, $personnage->objets()->count());
        $this->assertSame(2, $personnage->objets()->where('nom', 'Kit de calligraphe')->first()->pivot->quantite);
    }
}
