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

        // Redirige l'utilisateur vers la page d'accueil
        return redirect('/');
    }

    /**
     * Affiche le formulaire de création (Create).
     */
    public function create(): View
    {
        return view('inscription');
    }

    /**
     * Affiche le formulaire de connexion.
     */
    public function formulaireConnexion(): View
    {
        return view('connexion');
    }

    /**
     * Authentifie un utilisateur à partir de son courriel et de son mot de passe.
     *
     * Les identifiants sont validés par ConnexionRequest avant l'exécution de la
     * méthode. La clé "password" est utilisée pour Auth::attempt() car Laravel
     * s'attend toujours à cette clé pour vérifier le mot de passe, peu importe
     * le nom réel de la colonne (mot_de_passe).
     *
     * @param  ConnexionRequest  $request  La requête HTTP contenant les identifiants validés.
     * @return RedirectResponse Une redirection vers la page d'accueil, ou vers le formulaire avec une erreur.
     */
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

        return redirect('/');
    }

    /**
     * Déconnecte l'utilisateur actuellement authentifié.
     *
     * Invalide la session et régénère le jeton CSRF afin d'éviter toute
     * réutilisation de la session par un tiers après la déconnexion.
     *
     * @param  Request  $request  La requête HTTP courante.
     * @return RedirectResponse Une redirection vers la page d'accueil.
     */
    public function deconnexion(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
