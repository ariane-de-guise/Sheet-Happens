<?php

namespace App\Http\Controllers;

use App\Http\Requests\UtilisateurRequest;
use App\Http\Resources\UtilisateurResource;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UtilisateurController extends Controller
{

    public function store(UtilisateurRequest $request): UtilisateurResource
    {
        $utilisateur = Utilisateur::create($request->validated());
        return new UtilisateurResource($utilisateur);
    }

    /**
     * Affiche le formulaire de création (Create).
     */
    public function create(): View
    {
        return view('inscription');
    }
}
