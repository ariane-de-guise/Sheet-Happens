<?php

namespace Tests\Feature;

use App\Models\Historique;
use App\Models\TraitModel;
use App\Models\Utilisateur;
use Database\Factories\ClasseFactory;
use Database\Factories\ObjetFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\PersonnageObjetFactory;
use Database\Factories\RaceFactory;
use Database\Factories\SousClasseFactory;
use Database\Factories\SousRaceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonnageSheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_liste_est_vide_pour_un_utilisateur_sans_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();

        $this->actingAs($utilisateur)
            ->get('/personnages')
            ->assertOk()
            ->assertSee("Vous n'avez pas encore de personnage.", false);
    }

    public function test_la_liste_affiche_les_personnages_de_l_utilisateur_connecte(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();

        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'nom' => 'Gimli',
        ]);

        PersonnageFactory::new()->create([
            'utilisateur_id' => $autre->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'nom' => 'Boromir',
        ]);

        $this->actingAs($utilisateur)
            ->get('/personnages')
            ->assertOk()
            ->assertSee('Gimli')
            ->assertDontSee('Boromir');
    }

    public function test_un_proprietaire_peut_afficher_la_fiche_de_son_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'alignement' => 'Loyal bon',
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}")
            ->assertOk()
            ->assertSee($personnage->nom)
            ->assertSee('Loyal bon');
    }

    public function test_la_page_des_traits_transmet_les_traits_de_la_race_et_de_la_sous_race(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'sous_race_id' => $sousRace->id,
        ]);
        $raceTrait = TraitModel::query()->create([
            'nom' => 'Vision dans le noir',
            'niveau' => 1,
        ]);
        $sousRaceTrait = TraitModel::query()->create([
            'nom' => 'Résilience',
            'niveau' => 1,
        ]);
        $race->traits()->attach($raceTrait);
        $sousRace->traits()->attach($sousRaceTrait);

        $this->actingAs($utilisateur)
            ->get(route('personnages.traits', $personnage))
            ->assertOk()
            ->assertViewHas('raceTraits', fn ($traits) => $traits->contains($raceTrait))
            ->assertViewHas('sousRaceTraits', fn ($traits) => $traits->contains($sousRaceTrait));
    }

    public function test_un_autre_utilisateur_ne_peut_pas_voir_la_fiche(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);

        $this->actingAs($autre)
            ->get("/personnages/{$personnage->id}")
            ->assertForbidden();
    }

    public function test_la_fiche_affiche_les_jets_de_sauvegarde_les_competences_et_les_pv(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create(['nom' => 'Guerrier']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
            'point_de_vie' => 27,
            'langues' => ['Commun', 'Elfe'],
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}")
            ->assertOk()
            ->assertSee('Jets de sauvegarde')
            ->assertSee('Compétences')
            ->assertSee('Points de vie')
            ->assertSee('27')
            ->assertSee('Bonus de maîtrise')
            ->assertSee('Perception passive')
            ->assertSee('Commun, Elfe');
    }

    public function test_la_fiche_affiche_des_attaques_pour_une_classe_non_lanceuse_de_sorts(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create(['nom' => 'Guerrier']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
        ]);
        $epee = ObjetFactory::new()->arme()->create(['nom' => 'Épée']);
        PersonnageObjetFactory::new()->equipe()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $epee->id,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}")
            ->assertOk()
            ->assertSee('Actions')
            ->assertSee('Épée')
            ->assertSee('pour toucher');
    }

    public function test_la_fiche_affiche_des_sorts_pour_une_classe_lanceuse_de_sorts(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create(['nom' => 'Magicien']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}")
            ->assertOk()
            ->assertSee('Actions')
            ->assertSee('Jet de sauvegarde de tes sorts')
            ->assertSee('attaque de sorts')
            ->assertDontSee('DD de sauvegarde des sorts');
    }

    public function test_la_fiche_mentionne_la_sous_classe_dans_la_section_actions_le_cas_echeant(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create(['nom' => 'Guerrier']);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create(['nom' => 'Champion']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}")
            ->assertOk()
            ->assertSee('Sous-classe : Champion', false);
    }

    public function test_la_section_traits_est_vide(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create(['description' => 'Une race curieuse.']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}")
            ->assertOk()
            ->assertSee('Traits')
            ->assertDontSee('Une race curieuse.');
    }

    public function test_la_fiche_affiche_l_inventaire_avec_un_lien_vers_la_page_complete(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $sacADos = ObjetFactory::new()->create(['nom' => 'Sac à dos']);
        PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $sacADos->id,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}")
            ->assertOk()
            ->assertSee('Inventaire')
            ->assertSee('Sac à dos')
            ->assertSee(route('personnages.inventaire.index', $personnage), false);
    }

    public function test_un_proprietaire_peut_consulter_l_inventaire_de_son_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $sacADos = ObjetFactory::new()->create(['nom' => 'Sac à dos']);
        $epee = ObjetFactory::new()->arme()->create(['nom' => 'Épée']);
        $bouclier = ObjetFactory::new()->bouclier()->create(['nom' => 'Bouclier']);

        foreach ([$sacADos, $epee, $bouclier] as $objet) {
            PersonnageObjetFactory::new()->create([
                'personnage_id' => $personnage->id,
                'objet_id' => $objet->id,
            ]);
        }

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/inventaire")
            ->assertOk()
            ->assertSee('Inventaire de '.$personnage->nom)
            ->assertSee('Sac à dos')
            ->assertSee('Épée')
            ->assertSee('Bouclier');
    }

    public function test_un_autre_utilisateur_ne_peut_pas_consulter_l_inventaire(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);

        $this->actingAs($autre)
            ->get("/personnages/{$personnage->id}/inventaire")
            ->assertForbidden();
    }

    public function test_un_proprietaire_peut_afficher_le_formulaire_edition(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertOk()
            ->assertDontSee('onchange="this.form.submit()"', false);
    }

    public function test_le_formulaire_edition_preselectionne_l_alignement_calcule_a_la_creation(): void
    {
        // calculateAlignement() (CreationPersonnageController) doit produire
        // exactement les mêmes chaînes que les options du select
        // "Alignement" ci-dessous, sinon la valeur enregistrée en base ne
        // s'y retrouve jamais présélectionnée (elle affiche "-- Choisir --"
        // au premier chargement).
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'alignement' => 'Chaotique neutre',
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertOk()
            ->assertSee('<option value="Chaotique neutre" selected>', false);
    }

    public function test_le_formulaire_edition_affiche_et_preselectionne_la_sous_race_actuelle(): void
    {
        // La sous-race doit être visible dès le chargement de la page (pas
        // seulement après un appel JS asynchrone), sinon un souci réseau ou
        // JS côté client la ferait disparaître de l'écran alors qu'elle est
        // bien enregistrée en base.
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $autreSousRace = SousRaceFactory::new()->pourRace($race)->create(['nom' => 'Elfe des bois']);
        $sousRace = SousRaceFactory::new()->pourRace($race)->create(['nom' => 'Haut Elfe']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertOk()
            ->assertSee('Haut Elfe', false)
            ->assertSee('Elfe des bois', false)
            ->assertSee('<option value="'.$sousRace->id.'" selected>', false);
    }

    public function test_le_formulaire_edition_bloque_la_sous_classe_si_le_niveau_est_insuffisant(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 3]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create(['nom' => 'Voie du berserker']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertOk()
            ->assertDontSee('<option value="'.$sousClasse->id.'">', false)
            ->assertSee('id="sousClasseSelect" class="form-select-small" disabled', false)
            ->assertSee('Sous-classe disponible au niveau 3', false);
    }

    public function test_une_competence_d_historique_est_verrouillee_dans_le_formulaire(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $historique = Historique::create([
            'nom' => 'Sage',
            'competences' => ['Religion'],
        ]);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'historique_id' => $historique->id,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertOk()
            ->assertSee('value="Religion"', false)
            ->assertSee('disabled', false);
    }

    public function test_une_classe_avec_quatre_competences_peut_en_remplacer_une(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create([
            'nombre_competences' => 4,
            'competences_disponibles' => ['Acrobaties', 'Athlétisme', 'Nature', 'Perception', 'Survie'],
        ]);
        $historique = Historique::create([
            'nom' => 'Sage',
            'competences' => ['Religion', 'Histoire'],
        ]);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'historique_id' => $historique->id,
        ]);
        $competences = ['Acrobaties', 'Athlétisme', 'Nature', 'Survie', 'Religion', 'Histoire'];
        $personnage->listeCompetence->update(['competences' => $competences]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertOk()
            ->assertSee('Compétences de classe (max 4)', false)
            ->assertSee('Compétences d’historique incluses et non modifiables : Religion, Histoire', false);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'competences' => ['Acrobaties', 'Athlétisme', 'Perception', 'Survie', 'Religion', 'Histoire'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertRedirect(route('personnages.show', $personnage));
        $this->assertSame(
            ['Acrobaties', 'Athlétisme', 'Perception', 'Survie', 'Religion', 'Histoire'],
            $personnage->refresh()->listeCompetence->competences,
        );
    }

    public function test_un_autre_utilisateur_ne_peut_pas_modifier_le_personnage(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);

        $this->actingAs($autre)
            ->get("/personnages/{$personnage->id}/edit")
            ->assertForbidden();

        $this->actingAs($autre)
            ->patch("/personnages/{$personnage->id}", [])
            ->assertForbidden();
    }

    private function donneesMiseAJour(array $overrides = []): array
    {
        return array_merge([
            'nom' => 'Aragorn Mis à jour',
            'niveau' => 1,
            'alignement' => 'Neutre bon',
            'description' => 'Roi du Gondor',
            'point_de_vie' => 10,
            'langues' => ['Commun', 'Elfe'],
            'force' => 10,
            'dexterite' => 10,
            'constitution' => 10,
            'intelligence' => 10,
            'sagesse' => 10,
            'charisme' => 10,
        ], $overrides);
    }

    public function test_un_proprietaire_peut_mettre_a_jour_son_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
            'point_de_vie' => 8,
            'force' => 10,
            'competences' => ['Athlétisme', 'Perception'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertRedirect(route('personnages.show', $personnage));
        $response->assertSessionHas('success');

        $personnage->refresh();
        $this->assertSame('Aragorn Mis à jour', $personnage->nom);
        $this->assertSame(8, $personnage->point_de_vie);
        $this->assertSame(['Commun', 'Elfe'], $personnage->langues);
        $this->assertSame(['Athlétisme', 'Perception'], $personnage->listeCompetence->fresh()->competences);
        $this->assertSame(10, $personnage->abilites->fresh()->force);
    }

    public function test_un_proprietaire_peut_changer_la_classe_de_son_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $ancienneClasse = ClasseFactory::new()->create(['nombre_competences' => 2]);
        $nouvelleClasse = ClasseFactory::new()->create(['nombre_competences' => 4]);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $ancienneClasse->id,
            'niveau' => 1,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $nouvelleClasse->id,
            'niveau' => 1,
            'competences' => ['Athlétisme', 'Perception', 'Nature', 'Survie'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('personnages.show', $personnage));
        $this->assertSame($nouvelleClasse->id, $personnage->refresh()->classe_id);
    }

    public function test_un_proprietaire_peut_modifier_uniquement_le_niveau(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'niveau' => 1,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", ['niveau' => 2]);

        $response->assertRedirect(route('personnages.show', $personnage));
        $this->assertSame(2, $personnage->refresh()->niveau);
    }

    public function test_un_proprietaire_peut_modifier_uniquement_les_points_de_vie(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'point_de_vie' => 8,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", ['point_de_vie' => 25]);

        $response->assertSessionHasErrors(['point_de_vie']);
        $this->assertSame(8, $personnage->refresh()->point_de_vie);
    }

    public function test_la_mise_a_jour_efface_les_listes_quand_toutes_les_options_sont_decoches(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'langues' => ['Commun'],
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $personnage->race_id,
            'classe_id' => $personnage->classe_id,
            'langues' => [],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $personnage->refresh();
        $this->assertSame([], $personnage->langues);
        $this->assertSame([], $personnage->listeCompetence->fresh()->competences);
    }

    public function test_la_mise_a_jour_conserve_des_competences_existantes_au_dela_de_la_limite(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'niveau' => 1,
        ]);
        $competences = ['Athlétisme', 'Perception', 'Nature'];
        $personnage->listeCompetence->update(['competences' => $competences]);

        $donnees = $this->donneesMiseAJour([
            'nom' => 'Nom conservé',
            'race_id' => $personnage->race_id,
            'classe_id' => $personnage->classe_id,
            'competences' => $competences,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertRedirect(route('personnages.show', $personnage));
        $personnage->refresh();
        $this->assertSame('Nom conservé', $personnage->nom);
        $this->assertSame($competences, $personnage->listeCompetence->fresh()->competences);
    }

    public function test_les_statistiques_pv_et_niveau_sont_acceptes_avec_un_asi_valide(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'niveau' => 3,
            'point_de_vie' => 10,
        ]);
        $competences = ['Athlétisme', 'Perception', 'Nature'];
        $personnage->listeCompetence->update(['competences' => $competences]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $personnage->race_id,
            'classe_id' => $personnage->classe_id,
            'niveau' => 4,
            'point_de_vie' => 20,
            'force' => 12,
            'competences' => $competences,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertRedirect(route('personnages.show', $personnage));
        $personnage->refresh();
        $this->assertSame(4, $personnage->niveau);
        $this->assertSame(20, $personnage->point_de_vie);
        $this->assertSame(12, $personnage->abilites->fresh()->force);
        $this->assertSame($competences, $personnage->listeCompetence->fresh()->competences);
    }

    public function test_la_mise_a_jour_conserve_une_sous_race_valide_meme_si_race_id_est_une_chaine(): void
    {
        // Les requêtes HTTP envoient toujours race_id sous forme de chaîne
        // (les champs de formulaire HTML n'ont pas de typage). La
        // comparaison ne doit donc pas être stricte (!==) sous peine de
        // neutraliser systématiquement la sous-race à chaque sauvegarde,
        // même sans changement de race.
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => (string) $race->id,
            'classe_id' => $classe->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $personnage->refresh();
        $this->assertSame($sousRace->id, $personnage->sous_race_id);
    }

    public function test_la_mise_a_jour_accepte_une_sous_race_valide(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $sousRace = SousRaceFactory::new()->pourRace($race)->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'sous_race_id' => $sousRace->id,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $personnage->refresh();
        $this->assertSame($sousRace->id, $personnage->sous_race_id);
    }

    public function test_la_mise_a_jour_refuse_une_race_avec_sous_races_sans_sous_race(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $ancienneRace = RaceFactory::new()->create();
        $nouvelleRace = RaceFactory::new()->create();
        SousRaceFactory::new()->pourRace($nouvelleRace)->create();
        $classe = ClasseFactory::new()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $ancienneRace->id,
            'classe_id' => $classe->id,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $nouvelleRace->id,
            'classe_id' => $classe->id,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasErrors(['sous_race_id']);
        $this->assertSame($ancienneRace->id, $personnage->refresh()->race_id);
    }

    public function test_la_mise_a_jour_refuse_une_sous_classe_dont_le_niveau_requis_est_trop_eleve(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 3]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
            'niveau' => 1,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasErrors(['sous_classe_id']);
        $personnage->refresh();
        $this->assertNull($personnage->sous_classe_id);
    }

    public function test_la_mise_a_jour_accepte_une_sous_classe_valide_au_bon_niveau(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);
        $sousClasse = SousClasseFactory::new()->pourClasse($classe)->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'sous_classe_id' => $sousClasse->id,
            'niveau' => 1,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasNoErrors();
        $personnage->refresh();
        $this->assertSame($sousClasse->id, $personnage->sous_classe_id);
    }

    public function test_la_mise_a_jour_refuse_une_classe_avec_sous_classes_sans_sous_classe(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $ancienneClasse = ClasseFactory::new()->create();
        $nouvelleClasse = ClasseFactory::new()->create(['niveau_sous_classe' => 1]);
        SousClasseFactory::new()->pourClasse($nouvelleClasse)->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $ancienneClasse->id,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $nouvelleClasse->id,
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasErrors(['sous_classe_id']);
        $this->assertSame($ancienneClasse->id, $personnage->refresh()->classe_id);
    }

    public function test_la_mise_a_jour_respecte_la_limite_de_competences_selon_le_niveau(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
            'competences' => ['Athlétisme', 'Perception', 'Nature'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasErrors(['competences']);
    }

    public function test_la_mise_a_jour_respecte_la_limite_de_langues_selon_le_niveau(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $classe = ClasseFactory::new()->create();

        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
        ]);

        $donnees = $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
            'langues' => ['Commun', 'Elfe', 'Nain'],
        ]);

        $response = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $response->assertSessionHasErrors(['langues']);
    }

    public function test_un_proprietaire_peut_changer_la_photo_de_son_personnage(): void
    {
        Storage::fake('public');

        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $photo = UploadedFile::fake()->image('portrait.jpg');

        $this->actingAs($utilisateur)
            ->patch(route('personnages.photo', $personnage), ['photo' => $photo])
            ->assertRedirect(route('personnages.show', $personnage));

        $personnage->refresh();
        $this->assertNotNull($personnage->photo);
        Storage::disk('public')->assertExists($personnage->photo);
    }

    public function test_changer_la_photo_supprime_l_ancienne(): void
    {
        Storage::fake('public');

        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)->patch(route('personnages.photo', $personnage), [
            'photo' => UploadedFile::fake()->image('premiere.jpg'),
        ]);
        $anciennePhoto = $personnage->refresh()->photo;

        $this->actingAs($utilisateur)->patch(route('personnages.photo', $personnage), [
            'photo' => UploadedFile::fake()->image('seconde.jpg'),
        ]);

        Storage::disk('public')->assertMissing($anciennePhoto);
        Storage::disk('public')->assertExists($personnage->refresh()->photo);
    }

    public function test_une_photo_non_image_est_rejetee(): void
    {
        Storage::fake('public');

        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->patch(route('personnages.photo', $personnage), [
                'photo' => UploadedFile::fake()->create('document.pdf', 100),
            ])
            ->assertSessionHasErrors('photo');
    }

    public function test_un_autre_utilisateur_ne_peut_pas_changer_la_photo(): void
    {
        Storage::fake('public');

        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);

        $this->actingAs($autre)
            ->patch(route('personnages.photo', $personnage), [
                'photo' => UploadedFile::fake()->image('portrait.jpg'),
            ])
            ->assertForbidden();
    }
}
