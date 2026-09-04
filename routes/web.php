<?php
use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inscription', [UtilisateurController::class, 'create'])->name('utilisateurs.create');

Route::post('/inscription', [UtilisateurController::class, 'store'])->name('utilisateurs.store');
