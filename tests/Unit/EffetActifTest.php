<?php

namespace Tests\Unit;

use App\Models\EffetActif;
use Database\Factories\ObjetFactory;
use Database\Factories\PersonnageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EffetActifTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_effet_actif_appartient_a_un_personnage_et_a_un_objet(): void
    {
        $personnage = PersonnageFactory::new()->create();
        $objet = ObjetFactory::new()->create();
        $effet = EffetActif::create([
            'personnage_id' => $personnage->id,
            'objet_id' => $objet->id,
            'nom' => $objet->nom,
            'bonus' => ['force' => 4],
            'expire_a' => now()->addMinute(),
        ]);

        $this->assertTrue($effet->personnage->is($personnage));
        $this->assertTrue($effet->objet->is($objet));
    }

    public function test_un_effet_sans_objet_associe_est_permis(): void
    {
        $personnage = PersonnageFactory::new()->create();
        $effet = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Effet sans objet',
            'bonus' => ['force' => 1],
            'expire_a' => now()->addMinute(),
        ]);

        $this->assertNull($effet->objet);
    }

    public function test_est_expire_selon_l_heure_d_expiration_pour_un_effet_en_minutes(): void
    {
        $personnage = PersonnageFactory::new()->create();

        $expire = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Passé',
            'bonus' => ['force' => 1],
            'unite' => 'minutes',
            'expire_a' => now()->subMinute(),
        ]);
        $actif = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Actif',
            'bonus' => ['force' => 1],
            'unite' => 'minutes',
            'expire_a' => now()->addMinute(),
        ]);

        $this->assertTrue($expire->estExpire());
        $this->assertFalse($actif->estExpire());
    }

    public function test_est_expire_selon_les_tours_restants_pour_un_effet_en_tours(): void
    {
        $personnage = PersonnageFactory::new()->create();

        $epuise = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Épuisé',
            'bonus' => ['force' => 1],
            'unite' => 'tours',
            'tours_restants' => 0,
        ]);
        $actif = EffetActif::create([
            'personnage_id' => $personnage->id,
            'nom' => 'Actif',
            'bonus' => ['force' => 1],
            'unite' => 'tours',
            'tours_restants' => 3,
        ]);

        $this->assertTrue($epuise->estExpire());
        $this->assertFalse($actif->estExpire());
    }
}
