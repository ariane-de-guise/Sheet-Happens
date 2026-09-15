<?php

namespace Tests\Unit;

use App\Models\Attachement;
use App\Models\Historique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachementTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_attachement_appartient_a_un_historique(): void
    {
        $historique = Historique::create(['nom' => 'Acolyte']);
        $attachement = Attachement::create([
            'historique_id' => $historique->id,
            'valeur' => 'Je protégerais de ma vie les autres acolytes de mon temple.',
        ]);

        $this->assertTrue($attachement->historique->is($historique));
    }
}
