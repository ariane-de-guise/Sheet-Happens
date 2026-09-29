<?php

namespace Tests\Feature;

use App\Models\EffetActif;
use App\Models\Objet;
use App\Models\Utilisateur;
use Database\Factories\ObjetFactory;
use Database\Factories\PersonnageFactory;
use Database\Factories\PersonnageObjetFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventaireControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_proprietaire_peut_consulter_la_page_d_ajout_d_objet(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        ObjetFactory::new()->create(['nom' => 'Corde']);

        $this->actingAs($utilisateur)
            ->get(route('personnages.inventaire.ajouter-page', $personnage))
            ->assertOk()
            ->assertSee('Corde')
            ->assertSee('+ Objet custom', false);
    }

    public function test_un_autre_utilisateur_ne_peut_pas_consulter_la_page_d_ajout_d_objet(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);

        $this->actingAs($autre)
            ->get(route('personnages.inventaire.ajouter-page', $personnage))
            ->assertForbidden();
    }

    public function test_un_proprietaire_peut_consulter_la_page_d_objet_custom(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->get(route('personnages.inventaire.ajouter-personnalise-page', $personnage))
            ->assertOk()
            ->assertSee('Créer un objet custom')
            ->assertSee('Objet magique');
    }

    public function test_un_autre_utilisateur_ne_peut_pas_consulter_la_page_d_objet_custom(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);

        $this->actingAs($autre)
            ->get(route('personnages.inventaire.ajouter-personnalise-page', $personnage))
            ->assertForbidden();
    }

    public function test_la_page_d_inventaire_affiche_l_equipement_porte_separement(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $epeeEquipee = ObjetFactory::new()->arme()->create(['nom' => 'Épée équipée']);
        PersonnageObjetFactory::new()->equipe()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $epeeEquipee->id,
        ]);

        $sacADos = ObjetFactory::new()->create(['nom' => 'Sac à dos']);
        PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $sacADos->id,
        ]);

        $this->actingAs($utilisateur)
            ->get(route('personnages.inventaire.index', $personnage))
            ->assertOk()
            ->assertSee('Équipement porté')
            ->assertSee('Épée équipée')
            ->assertDontSee('Ajouter un objet du catalogue');
    }

    public function test_la_page_d_inventaire_affiche_toutes_les_stats_d_un_objet_magique(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $epeeMagique = ObjetFactory::new()->arme()->magique('rare')->create([
            'nom' => 'Épée +2',
            'bonus' => ['bonus_attaque' => 2, 'bonus_degats' => 2],
        ]);
        PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $epeeMagique->id,
        ]);

        $this->actingAs($utilisateur)
            ->get(route('personnages.inventaire.index', $personnage))
            ->assertOk()
            ->assertSee('Épée +2')
            ->assertSee('Rare')
            ->assertSee('Dégâts : 1d8 tranchant (Force)')
            ->assertSee('+2 au toucher, +2 aux dégâts', false);
    }

    public function test_un_proprietaire_peut_ajouter_un_objet_du_catalogue(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $objet = ObjetFactory::new()->create(['nom' => 'Corde']);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.ajouter', $personnage), [
                'objet_id' => $objet->id,
                'quantite' => 3,
            ])
            ->assertRedirect(route('personnages.inventaire.index', $personnage));

        $this->assertSame(3, $personnage->objets()->first()->pivot->quantite);
    }

    public function test_un_autre_utilisateur_ne_peut_pas_ajouter_un_objet(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);
        $objet = ObjetFactory::new()->create();

        $this->actingAs($autre)
            ->post(route('personnages.inventaire.ajouter', $personnage), ['objet_id' => $objet->id])
            ->assertForbidden();
    }

    public function test_creer_un_objet_custom_l_ajoute_au_catalogue_et_a_l_inventaire(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.ajouter-personnalise', $personnage), [
                'nom' => 'Amulette du hasard',
                'description' => 'Un porte-bonheur douteux.',
                'type' => 'autre',
                'bonus' => ['charisme' => 1],
                'bonus_toujours_actif' => '1',
                'quantite' => 1,
            ])
            ->assertRedirect(route('personnages.inventaire.index', $personnage));

        $objet = Objet::where('nom', 'Amulette du hasard')->firstOrFail();
        $this->assertSame($utilisateur->id, $objet->utilisateur_id);
        $this->assertSame(['charisme' => 1], $objet->bonus);
        $this->assertTrue($objet->bonus_toujours_actif);
        $this->assertSame(1, $personnage->objets()->where('objet_id', $objet->id)->count());
    }

    public function test_creer_une_potion_custom_sans_duree_ignore_le_bonus_saisi(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.ajouter-personnalise', $personnage), [
                'nom' => 'Décoction de courage',
                'description' => 'Un liquide amer.',
                'type' => 'potion',
                'effet_pv' => 5,
                'bonus' => ['force' => 4],
                'bonus_toujours_actif' => '1',
            ])
            ->assertRedirect(route('personnages.inventaire.index', $personnage));

        $objet = Objet::where('nom', 'Décoction de courage')->firstOrFail();
        $this->assertTrue($objet->est_consommable);
        $this->assertSame(['pv' => 5], $objet->effet_utilisation);
        $this->assertNull($objet->bonus);
        $this->assertFalse($objet->bonus_toujours_actif);
        $this->assertSame('Un liquide amer.', $objet->description);
    }

    public function test_creer_une_potion_custom_avec_duree_et_bonus_cree_un_effet_temporaire(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.ajouter-personnalise', $personnage), [
                'nom' => 'Élixir de vigueur',
                'description' => 'Un liquide amer.',
                'type' => 'potion',
                'duree_valeur' => 10,
                'duree_unite' => 'tours',
                'bonus' => ['force' => 4],
            ])
            ->assertRedirect(route('personnages.inventaire.index', $personnage));

        $objet = Objet::where('nom', 'Élixir de vigueur')->firstOrFail();
        $this->assertSame(['force' => 4], $objet->effet_utilisation['bonus']);
        $this->assertSame(10, $objet->effet_utilisation['duree_valeur']);
        $this->assertSame('tours', $objet->effet_utilisation['duree_unite']);
        $this->assertSame(60, $objet->effet_utilisation['duree_secondes']); // 10 tours * 6 secondes
        $this->assertNull($objet->bonus);
        $this->assertStringContainsString('Un liquide amer.', $objet->description);
        $this->assertStringContainsString('Durée : 10 tours.', $objet->description);
    }

    public function test_utiliser_une_potion_custom_a_bonus_en_minutes_cree_un_effet_qui_expire(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $potion = ObjetFactory::new()->create([
            'type' => 'potion',
            'est_consommable' => true,
            'effet_utilisation' => ['bonus' => ['force' => 4], 'duree_valeur' => 1, 'duree_unite' => 'minutes', 'duree_secondes' => 60],
        ]);
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $potion->id,
        ]);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.utiliser', [$personnage, $ligne]))
            ->assertRedirect();

        $effet = $personnage->effetsActifs()->first();
        $this->assertNotNull($effet);
        $this->assertSame('minutes', $effet->unite);
        $this->assertSame(['force' => 4], $effet->bonus);
        $this->assertTrue($effet->expire_a->isFuture());
    }

    public function test_utiliser_une_potion_custom_a_bonus_en_tours_cree_un_effet_decompte_a_la_main(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $potion = ObjetFactory::new()->create([
            'type' => 'potion',
            'est_consommable' => true,
            'effet_utilisation' => ['bonus' => ['bonus_degats' => 2], 'duree_valeur' => 10, 'duree_unite' => 'tours', 'duree_secondes' => 60],
        ]);
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $potion->id,
        ]);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.utiliser', [$personnage, $ligne]))
            ->assertRedirect();

        $effet = $personnage->effetsActifs()->first();
        $this->assertNotNull($effet);
        $this->assertSame('tours', $effet->unite);
        $this->assertSame(10, $effet->tours_restants);
        $this->assertNull($effet->expire_a);
    }

    public function test_un_proprietaire_peut_decompter_les_tours_d_un_effet_actif(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $effet = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Élixir de rage',
            'bonus' => ['bonus_degats' => 2],
            'unite' => 'tours',
            'tours_restants' => 5,
        ]);

        $this->actingAs($utilisateur)
            ->patch(route('personnages.inventaire.effets.tours', [$personnage, $effet]), ['delta' => -2])
            ->assertRedirect();

        $this->assertSame(3, $effet->fresh()->tours_restants);
    }

    public function test_un_autre_utilisateur_ne_peut_pas_decompter_les_tours_d_un_effet(): void
    {
        $proprietaire = Utilisateur::factory()->create();
        $autre = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $proprietaire->id]);
        $effet = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Élixir de rage',
            'bonus' => ['bonus_degats' => 2],
            'unite' => 'tours',
            'tours_restants' => 5,
        ]);

        $this->actingAs($autre)
            ->patch(route('personnages.inventaire.effets.tours', [$personnage, $effet]), ['delta' => -2])
            ->assertForbidden();
    }

    public function test_creer_une_arme_custom_magique_avec_degats_et_rarete(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.ajouter-personnalise', $personnage), [
                'nom' => 'Lame des étoiles',
                'type' => 'arme',
                'categorie' => 'une_main',
                'degats' => '1d8',
                'type_degats' => 'tranchant',
                'caracteristique_degats' => 'dexterite',
                'magique' => '1',
                'rarete' => 'tres_rare',
                'bonus' => ['bonus_attaque' => 2, 'bonus_degats' => 2],
            ])
            ->assertRedirect(route('personnages.inventaire.index', $personnage));

        $objet = Objet::where('nom', 'Lame des étoiles')->firstOrFail();
        $this->assertTrue($objet->magique);
        $this->assertSame('tres_rare', $objet->rarete);
        $this->assertSame('1d8', $objet->degats);
        $this->assertSame('dexterite', $objet->caracteristique_degats);
        $this->assertSame(['bonus_attaque' => 2, 'bonus_degats' => 2], $objet->bonus);
        $this->assertSame(1, $objet->cout_mains);
    }

    public function test_creer_une_arme_custom_a_distance_avec_une_main_respecte_le_choix_explicite(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.ajouter-personnalise', $personnage), [
                'nom' => 'Arbalète de poing improvisée',
                'type' => 'arme',
                'categorie' => 'distance',
                'cout_mains' => 1,
                'degats' => '1d6',
                'type_degats' => 'perforant',
                'caracteristique_degats' => 'dexterite',
            ])
            ->assertRedirect(route('personnages.inventaire.index', $personnage));

        $objet = Objet::where('nom', 'Arbalète de poing improvisée')->firstOrFail();
        $this->assertSame(1, $objet->cout_mains);
    }

    public function test_un_objet_non_magique_ignore_la_rarete_envoyee(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)->post(route('personnages.inventaire.ajouter-personnalise', $personnage), [
            'nom' => 'Bâton ordinaire',
            'type' => 'autre',
            'rarete' => 'legendaire',
        ]);

        $objet = Objet::where('nom', 'Bâton ordinaire')->firstOrFail();
        $this->assertFalse($objet->magique);
        $this->assertNull($objet->rarete);
    }

    public function test_un_objet_custom_cree_est_ensuite_propose_dans_le_catalogue(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->actingAs($utilisateur)->post(route('personnages.inventaire.ajouter-personnalise', $personnage), [
            'nom' => 'Grimoire personnel',
            'type' => 'autre',
        ]);

        $this->actingAs($utilisateur)
            ->get(route('personnages.inventaire.ajouter-page', $personnage))
            ->assertOk()
            ->assertSee('Grimoire personnel');
    }

    public function test_equiper_une_deuxieme_armure_est_refuse_avec_un_message_d_erreur(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $cuir = ObjetFactory::new()->armure('legere', 11)->create();
        $plates = ObjetFactory::new()->armure('lourde', 18)->create();

        $ligneCuir = PersonnageObjetFactory::new()->equipe()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $cuir->id,
        ]);
        $lignePlates = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $plates->id,
        ]);

        $this->actingAs($utilisateur)
            ->patch(route('personnages.inventaire.equiper', [$personnage, $lignePlates]))
            ->assertRedirect()
            ->assertSessionHasErrors('objet');

        $this->assertSame(0, $lignePlates->fresh()->quantite_equipee);
    }

    public function test_un_proprietaire_peut_equiper_une_arme(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $epee = ObjetFactory::new()->arme()->create();
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $epee->id,
        ]);

        $this->actingAs($utilisateur)
            ->patch(route('personnages.inventaire.equiper', [$personnage, $ligne]))
            ->assertRedirect()
            ->assertSessionHas('succes');

        $this->assertSame(1, $ligne->fresh()->quantite_equipee);
    }

    public function test_un_proprietaire_peut_desequiper_une_arme(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $epee = ObjetFactory::new()->arme()->create();
        $ligne = PersonnageObjetFactory::new()->equipe()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $epee->id,
        ]);

        $this->actingAs($utilisateur)
            ->patch(route('personnages.inventaire.desequiper', [$personnage, $ligne]))
            ->assertRedirect()
            ->assertSessionHas('succes');

        $this->assertSame(0, $ligne->fresh()->quantite_equipee);
    }

    public function test_un_proprietaire_peut_equiper_deux_exemplaires_de_la_meme_arme(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $dague = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague']);
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $dague->id,
            'quantite' => 2,
        ]);

        $this->actingAs($utilisateur)
            ->patch(route('personnages.inventaire.equiper', [$personnage, $ligne]))
            ->assertRedirect();
        $this->actingAs($utilisateur)
            ->patch(route('personnages.inventaire.equiper', [$personnage, $ligne]))
            ->assertRedirect()
            ->assertSessionHas('succes');

        $this->assertSame(2, $ligne->fresh()->quantite_equipee);
    }

    public function test_la_page_d_inventaire_affiche_le_nombre_de_mains_d_une_arme(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $dague = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague']);
        $arc = ObjetFactory::new()->arme('distance', 2)->create(['nom' => 'Arc long']);
        PersonnageObjetFactory::new()->create(['personnage_id' => $personnage->id, 'objet_id' => $dague->id]);
        PersonnageObjetFactory::new()->create(['personnage_id' => $personnage->id, 'objet_id' => $arc->id]);

        $this->actingAs($utilisateur)
            ->get(route('personnages.inventaire.index', $personnage))
            ->assertOk()
            ->assertSee('Une main')
            ->assertSee('Deux mains');
    }

    public function test_un_proprietaire_peut_ajuster_la_quantite_d_un_objet(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $objet = ObjetFactory::new()->create();
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $objet->id,
            'quantite' => 2,
        ]);

        $this->actingAs($utilisateur)
            ->patch(route('personnages.inventaire.quantite', [$personnage, $ligne]), ['delta' => 3])
            ->assertRedirect();

        $this->assertSame(5, $ligne->fresh()->quantite);
    }

    public function test_le_bouton_moins_ne_retire_pas_le_dernier_exemplaire_d_un_objet(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $objet = ObjetFactory::new()->create();
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $objet->id,
            'quantite' => 1,
        ]);

        $this->actingAs($utilisateur)
            ->patch(route('personnages.inventaire.quantite', [$personnage, $ligne]), ['delta' => -1])
            ->assertRedirect();

        $this->assertSame(1, $ligne->fresh()->quantite);
        $this->assertSame(1, $personnage->objets()->count());
    }

    public function test_utiliser_un_objet_non_consommable_echoue_avec_un_message_d_erreur(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $epee = ObjetFactory::new()->arme()->create();
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $epee->id,
        ]);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.utiliser', [$personnage, $ligne]))
            ->assertRedirect()
            ->assertSessionHasErrors('objet');

        $this->assertSame(1, $personnage->objets()->count());
    }

    public function test_utiliser_une_potion_applique_son_effet_et_retire_une_unite(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
            'point_de_vie' => 20,
            'point_de_vie_actuel' => 5,
        ]);
        $potion = ObjetFactory::new()->potion(['pv' => 7])->create();
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $potion->id,
            'quantite' => 1,
        ]);

        $this->actingAs($utilisateur)
            ->post(route('personnages.inventaire.utiliser', [$personnage, $ligne]))
            ->assertRedirect();

        $this->assertSame(12, $personnage->fresh()->point_de_vie_actuel);
        $this->assertSame(0, $personnage->objets()->count());
    }

    public function test_retirer_supprime_la_ligne_d_inventaire(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $objet = ObjetFactory::new()->create();
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $personnage->id,
            'objet_id' => $objet->id,
        ]);

        $this->actingAs($utilisateur)
            ->delete(route('personnages.inventaire.retirer', [$personnage, $ligne]))
            ->assertRedirect();

        $this->assertSame(0, $personnage->objets()->count());
    }

    public function test_une_ligne_d_inventaire_d_un_autre_personnage_est_introuvable(): void
    {
        $utilisateur = Utilisateur::factory()->create();
        $personnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $autrePersonnage = PersonnageFactory::new()->create(['utilisateur_id' => $utilisateur->id]);
        $objet = ObjetFactory::new()->create();
        $ligne = PersonnageObjetFactory::new()->create([
            'personnage_id' => $autrePersonnage->id,
            'objet_id' => $objet->id,
        ]);

        $this->actingAs($utilisateur)
            ->delete(route('personnages.inventaire.retirer', [$personnage, $ligne]))
            ->assertNotFound();
    }
}
