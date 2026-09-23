<?php

namespace Tests\Unit;

use App\Models\EffetActif;
use App\Models\Personnage;
use App\Services\InventaireService;
use Database\Factories\ObjetFactory;
use Database\Factories\PersonnageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventaireServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): InventaireService
    {
        return new InventaireService;
    }

    private function personnage(): Personnage
    {
        return PersonnageFactory::new()->create();
    }

    public function test_ajouter_cree_une_ligne_puis_incremente_si_l_objet_est_deja_possede(): void
    {
        $personnage = $this->personnage();
        $objet = ObjetFactory::new()->create();
        $service = $this->service();

        $ligne = $service->ajouter($personnage, $objet, 2);
        $this->assertSame(2, $ligne->quantite);

        $ligne = $service->ajouter($personnage, $objet, 3);
        $this->assertSame(5, $ligne->quantite);
        $this->assertSame(1, $personnage->objets()->count());
    }

    public function test_ajuster_quantite_supprime_la_ligne_a_zero(): void
    {
        $personnage = $this->personnage();
        $objet = ObjetFactory::new()->create();
        $service = $this->service();

        $ligne = $service->ajouter($personnage, $objet, 2);
        $service->ajusterQuantite($ligne, -1);
        $this->assertSame(1, $personnage->objets()->first()->pivot->quantite);

        $service->ajusterQuantite($ligne, -1);
        $this->assertSame(0, $personnage->objets()->count());
    }

    public function test_ajuster_quantite_sans_permettre_la_suppression_plafonne_a_1(): void
    {
        $personnage = $this->personnage();
        $objet = ObjetFactory::new()->create();
        $service = $this->service();

        $ligne = $service->ajouter($personnage, $objet, 1);
        $service->ajusterQuantite($ligne, -1, permetSuppression: false);

        $this->assertSame(1, $ligne->fresh()->quantite);
        $this->assertSame(1, $personnage->objets()->count());
    }

    public function test_equiper_une_armure_refuse_si_une_autre_armure_est_deja_equipee(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $cuir = ObjetFactory::new()->armure('legere', 11)->create(['nom' => 'Armure de cuir']);
        $plates = ObjetFactory::new()->armure('lourde', 18)->create(['nom' => 'Armure de plates']);

        $ligneCuir = $service->ajouter($personnage, $cuir);
        $lignePlates = $service->ajouter($personnage, $plates);

        $service->equiper($personnage, $ligneCuir);

        $this->expectException(ValidationException::class);
        $service->equiper($personnage, $lignePlates);
    }

    public function test_desequiper_marque_la_ligne_comme_non_equipee(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $epee = ObjetFactory::new()->arme()->create();
        $ligne = $service->ajouter($personnage, $epee);
        $service->equiper($personnage, $ligne);

        $service->desequiper($ligne);

        $this->assertSame(0, $ligne->fresh()->quantite_equipee);
    }

    public function test_equiper_deux_armes_a_une_main_est_permis(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $dague1 = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague 1']);
        $dague2 = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague 2']);

        $ligne1 = $service->ajouter($personnage, $dague1);
        $ligne2 = $service->ajouter($personnage, $dague2);

        $service->equiper($personnage, $ligne1);
        $service->equiper($personnage, $ligne2);

        $this->assertCount(2, $service->objetsEquipes($personnage));
    }

    public function test_equiper_deux_exemplaires_de_la_meme_arme_a_une_main_est_permis(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $dague = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague']);
        $ligne = $service->ajouter($personnage, $dague, 2);

        $service->equiper($personnage, $ligne);
        $service->equiper($personnage, $ligne);

        $this->assertSame(2, $ligne->fresh()->quantite_equipee);
    }

    public function test_equiper_un_troisieme_exemplaire_de_la_meme_arme_refuse_faute_de_mains_libres(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $dague = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague']);
        $ligne = $service->ajouter($personnage, $dague, 3);

        $service->equiper($personnage, $ligne);
        $service->equiper($personnage, $ligne);

        $this->expectException(ValidationException::class);
        $service->equiper($personnage, $ligne);
    }

    public function test_equiper_refuse_un_exemplaire_qu_on_ne_possede_pas(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $dague = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague']);
        $ligne = $service->ajouter($personnage, $dague, 1);

        $service->equiper($personnage, $ligne);

        $this->expectException(ValidationException::class);
        $service->equiper($personnage, $ligne);
    }

    public function test_equiper_deux_fois_la_meme_armure_est_refuse(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $cuir = ObjetFactory::new()->armure('legere', 11)->create(['nom' => 'Armure de cuir']);
        $ligne = $service->ajouter($personnage, $cuir, 2);

        $service->equiper($personnage, $ligne);

        $this->expectException(ValidationException::class);
        $service->equiper($personnage, $ligne);
    }

    public function test_ajuster_quantite_replafonne_le_nombre_equipe_si_la_quantite_diminue(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $dague = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague']);
        $ligne = $service->ajouter($personnage, $dague, 2);
        $service->equiper($personnage, $ligne);
        $service->equiper($personnage, $ligne);

        $service->ajusterQuantite($ligne, -1);

        $ligne->refresh();
        $this->assertSame(1, $ligne->quantite);
        $this->assertSame(1, $ligne->quantite_equipee);
    }

    public function test_armes_equipees_repete_une_arme_dual_wieldee(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $dague = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague', 'degats' => '1d4']);
        $ligne = $service->ajouter($personnage, $dague, 2);
        $service->equiper($personnage, $ligne);
        $service->equiper($personnage, $ligne);

        $armes = $service->armesEquipees($personnage->fresh(['objets']));

        $this->assertCount(2, $armes);
    }

    public function test_equiper_une_troisieme_arme_a_une_main_refuse_faute_de_mains_libres(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $dague1 = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague 1']);
        $dague2 = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague 2']);
        $dague3 = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague 3']);

        $service->equiper($personnage, $service->ajouter($personnage, $dague1));
        $service->equiper($personnage, $service->ajouter($personnage, $dague2));

        $this->expectException(ValidationException::class);
        $service->equiper($personnage, $service->ajouter($personnage, $dague3));
    }

    public function test_equiper_un_arc_occupe_les_deux_mains(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $arc = ObjetFactory::new()->arme('distance', 2)->create(['nom' => 'Arc court']);
        $dague = ObjetFactory::new()->arme('une_main', 1)->create(['nom' => 'Dague']);

        $service->equiper($personnage, $service->ajouter($personnage, $arc));

        $this->expectException(ValidationException::class);
        $service->equiper($personnage, $service->ajouter($personnage, $dague));
    }

    public function test_equiper_un_bouclier_avec_une_arme_a_deux_mains_refuse(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $armeDeuxMains = ObjetFactory::new()->arme('deux_mains', 2)->create(['nom' => 'Épée à deux mains']);
        $bouclier = ObjetFactory::new()->bouclier()->create();

        $service->equiper($personnage, $service->ajouter($personnage, $armeDeuxMains));

        $this->expectException(ValidationException::class);
        $service->equiper($personnage, $service->ajouter($personnage, $bouclier));
    }

    public function test_equiper_refuse_un_objet_qui_n_est_ni_arme_ni_armure_ni_bouclier(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();
        $potion = ObjetFactory::new()->potion()->create();

        $this->expectException(ValidationException::class);
        $service->equiper($personnage, $service->ajouter($personnage, $potion));
    }

    public function test_utiliser_refuse_un_objet_non_consommable(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();
        $epee = ObjetFactory::new()->arme()->create();

        $this->expectException(ValidationException::class);
        $service->utiliser($personnage, $service->ajouter($personnage, $epee));
    }

    public function test_utiliser_une_potion_de_soin_ajoute_les_pv_et_consomme_une_unite(): void
    {
        $personnage = $this->personnage();
        $personnage->update(['point_de_vie' => 5]);
        $service = $this->service();

        $potion = ObjetFactory::new()->potion(['pv' => 7])->create();
        $ligne = $service->ajouter($personnage, $potion, 2);

        $service->utiliser($personnage, $ligne);

        $this->assertSame(12, $personnage->fresh()->point_de_vie);
        $this->assertSame(1, $personnage->objets()->first()->pivot->quantite);
    }

    public function test_utiliser_une_potion_sans_effet_pv_se_contente_de_la_consommer(): void
    {
        $personnage = $this->personnage();
        $personnage->update(['point_de_vie' => 5]);
        $service = $this->service();

        $potion = ObjetFactory::new()->create(['type' => 'potion', 'est_consommable' => true, 'effet_utilisation' => null]);
        $ligne = $service->ajouter($personnage, $potion);

        $service->utiliser($personnage, $ligne);

        $this->assertSame(5, $personnage->fresh()->point_de_vie);
        $this->assertSame(0, $personnage->objets()->count());
    }

    public function test_bonus_actifs_agrege_objets_equipes_et_toujours_actifs(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $bouclier = ObjetFactory::new()->bouclier()->create();
        $service->equiper($personnage, $service->ajouter($personnage, $bouclier));

        $anneau = ObjetFactory::new()->create(['bonus' => ['charisme' => 1], 'bonus_toujours_actif' => true]);
        $service->ajouter($personnage, $anneau);

        $objetNonEquipe = ObjetFactory::new()->create(['bonus' => ['force' => 4], 'bonus_toujours_actif' => false]);
        $service->ajouter($personnage, $objetNonEquipe);

        $bonus = $service->bonusActifs($personnage->fresh(['objets']));

        $this->assertSame(['ca' => 2, 'charisme' => 1], $bonus);
    }

    public function test_utiliser_une_potion_a_bonus_en_minutes_cree_un_effet_qui_expire_dans_le_futur(): void
    {
        Carbon::setTestNow('2026-01-01 12:00:00');

        $personnage = $this->personnage();
        $service = $this->service();

        $potion = ObjetFactory::new()->create([
            'type' => 'potion',
            'est_consommable' => true,
            'effet_utilisation' => ['bonus' => ['force' => 4], 'duree_valeur' => 1, 'duree_unite' => 'minutes', 'duree_secondes' => 60],
        ]);
        $service->utiliser($personnage, $service->ajouter($personnage, $potion));

        $effet = $personnage->effetsActifs()->first();
        $this->assertNotNull($effet);
        $this->assertSame('minutes', $effet->unite);
        $this->assertSame(['force' => 4], $effet->bonus);
        $this->assertNull($effet->tours_restants);
        $this->assertTrue($effet->expire_a->equalTo(Carbon::parse('2026-01-01 12:01:00')));

        Carbon::setTestNow();
    }

    public function test_utiliser_une_potion_a_bonus_en_tours_cree_un_effet_decompte_a_la_main(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $potion = ObjetFactory::new()->create([
            'type' => 'potion',
            'est_consommable' => true,
            'effet_utilisation' => ['bonus' => ['bonus_degats' => 2], 'duree_valeur' => 10, 'duree_unite' => 'tours', 'duree_secondes' => 60],
        ]);
        $service->utiliser($personnage, $service->ajouter($personnage, $potion));

        $effet = $personnage->effetsActifs()->first();
        $this->assertNotNull($effet);
        $this->assertSame('tours', $effet->unite);
        $this->assertSame(10, $effet->tours_restants);
        $this->assertNull($effet->expire_a);
    }

    public function test_ajuster_tours_decremente_et_retire_l_effet_a_zero(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $effet = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Élixir de rage',
            'bonus' => ['bonus_degats' => 2],
            'unite' => 'tours',
            'tours_restants' => 2,
        ]);

        $service->ajusterTours($effet, -1);
        $this->assertSame(1, $effet->fresh()->tours_restants);

        $service->ajusterTours($effet, -1);
        $this->assertModelMissing($effet);
    }

    public function test_bonus_actifs_ignore_un_effet_en_tours_a_zero(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        // Un effet en tours ne disparaît normalement que via ajusterTours()
        // (qui le supprime dès qu'il atteint 0) : ce test vérifie que
        // bonusActifs() l'ignorerait malgré tout s'il en restait un à 0,
        // par exemple créé directement sans passer par ce chemin.
        EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Élixir épuisé',
            'bonus' => ['force' => 4],
            'unite' => 'tours',
            'tours_restants' => 0,
        ]);

        $bonus = $service->bonusActifs($personnage->fresh(['objets', 'effetsActifs']));

        $this->assertSame([], $bonus);
    }

    public function test_purger_effets_expires_ignore_les_effets_en_tours(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $effetTours = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Élixir de rage',
            'bonus' => ['bonus_degats' => 2],
            'unite' => 'tours',
            'tours_restants' => 3,
        ]);

        $service->purgerEffetsExpires($personnage);

        $this->assertModelExists($effetTours);
    }

    public function test_bonus_actifs_ignore_un_effet_temporaire_expire(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Potion expirée',
            'bonus' => ['force' => 4],
            'expire_a' => now()->subMinute(),
        ]);
        EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Potion active',
            'bonus' => ['charisme' => 2],
            'expire_a' => now()->addMinute(),
        ]);

        $bonus = $service->bonusActifs($personnage->fresh(['objets', 'effetsActifs']));

        $this->assertSame(['charisme' => 2], $bonus);
    }

    public function test_purger_effets_expires_supprime_uniquement_les_effets_passes(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $expire = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Expiré',
            'bonus' => ['force' => 1],
            'expire_a' => now()->subMinute(),
        ]);
        $actif = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Actif',
            'bonus' => ['force' => 1],
            'expire_a' => now()->addMinute(),
        ]);

        $service->purgerEffetsExpires($personnage);

        $this->assertModelMissing($expire);
        $this->assertModelExists($actif);
    }

    public function test_classe_armure_avec_une_armure_intermediaire_plafonne_le_bonus_de_dexterite(): void
    {
        $personnage = $this->personnage();
        $service = $this->service();

        $cuirasse = ObjetFactory::new()->armure('intermediaire', 14)->create();
        $service->equiper($personnage, $service->ajouter($personnage, $cuirasse));

        // Cuirasse : CA 14 + mod Dex plafonné à +2, même si le mod réel est plus élevé (+4 ici).
        $ca = $service->classeArmure($personnage->fresh(['objets']), 4);

        $this->assertSame(16, $ca);
    }
}
