<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Services\MonteeNiveauService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonteeNiveauController extends Controller
{
    public function show(Personnage $personnage, MonteeNiveauService $service): View|RedirectResponse
    {
        if ($personnage->niveau >= 5) {
            return redirect()->route('personnages.show', $personnage)->with('succes', 'Ce personnage a atteint le niveau maximum pris en charge (5).');
        }
        if (! $service->peutMonterDeNiveau($personnage)) {
            return redirect()->route('personnages.show', $personnage)
                ->withErrors(['experience' => $service->messageExperienceInsuffisante($personnage, $personnage->niveau + 1)]);
        }

        $personnage->load(['classe', 'sousClasse', 'abilites']);

        return view('personnages.montee-niveau', [
            'personnage' => $personnage,
            'apercu' => $service->apercu($personnage),
            'jetDeVie' => session()->get('montee_niveau.jet_de_vie.'.$personnage->id),
        ]);
    }

    public function lancerDeVie(Personnage $personnage, MonteeNiveauService $service): JsonResponse
    {
        $apercu = $service->apercu($personnage);
        $faces = $apercu['de_vie_faces'];
        abort_if($faces < 1, 422, 'Dé de vie invalide.');

        $jet = random_int(1, $faces);
        session()->put('montee_niveau.jet_de_vie.'.$personnage->id, $jet);

        return response()->json(['lancer' => $jet, 'faces' => $faces]);
    }

    public function store(Request $requete, Personnage $personnage, MonteeNiveauService $service): RedirectResponse
    {
        $choix = $requete->all();
        if (($choix['mode_pv'] ?? 'fixe') === 'de') {
            $choix['jet_de_vie'] = session()->get('montee_niveau.jet_de_vie.'.$personnage->id);
        }
        $service->monter($personnage, $choix);
        session()->forget('montee_niveau.jet_de_vie.'.$personnage->id);

        // L'expérience restante peut suffire pour un niveau de plus (gros
        // gain d'XP d'un coup) : on enchaîne directement sur la montée
        // suivante plutôt que de laisser le joueur sur une fiche bloquée.
        if ($service->peutMonterDeNiveau($personnage)) {
            return redirect()->route('personnages.montee-niveau', $personnage)
                ->with('succes', "{$personnage->nom} est maintenant niveau {$personnage->niveau} et peut encore monter de niveau !");
        }

        return redirect()->route('personnages.show', $personnage)
            ->with('succes', "{$personnage->nom} est maintenant niveau {$personnage->niveau} !");
    }
}
