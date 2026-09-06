<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use Illuminate\View\View;

class PersonnageSheetController extends Controller
{
    public function index(): View
    {
        $personnages = Personnage::where('utilisateur_id', 1)
            ->with(['race', 'classe', 'abilites'])
            ->get();

        return view('personnages.index', [
            'personnages' => $personnages,
        ]);
    }

    public function show(Personnage $personnage): View
    {
        $personnage->load(['race', 'sousRace', 'classe', 'sousClasse', 'abilites', 'listeCompetence']);

        return view('personnages.show', [
            'personnage' => $personnage,
        ]);
    }
}
