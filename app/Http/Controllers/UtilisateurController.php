<?php

namespace App\Http\Controllers;

use App\Http\Requests\UtilisateurRequest;
use App\Http\Resources\UtilisateurResource;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UtilisateurController extends Controller
{

    /**
     * Enregistre un nouvel utilisateur dans la base de données.
     *
     * Les données entrantes sont automatiquement validées et autorisées
     * par la classe UtilisateurRequest avant l'exécution de la méthode.
     *
     * @param  UtilisateurRequest  $request  La requête HTTP contenant les données validées.
     * @return RedirectResponse Une redirection vers la page d'accueil.
     */
    public function store(UtilisateurRequest $request): RedirectResponse
    {
        // Insère le nouvel enregistrement en base de données avec les champs validés
        $utilisateur = Utilisateur::create($request->validated());
        Auth::login($utilisateur);
        $request->session()->regenerate();
        // La liste des personnages est la première page après connexion.
        return redirect()->route('personnages.index');
    }

    /**
     * Affiche le formulaire de création (Create).
     */
    public function create(): View
    {
        return view('inscription');
    }

    /**
     * Déconnecte l'utilisateur et retourne à l'inscription.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('utilisateurs.create');
    }

    /**
     * Affiche le profil de l'utilisateur actuellement connecté.
     */
    public function show(Request $request): View
    {
        return view('profil', [
            'utilisateur' => $request->user(),
        ]);
    }
}
