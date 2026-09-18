<?php

namespace Tests\Unit;

use App\Models\Abilite;
use App\Models\Classe;
use App\Models\ListeCompetence;
use App\Models\Personnage;
use App\Models\Race;
use App\Models\SousClasse;
use App\Models\Utilisateur;
use App\Services\MonteeNiveauService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MonteeNiveauServiceTest extends TestCase
{
    use RefreshDatabase;

    private MonteeNiveauService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MonteeNiveauService::class);
    }

    private function personnage(string $classeNom = 'Barbare', int $niveau = 1, int $con = 14): Personnage
    {
        $classe = Classe::create([
            'nom' => $classeNom, 'de_de_vie' => match ($classeNom) {
                'Barbare' => '12','Guerrier' => '10','Magicien' => '6',default => '8'
            },
            'niveau_sous_classe' => match ($classeNom) {
                'Clerc' => 1,'Magicien' => 2,default => 3
            },
            'competences_disponibles' => [], 'nombre_competences' => 2,
        ]);

        return Personnage::create([
            'utilisateur_id' => Utilisateur::factory()->create()->id,
            'race_id' => Race::create(['nom' => 'Humain'])->id,
            'classe_id' => $classe->id,
            'abilites_id' => Abilite::create(['force' => 16, 'dexterite' => 12, 'constitution' => $con, 'intelligence' => 10, 'sagesse' => 10, 'charisme' => 10])->id,
            'liste_competence_id' => ListeCompetence::create(['competences' => []])->id,
            'nom' => 'Test', 'niveau' => $niveau, 'point_de_vie' => 20,
        ]);
    }

    public function test_barbare_niveau_2_recoit_capacites_et_pv(): void
    {
        $p = $this->personnage();
        $this->service->monter($p, []);
        $p->refresh();
        $this->assertSame(2, $p->niveau);
        $this->assertSame(29, $p->point_de_vie); // 7 + CON +2
        $this->assertContains('Attaque téméraire', $p->capacites);
        $this->assertContains('Sens du danger', $p->capacites);
    }

    public function test_sous_classe_est_obligatoire_au_niveau_requis(): void
    {
        $p = $this->personnage('Guerrier', 2);
        $sc = SousClasse::create(['classe_id' => $p->classe_id, 'nom' => 'Champion']);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, []);
    }

    public function test_sous_classe_valide_est_sauvegardee(): void
    {
        $p = $this->personnage('Guerrier', 2);
        $sc = SousClasse::create(['classe_id' => $p->classe_id, 'nom' => 'Champion']);
        $this->service->monter($p, ['sous_classe_id' => $sc->id]);
        $this->assertSame($sc->id, $p->fresh()->sous_classe_id);
    }

    public function test_asi_plus_deux_est_appliquee_au_niveau_4(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->service->monter($p, ['type_amelioration' => 'asi', 'asi' => ['force' => 2]]);
        $this->assertSame(18, $p->abilites->fresh()->force);
    }

    public function test_asi_plus_un_plus_un_est_appliquee(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->service->monter($p, ['type_amelioration' => 'asi', 'asi' => ['force' => 1, 'constitution' => 1]]);
        $this->assertSame(17, $p->abilites->fresh()->force);
        $this->assertSame(15, $p->abilites->fresh()->constitution);
    }

    public function test_asi_ne_peut_pas_depasser_20(): void
    {
        $p = $this->personnage('Barbare', 3);
        $p->abilites->update(['force' => 19]);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, ['type_amelioration' => 'asi', 'asi' => ['force' => 2]]);
    }

    public function test_feat_est_sauvegarde_et_effet_de_stat_applique(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->service->monter($p, ['type_amelioration' => 'feat', 'feat' => 'Athlète', 'feat_stat' => 'force']);
        $p->refresh();
        $this->assertContains('Athlète', $p->feats);
        $this->assertSame(17, $p->abilites->fresh()->force);
    }

    public function test_robuste_ajoute_huit_pv_au_niveau_4(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->service->monter($p, ['type_amelioration' => 'feat', 'feat' => 'Robuste']);
        $this->assertSame(37, $p->fresh()->point_de_vie); // 20 + 8 Robuste + 9 montée
    }

    public function test_bonus_maitrise_devient_trois_au_niveau_5(): void
    {
        $p = $this->personnage('Guerrier', 4);
        $montee = $this->service->monter($p, []);
        $this->assertSame(3, $montee->changements['bonus_maitrise']);
    }

    public function test_niveau_ne_peut_pas_depasser_cinq(): void
    {
        $p = $this->personnage('Guerrier', 5);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, []);
    }

    public function test_choix_et_changements_sont_sauvegardes(): void
    {
        $p = $this->personnage('Barbare', 1);
        $m = $this->service->monter($p, []);
        $this->assertDatabaseHas('montees_niveau', ['personnage_id' => $p->id, 'niveau_arrivee' => 2]);
        $this->assertContains('Attaque téméraire', $m->changements['capacites']);
    }

    public function test_apercu_magicien_niveau_deux_demande_sous_classe_et_de_d6(): void
    {
        $p = $this->personnage('Magicien', 1);
        SousClasse::create(['classe_id' => $p->classe_id, 'nom' => 'École de l\'Évocation']);
        $a = $this->service->apercu($p);
        $this->assertTrue($a['choix_sous_classe']);
        $this->assertSame(4, $a['pv_base']);
        $this->assertSame(2, $a['bonus_maitrise']);
        $this->assertNotEmpty($a['sous_classes']);
        $this->assertSame([], $a['feats']);
    }

    public function test_apercu_niveau_quatre_propose_les_dons(): void
    {
        $p = $this->personnage('Barbare', 3);
        $a = $this->service->apercu($p);
        $this->assertTrue($a['choix_asi']);
        $this->assertArrayHasKey('Robuste', $a['feats']);
    }

    public function test_clerc_utilise_de_d8(): void
    {
        $p = $this->personnage('Clerc', 1);
        $p->sous_classe_id = null;
        $a = $this->service->apercu($p);
        $this->assertSame(5, $a['pv_base']);
    }

    public function test_de_inconnu_utilise_gain_minimal(): void
    {
        $p = $this->personnage('Barbare', 1);
        $p->classe->update(['de_de_vie' => 'd4']);
        $a = $this->service->apercu($p->fresh());
        $this->assertSame(1, $a['pv_base']);
    }

    public function test_classe_non_supportee_est_refusee(): void
    {
        $p = $this->personnage('Barde', 1);
        $this->expectException(ValidationException::class);
        $this->service->apercu($p);
    }

    public function test_asi_refuse_un_gain_negatif_ou_superieur_a_deux(): void
    {
        $p = $this->personnage('Barbare', 3);

        foreach ([-1, 3] as $gain) {
            try {
                $this->service->monter($p->fresh(), ['type_amelioration' => 'asi', 'asi' => ['force' => $gain, 'dexterite' => 2 - $gain]]);
                $this->fail('Une ASI invalide devait être refusée.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('asi', $e->errors());
            }
        }
    }

    public function test_asi_exige_exactement_deux_points(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, ['type_amelioration' => 'asi', 'asi' => ['force' => 1]]);
    }

    public function test_asi_refuse_plus_de_deux_caracteristiques(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, ['type_amelioration' => 'asi', 'asi' => ['force' => 1, 'dexterite' => 1, 'sagesse' => 1]]);
    }

    public function test_type_amelioration_est_obligatoire_au_niveau_quatre(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, []);
    }

    public function test_feat_invalide_est_refuse(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, ['type_amelioration' => 'feat', 'feat' => 'Inexistant']);
    }

    public function test_feat_deja_possede_est_refuse(): void
    {
        $p = $this->personnage('Barbare', 3);
        $p->update(['feats' => ['Chanceux']]);
        $this->expectException(ValidationException::class);
        $this->service->monter($p->fresh(), ['type_amelioration' => 'feat', 'feat' => 'Chanceux']);
    }

    public function test_feat_avec_choix_de_stat_refuse_un_choix_invalide(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, ['type_amelioration' => 'feat', 'feat' => 'Athlète', 'feat_stat' => 'sagesse']);
    }

    public function test_feat_avec_stat_fixe_augmente_la_stat(): void
    {
        $p = $this->personnage('Barbare', 3);
        $this->service->monter($p, ['type_amelioration' => 'feat', 'feat' => 'Acteur']);
        $this->assertSame(11, $p->abilites->fresh()->charisme);
    }

    public function test_feat_ne_peut_pas_augmenter_une_stat_deja_a_20(): void
    {
        $p = $this->personnage('Barbare', 3);
        $p->abilites->update(['charisme' => 20]);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, ['type_amelioration' => 'feat', 'feat' => 'Acteur']);
    }

    public function test_feat_sans_effet_representable_est_quand_meme_sauvegarde(): void
    {
        $p = $this->personnage('Barbare', 3);
        $m = $this->service->monter($p, ['type_amelioration' => 'feat', 'feat' => 'Chanceux']);
        $this->assertContains('Chanceux', $p->fresh()->feats);
        $this->assertSame([], $m->changements['effets_feat']);
    }

    public function test_augmentation_de_constitution_retroactive_met_a_jour_les_pv(): void
    {
        $p = $this->personnage('Barbare', 3, 13); // mod +1
        $this->service->monter($p, ['type_amelioration' => 'asi', 'asi' => ['constitution' => 1, 'force' => 1]]); // CON 14, mod +2
        $this->assertSame(32, $p->fresh()->point_de_vie); // 20 + (7+2) + 3 PV rétroactifs
    }

    public function test_gain_de_pv_est_au_minimum_un(): void
    {
        $p = $this->personnage('Magicien', 2, 1); // d6 fixe 4, mod CON -5 => minimum 1
        $sousClasse = SousClasse::create([
            'classe_id' => $p->classe_id,
            'nom' => "École de l'Évocation",
        ]);
        $p->sous_classe_id = $sousClasse->id;
        $p->save();

        $this->service->monter($p, []);
        $this->assertSame(21, $p->fresh()->point_de_vie);
    }

    public function test_apercu_expose_le_de_de_vie_et_la_valeur_fixe(): void
    {
        $p = $this->personnage('Barbare', 1);
        $apercu = $this->service->apercu($p);

        $this->assertSame(12, $apercu['de_vie_faces']);
        $this->assertSame(7, $apercu['pv_base']);
    }

    public function test_valeur_fixe_est_utilisee_et_sauvegardee(): void
    {
        $p = $this->personnage('Barbare', 1, 14);
        $montee = $this->service->monter($p, ['mode_pv' => 'fixe']);

        $this->assertSame(29, $p->fresh()->point_de_vie); // 20 + 7 + mod CON +2
        $this->assertSame('fixe', $montee->choix['mode_pv']);
        $this->assertSame('fixe', $montee->changements['mode_pv']);
        $this->assertSame(7, $montee->changements['pv_base_obtenu']);
        $this->assertNull($montee->changements['jet_de_vie']);
    }

    public function test_lancer_de_vie_est_dans_les_limites_et_sauvegarde(): void
    {
        $p = $this->personnage('Barbare', 1, 14);
        $montee = $this->service->monter($p, ['mode_pv' => 'de', 'jet_de_vie' => 9]);
        $jet = $montee->changements['jet_de_vie'];

        $this->assertSame(9, $jet);
        $this->assertSame('de', $montee->choix['mode_pv']);
        $this->assertSame('de', $montee->changements['mode_pv']);
        $this->assertSame($jet, $montee->changements['pv_base_obtenu']);
        $this->assertSame(20 + $jet + 2, $p->fresh()->point_de_vie);
    }

    public function test_lancer_exige_un_resultat_deja_obtenu(): void
    {
        $p = $this->personnage('Barbare', 1);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, ['mode_pv' => 'de']);
    }

    public function test_lancer_refuse_un_resultat_hors_du_de(): void
    {
        $p = $this->personnage('Barbare', 1);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, ['mode_pv' => 'de', 'jet_de_vie' => 13]);
    }

    public function test_mode_pv_invalide_est_refuse(): void
    {
        $p = $this->personnage('Barbare', 1);
        $this->expectException(ValidationException::class);
        $this->service->monter($p, ['mode_pv' => 'invalide']);
    }

    public function test_lancer_refuse_un_de_de_vie_invalide(): void
    {
        $p = $this->personnage('Barbare', 1);
        $p->classe->update(['de_de_vie' => 'invalide']);

        $this->expectException(ValidationException::class);
        $this->service->monter($p->fresh(), ['mode_pv' => 'de']);
    }
}
