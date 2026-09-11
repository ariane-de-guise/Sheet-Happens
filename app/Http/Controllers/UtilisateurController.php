<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConnexionRequest;
use App\Http\Requests\UtilisateurRequest;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UtilisateurController extends Controller
{
    public function create(): View
    {
        return view('inscription');
    }

    public function store(UtilisateurRequest $request): RedirectResponse
    {
        $utilisateur = Utilisateur::create($request->validated());

        Auth::login($utilisateur);
        $request->session()->regenerate();

        return redirect()->route('personnages.index');
    }

    public function formulaireConnexion(): View
    {
        return view('connexion');
    }

    public function connexion(ConnexionRequest $request): RedirectResponse
    {
        $identifiants = [
            'courriel' => $request->validated('courriel'),
            'password' => $request->validated('mot_de_passe'),
        ];

        if (! Auth::attempt($identifiants)) {
            return back()
                ->withErrors([
                    'connexion' => "L'adresse courriel ou le mot de passe est incorrect.",
                ])
                ->onlyInput('courriel');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('personnages.index'));
    }

    public function profil(Request $request): View
    {
        return view('profil', [
            'utilisateur' => $request->user(),
        ]);
    }

    public function deconnexion(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}

