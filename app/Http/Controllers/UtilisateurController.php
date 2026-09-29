<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConnexionRequest;
use App\Http\Requests\UtilisateurRequest;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UtilisateurController extends Controller
{
    /** Échecs de connexion tolérés par minute pour un même courriel et une même IP. */
    public const TENTATIVES_CONNEXION_MAX = 5;

    public function create(): View
    {
        return view('inscription');
    }

    public function store(UtilisateurRequest $requete): RedirectResponse
    {
        $utilisateur = Utilisateur::create($requete->validated());

        Auth::login($utilisateur);
        $requete->session()->regenerate();

        return redirect()->route('personnages.index');
    }

    public function formulaireConnexion(): View
    {
        return view('connexion');
    }

    public function connexion(ConnexionRequest $requete): RedirectResponse
    {
        $identifiants = [
            'courriel' => $requete->validated('courriel'),
            'password' => $requete->validated('mot_de_passe'),
        ];

        // Freine les essais de mots de passe en série : seuls les échecs
        // sont comptés, et une connexion réussie remet le compteur à zéro.
        $cleLimite = Str::transliterate(Str::lower($identifiants['courriel'])).'|'.$requete->ip();

        if (RateLimiter::tooManyAttempts($cleLimite, self::TENTATIVES_CONNEXION_MAX)) {
            return back()
                ->withErrors([
                    'connexion' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($cleLimite)]),
                ])
                ->onlyInput('courriel');
        }

        if (! Auth::attempt($identifiants)) {
            RateLimiter::hit($cleLimite, 60);

            return back()
                ->withErrors([
                    'connexion' => "L'adresse courriel ou le mot de passe est incorrect.",
                ])
                ->onlyInput('courriel');
        }

        RateLimiter::clear($cleLimite);
        $requete->session()->regenerate();

        return redirect()->intended(route('personnages.index'));
    }

    public function profil(Request $requete): View
    {
        return view('profil', [
            'utilisateur' => $requete->user(),
        ]);
    }

    public function deconnexion(Request $requete): RedirectResponse
    {
        Auth::logout();

        $requete->session()->invalidate();
        $requete->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}
