<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonnageController;
use App\Http\Controllers\CreationPersonnageController;
use App\Http\Controllers\PersonnageSheetController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::post('/api/personnages', [PersonnageController::class, 'store']);

Route::get('/personnages', [PersonnageSheetController::class, 'index'])->name('personnages.index');

Route::prefix('personnages/creer')->group(function () {
    Route::get('/', [CreationPersonnageController::class, 'stepRace'])->name('creation.race');
    Route::post('/race', [CreationPersonnageController::class, 'saveRace'])->name('creation.race.save');

    Route::get('/classe', [CreationPersonnageController::class, 'stepClasse'])->name('creation.classe');
    Route::post('/classe', [CreationPersonnageController::class, 'saveClasse'])->name('creation.classe.save');

    Route::get('/methode', [CreationPersonnageController::class, 'stepMethode'])->name('creation.methode');
    Route::post('/methode', [CreationPersonnageController::class, 'saveMethode'])->name('creation.methode.save');

    Route::get('/caracteristiques', [CreationPersonnageController::class, 'stepCaracteristiques'])->name('creation.caracteristiques');
    Route::post('/caracteristiques', [CreationPersonnageController::class, 'saveCaracteristiques'])->name('creation.caracteristiques.save');
    Route::post('/caracteristiques/roller', [CreationPersonnageController::class, 'rollDice'])->name('creation.roll');

    Route::get('/competences', [CreationPersonnageController::class, 'stepCompetences'])->name('creation.competences');
    Route::post('/competences', [CreationPersonnageController::class, 'saveCompetences'])->name('creation.competences.save');

    Route::get('/contexte', [CreationPersonnageController::class, 'stepContexte'])->name('creation.contexte');
    Route::post('/contexte', [CreationPersonnageController::class, 'saveContexte'])->name('creation.contexte.save');

    Route::get('/confirmation', [CreationPersonnageController::class, 'stepConfirmation'])->name('creation.confirmation');
    Route::post('/', [CreationPersonnageController::class, 'finalize'])->name('creation.finalize');
});

Route::get('/personnages/{personnage}', [PersonnageSheetController::class, 'show'])->name('personnages.show');

Route::get('/api/sous-classes/{classeId}', [CreationPersonnageController::class, 'getSousClasses'])->name('api.sous-classes');
Route::get('/api/sous-races/{raceId}', [CreationPersonnageController::class, 'getSousRaces'])->name('api.sous-races');
