<?php

namespace App\Http\Middleware;

use App\Models\Personnage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Empêche un utilisateur ayant déjà atteint son quota de personnages
 * (Personnage::MAX_PAR_UTILISATEUR) d'entrer dans l'assistant de création,
 * à n'importe quelle étape, et le renvoie vers sa liste de personnages
 * avec un message explicatif.
 *
 * Ce contrôle est une commodité pour l'utilisateur (éviter de lui faire
 * remplir 6 étapes pour rien) : la vérification faisant foi reste celle du
 * PersonnageCreationService, qui protège aussi le point d'entrée API et
 * gère les accès concurrents.
 */
class EnsureLimitePersonnagesNonAtteinte
{
    public function handle(Request $request, Closure $next): Response
    {
        $nombrePersonnages = Personnage::where('utilisateur_id', $request->user()->id)->count();

        if ($nombrePersonnages >= Personnage::MAX_PAR_UTILISATEUR) {
            return redirect()->route('personnages.index')
                ->with('error', 'Vous avez atteint la limite de '.Personnage::MAX_PAR_UTILISATEUR.' personnages. Supprimez-en un pour pouvoir en créer un nouveau.');
        }

        return $next($request);
    }
}
