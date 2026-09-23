<?php

namespace Tests\Unit\Models;

use App\Models\Utilisateur;
use Database\Factories\PersonnageFactory;
use Database\Factories\UtilisateurFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilisateurTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_possede_plusieurs_personnages(): void
    {
        $utilisateur = UtilisateurFactory::new()->create();
        $personnage = PersonnageFactory::new()->create([
            'utilisateur_id' => $utilisateur->id,
        ]);

        $this->assertTrue($utilisateur->personnages->contains($personnage));
    }

    public function test_le_nom_de_l_attribut_du_mot_de_passe_est_correct(): void
    {
        $utilisateur = new Utilisateur;

        $this->assertSame('mot_de_passe', $utilisateur->getAuthPasswordName());
    }
}
