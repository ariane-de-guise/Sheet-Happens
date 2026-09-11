<?php

use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inscription', [UtilisateurController::class, 'create'])->name('utilisateurs.create');

Route::post('/inscription', [UtilisateurController::class, 'store'])->name('utilisateurs.store');

Route::get('/connexion', [UtilisateurController::class, 'formulaireConnexion'])->name('connexion.create');

Route::post('/connexion', [UtilisateurController::class, 'connexion'])->name('connexion.store');

Route::post('/deconnexion', [UtilisateurController::class, 'deconnexion'])->name('deconnexion');
