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
        $this->authorizeOwner($personnage);
        if ($personnage->niveau >= 5) {
            return redirect()->route('personnages.show', $personnage)->with('success', 'Ce personnage a atteint le niveau maximum pris en charge (5).');
        }

        $personnage->load(['classe', 'sousClasse', 'abilites']);

        return view('personnages.montee-niveau', [
            'personnage' => $personnage,
            'apercu' => $service->apercu($personnage),
            'jetDeVie' => session()->get('montee_niveau.jet_de_vie.'.$personnage->id),
        ]);
    }

    public function rollHitDie(Personnage $personnage, MonteeNiveauService $service): JsonResponse
    {
        $this->authorizeOwner($personnage);
        $apercu = $service->apercu($personnage);
        $faces = $apercu['de_vie_faces'];
        abort_if($faces < 1, 422, 'Dé de vie invalide.');

        $jet = random_int(1, $faces);
        session()->put('montee_niveau.jet_de_vie.'.$personnage->id, $jet);

        return response()->json(['roll' => $jet, 'faces' => $faces]);
    }

    public function store(Request $request, Personnage $personnage, MonteeNiveauService $service): RedirectResponse
    {
        $this->authorizeOwner($personnage);
        $choix = $request->all();
        if (($choix['mode_pv'] ?? 'fixe') === 'de') {
            $choix['jet_de_vie'] = session()->get('montee_niveau.jet_de_vie.'.$personnage->id);
        }
        $service->monter($personnage, $choix);
        session()->forget('montee_niveau.jet_de_vie.'.$personnage->id);

        return redirect()->route('personnages.show', $personnage)
            ->with('success', "{$personnage->nom} est maintenant niveau {$personnage->niveau} !");
    }

    private function authorizeOwner(Personnage $personnage): void
    {
        abort_unless(auth()->id() === $personnage->utilisateur_id, 403);
    }
}
