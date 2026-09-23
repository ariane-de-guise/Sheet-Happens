<?php

namespace Tests\Unit;

use Database\Factories\ObjetFactory;
use Database\Factories\UtilisateurFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObjetTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_objet_custom_appartient_a_l_utilisateur_qui_l_a_cree(): void
    {
        $utilisateur = UtilisateurFactory::new()->create();
        $objet = ObjetFactory::new()->create(['utilisateur_id' => $utilisateur->id]);

        $this->assertTrue($objet->utilisateur->is($utilisateur));
    }

    public function test_un_objet_de_base_n_a_pas_d_utilisateur(): void
    {
        $objet = ObjetFactory::new()->create(['utilisateur_id' => null]);

        $this->assertNull($objet->utilisateur);
    }
}
