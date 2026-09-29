<?php

namespace Tests\Feature;

use App\Models\Attachement;
use App\Models\Capacite;
use App\Models\Defaut;
use App\Models\Historique;
use App\Models\Ideal;
use App\Models\LimiteMagie;
use App\Models\LimiteMagieClasse;
use App\Models\PersonnageSort;
use App\Models\Personnalite;
use App\Models\Sort;
use App\Models\TraitRacial;
use App\Models\Utilisateur;
use App\Services\CapaciteService;
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

class FichePersonnageTest extends TestCase
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
        $raceTrait = TraitRacial::query()->create([
            'nom' => 'Vision dans le noir',
            'niveau' => 1,
        ]);
        $sousRaceTrait = TraitRacial::query()->create([
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

    public function test_la_fiche_propose_les_sorts_connus_si_la_classe_accorde_de_la_magie_au_niveau_atteint(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create(['nom' => 'Clerc']);
        $limite = LimiteMagie::create(['tours_de_magie' => 3, 'sorts_prepares' => 2, 'emplacements_niveau_1' => 2]);
        LimiteMagieClasse::create(['classe_id' => $classe->id, 'limite_magie_id' => $limite->id, 'niveau' => 1, 'caracteristique_magie' => 'Sagesse']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
        ]);

        $this->actingAs($utilisateur)
            ->get("/personnages/{$personnage->id}")
            ->assertOk()
            ->assertSee('Sorts connus');
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
            ->assertDontSee('onchange="this.form.submit()"', false)
            ->assertSee('id="edition_historique_liste" class="formulaire-liste-petit" required', false)
            ->assertSee('id="edition_ideal_liste" class="formulaire-liste-petit" required', false)
            ->assertSee('id="edition_defaut_liste" class="formulaire-liste-petit" required', false)
            ->assertSee('id="edition_attachement_liste" class="formulaire-liste-petit" required', false)
            ->assertSee('id="edition_personnalite_liste" class="formulaire-liste-petit" required', false)
            ->assertSee('Veuillez sélectionner un élément dans la liste.', false)
            ->assertDontSee('Gérer l’inventaire', false)
            ->assertDontSee('Gérer l\'inventaire', false);
    }

    public function test_le_formulaire_edition_preselectionne_l_alignement_calcule_a_la_creation(): void
    {
        // CalculCreationService::alignement() doit produire
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
            ->assertSee('id="sousClasseListe" class="formulaire-liste-petit" disabled', false)
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertRedirect(route('personnages.show', $personnage));
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

    private function donneesMiseAJour(array $surcharges = []): array
    {
        $historique = isset($surcharges['historique_id'])
            ? Historique::find($surcharges['historique_id'])
            : null;
        $historique ??= Historique::create(['nom' => fake()->unique()->words(3, true)]);

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
            'historique_id' => $historique->id,
            'ideal_id' => Ideal::create([
                'historique_id' => $historique->id,
                'valeur' => 'Idéal de test',
            ])->id,
            'defaut_id' => Defaut::create([
                'historique_id' => $historique->id,
                'valeur' => 'Défaut de test',
            ])->id,
            'attachement_id' => Attachement::create([
                'historique_id' => $historique->id,
                'valeur' => 'Attachement de test',
            ])->id,
            'personnalite_id' => Personnalite::create([
                'historique_id' => $historique->id,
                'valeur' => 'Qualité de test',
            ])->id,
        ], $surcharges);
    }

    public function test_un_nouveau_personnage_demarre_avec_tous_ses_points_de_vie(): void
    {
        $personnage = PersonnageFactory::new()->create(['point_de_vie' => 14]);

        $this->assertSame(14, $personnage->fresh()->point_de_vie_actuel);
    }

    public function test_la_fiche_affiche_les_points_de_vie_actuels_sur_le_maximum(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id, 'point_de_vie' => 20]);
        $personnage->update(['point_de_vie_actuel' => 13]);

        $this->actingAs($utilisateur)->get(route('personnages.show', $personnage))
            ->assertOk()
            ->assertSee('13 / 20');
    }

    public function test_des_degats_puis_des_soins_modifient_les_points_de_vie_actuels_dans_les_bornes(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id, 'point_de_vie' => 20]);
        $route = route('personnages.points-de-vie', $personnage);

        $this->actingAs($utilisateur)->patch($route, ['montant' => 8, 'type' => 'degats'])
            ->assertRedirect(route('personnages.show', $personnage));
        $this->assertSame(12, $personnage->fresh()->point_de_vie_actuel);

        $this->actingAs($utilisateur)->patch($route, ['montant' => 50, 'type' => 'soins']);
        $this->assertSame(20, $personnage->fresh()->point_de_vie_actuel);

        $this->actingAs($utilisateur)->patch($route, ['montant' => 99, 'type' => 'degats']);
        $this->assertSame(0, $personnage->fresh()->point_de_vie_actuel);
        $this->assertSame(20, $personnage->fresh()->point_de_vie);
    }

    public function test_ajuster_les_points_de_vie_exige_un_montant_et_un_type_valides(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->patch(route('personnages.points-de-vie', $personnage), ['montant' => 0, 'type' => 'autre'])
            ->assertSessionHasErrors(['montant', 'type']);
    }

    public function test_un_autre_utilisateur_ne_peut_pas_modifier_les_points_de_vie(): void
    {
        $personnage = PersonnageFactory::new()->create(['point_de_vie' => 20]);

        $this->actingAs(Utilisateur::factory()->create())
            ->patch(route('personnages.points-de-vie', $personnage), ['montant' => 5, 'type' => 'degats'])
            ->assertForbidden();

        $this->assertSame(20, $personnage->fresh()->point_de_vie_actuel);
    }

    public function test_le_niveau_et_les_caracteristiques_ne_changent_pas_depuis_l_edition(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'niveau' => 1,
            'experience' => 6500,
        ]);
        $forceAvant = $personnage->abilites->force;

        $this->actingAs($utilisateur)->patch(route('personnages.update', $personnage), $this->donneesMiseAJour([
            'race_id' => $personnage->race_id,
            'classe_id' => $personnage->classe_id,
            'niveau' => 4,
            'force' => 20,
        ]))->assertSessionHasNoErrors();

        $this->assertSame(1, $personnage->fresh()->niveau);
        $this->assertSame($forceAvant, $personnage->abilites->fresh()->force);
    }

    public function test_le_formulaire_d_edition_affiche_le_niveau_et_les_caracteristiques_sans_champ_modifiable(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)->get(route('personnages.edit', $personnage))
            ->assertOk()
            ->assertDontSee('name="niveau"', false)
            ->assertDontSee('name="force"', false)
            ->assertSee('évoluent uniquement par la montée de niveau');
    }

    public function test_modifier_le_maximum_de_pv_conserve_les_degats_subis(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create(['de_de_vie' => '10']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
            'niveau' => 3,
            'point_de_vie' => 20,
        ]);
        $personnage->update(['point_de_vie_actuel' => 15]); // 5 dégâts subis

        $this->actingAs($utilisateur)->patch(route('personnages.update', $personnage), $this->donneesMiseAJour([
            'race_id' => $personnage->race_id,
            'classe_id' => $classe->id,
            'niveau' => 3,
            'point_de_vie' => 24,
        ]))->assertSessionHasNoErrors();

        $this->assertSame(24, $personnage->fresh()->point_de_vie);
        $this->assertSame(19, $personnage->fresh()->point_de_vie_actuel);
    }

    public function test_le_maximum_de_pv_reste_borne_selon_le_niveau_et_la_classe(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $classe = ClasseFactory::new()->create(['de_de_vie' => '10']);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'classe_id' => $classe->id,
            'niveau' => 1,
            'point_de_vie' => 10,
        ]);

        $this->actingAs($utilisateur)->patch(route('personnages.update', $personnage), $this->donneesMiseAJour([
            'race_id' => $personnage->race_id,
            'classe_id' => $classe->id,
            'point_de_vie' => 50,
        ]))->assertSessionHasErrors('point_de_vie');

        $this->assertSame(10, $personnage->fresh()->point_de_vie);
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertRedirect(route('personnages.show', $personnage));
        $reponse->assertSessionHas('succes');

        $personnage->refresh();
        $this->assertSame('Aragorn Mis à jour', $personnage->nom);
        $this->assertSame(8, $personnage->point_de_vie);
        $this->assertSame(['Commun', 'Elfe'], $personnage->langues);
        $this->assertSame(['Athlétisme', 'Perception'], $personnage->listeCompetence->fresh()->competences);
        $this->assertSame(10, $personnage->abilites->fresh()->force);
    }

    public function test_la_mise_a_jour_refuse_une_personnalite_incomplete(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
        ]);
        $donnees = $this->donneesMiseAJour([
            'race_id' => $personnage->race_id,
            'classe_id' => $personnage->classe_id,
        ]);

        foreach (['historique_id', 'ideal_id', 'defaut_id', 'attachement_id', 'personnalite_id'] as $champ) {
            $donneesInvalides = $donnees;
            $donneesInvalides[$champ] = null;

            $this->actingAs($utilisateur)
                ->patch(route('personnages.update', $personnage), $donneesInvalides)
                ->assertSessionHasErrors($champ);
        }

        $personnage->refresh();
        $this->assertNull($personnage->historique_id);
        $this->assertNull($personnage->ideal_id);
        $this->assertNull($personnage->defaut_id);
        $this->assertNull($personnage->attachement_id);
        $this->assertNull($personnage->personnalite_id);
    }

    public function test_la_mise_a_jour_refuse_des_traits_d_un_autre_historique(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
        ]);
        $donnees = $this->donneesMiseAJour([
            'race_id' => $personnage->race_id,
            'classe_id' => $personnage->classe_id,
        ]);
        $autreHistorique = Historique::create(['nom' => 'Historique incompatible']);

        $donnees['ideal_id'] = Ideal::create([
            'historique_id' => $autreHistorique->id,
            'valeur' => 'Idéal incompatible',
        ])->id;
        $donnees['defaut_id'] = Defaut::create([
            'historique_id' => $autreHistorique->id,
            'valeur' => 'Défaut incompatible',
        ])->id;
        $donnees['attachement_id'] = Attachement::create([
            'historique_id' => $autreHistorique->id,
            'valeur' => 'Attachement incompatible',
        ])->id;
        $donnees['personnalite_id'] = Personnalite::create([
            'historique_id' => $autreHistorique->id,
            'valeur' => 'Qualité incompatible',
        ])->id;

        $this->actingAs($utilisateur)
            ->patch(route('personnages.update', $personnage), $donnees)
            ->assertSessionHasErrors(['ideal_id', 'defaut_id', 'attachement_id', 'personnalite_id']);
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasNoErrors();
        $reponse->assertRedirect(route('personnages.show', $personnage));
        $this->assertSame($nouvelleClasse->id, $personnage->refresh()->classe_id);
    }

    private function sort(string $nom): Sort
    {
        return Sort::create([
            'nom' => $nom,
            'description' => 'Sort de test',
            'niveau' => 0,
            'temps_activation' => '1 action',
            'duree' => 'Instantanée',
            'ecole' => 'Évocation',
            'distance' => '18 mètres',
        ]);
    }

    public function test_changer_de_classe_reinitialise_les_sorts_du_personnage(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $ancienneClasse = ClasseFactory::new()->create();
        $nouvelleClasse = ClasseFactory::new()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $ancienneClasse->id,
            'niveau' => 1,
        ]);
        foreach (['Lumière', 'Flamme sacrée'] as $nom) {
            PersonnageSort::create(['personnage_id' => $personnage->id, 'sort_id' => $this->sort($nom)->id, 'preparation' => 1]);
        }

        $reponse = $this->actingAs($utilisateur)->patch("/personnages/{$personnage->id}", $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $nouvelleClasse->id,
        ]));

        $reponse->assertSessionHasNoErrors();
        $reponse->assertSessionHas('succes', fn ($message) => str_contains($message, 'sorts ont été réinitialisés'));
        $this->assertDatabaseMissing('personnage_sorts', ['personnage_id' => $personnage->id]);
    }

    public function test_modifier_le_personnage_sans_changer_de_classe_conserve_les_sorts(): void
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
        $sort = $this->sort('Lumière');
        PersonnageSort::create(['personnage_id' => $personnage->id, 'sort_id' => $sort->id, 'preparation' => 1]);

        $this->actingAs($utilisateur)->patch("/personnages/{$personnage->id}", $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $classe->id,
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('personnage_sorts', ['personnage_id' => $personnage->id, 'sort_id' => $sort->id, 'preparation' => 1]);
    }

    public function test_changer_de_classe_remplace_les_capacites_de_l_ancienne_classe(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $race = RaceFactory::new()->create();
        $ancienneClasse = ClasseFactory::new()->create();
        $nouvelleClasse = ClasseFactory::new()->create();
        Capacite::create(['classe_id' => $ancienneClasse->id, 'nom' => 'Rage', 'niveau' => 1]);
        $nouvelle = Capacite::create(['classe_id' => $nouvelleClasse->id, 'nom' => 'Second souffle', 'niveau' => 1]);
        Capacite::create(['classe_id' => $nouvelleClasse->id, 'nom' => 'Sursaut d’activité', 'niveau' => 2]);
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'race_id' => $race->id,
            'classe_id' => $ancienneClasse->id,
            'niveau' => 1,
        ]);
        app(CapaciteService::class)->synchroniser($personnage);

        $this->actingAs($utilisateur)->patch("/personnages/{$personnage->id}", $this->donneesMiseAJour([
            'race_id' => $race->id,
            'classe_id' => $nouvelleClasse->id,
        ]))->assertSessionHasNoErrors();

        $this->assertSame([$nouvelle->id], $personnage->refresh()->capacites->modelKeys());
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasNoErrors();
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertRedirect(route('personnages.show', $personnage));
        $personnage->refresh();
        $this->assertSame('Nom conservé', $personnage->nom);
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasNoErrors();
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasNoErrors();
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasErrors(['sous_race_id']);
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasErrors(['sous_classe_id']);
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasNoErrors();
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasErrors(['sous_classe_id']);
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasErrors(['competences']);
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

        $reponse = $this->actingAs($utilisateur)
            ->patch("/personnages/{$personnage->id}", $donnees);

        $reponse->assertSessionHasErrors(['langues']);
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
