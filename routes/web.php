<?php

use App\Http\Controllers\CreationPersonnageController;
use App\Http\Controllers\FichePersonnageController;
use App\Http\Controllers\InventaireController;
use App\Http\Controllers\MonteeNiveauController;
use App\Http\Controllers\PersonnageSortController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('personnages.index');
    }

    return view('welcome');
})->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [UtilisateurController::class, 'create'])->name('utilisateurs.create');
    Route::post('/inscription', [UtilisateurController::class, 'store'])
        ->middleware('throttle:inscription')
        ->name('utilisateurs.store');

    Route::get('/connexion', [UtilisateurController::class, 'formulaireConnexion'])->name('connexion.create');
    Route::post('/connexion', [UtilisateurController::class, 'connexion'])->name('connexion.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profil', [UtilisateurController::class, 'profil'])->name('utilisateurs.profil');
    Route::post('/deconnexion', [UtilisateurController::class, 'deconnexion'])->name('deconnexion');

    Route::get('/personnages', [FichePersonnageController::class, 'index'])->name('personnages.index');

    Route::get('/personnages/choix', function () {
        return view('personnages.choixPersonnage');
    })->name('personnages.choix');

    Route::prefix('personnages/creer')->middleware('limite.personnages')->group(function () {
        Route::get('/', [CreationPersonnageController::class, 'etapeRace'])->name('creation.race');
        Route::post('/race', [CreationPersonnageController::class, 'enregistrerRace'])->name('creation.race.enregistrer');

        Route::get('/classe', [CreationPersonnageController::class, 'etapeClasse'])->name('creation.classe');
        Route::post('/classe', [CreationPersonnageController::class, 'enregistrerClasse'])->name('creation.classe.enregistrer');

        Route::get('/methode', [CreationPersonnageController::class, 'etapeMethode'])->name('creation.methode');
        Route::post('/methode', [CreationPersonnageController::class, 'enregistrerMethode'])->name('creation.methode.enregistrer');

        Route::get('/caracteristiques', [CreationPersonnageController::class, 'etapeCaracteristiques'])->name('creation.caracteristiques');
        Route::post('/caracteristiques', [CreationPersonnageController::class, 'enregistrerCaracteristiques'])->name('creation.caracteristiques.enregistrer');
        Route::post('/caracteristiques/lancer', [CreationPersonnageController::class, 'lancerDes'])->name('creation.lancer-des');

        Route::get('/competences', [CreationPersonnageController::class, 'etapeCompetences'])->name('creation.competences');
        Route::post('/competences', [CreationPersonnageController::class, 'enregistrerCompetences'])->name('creation.competences.enregistrer');

        Route::get('/contexte', [CreationPersonnageController::class, 'etapeContexte'])->name('creation.contexte');
        Route::post('/contexte', [CreationPersonnageController::class, 'enregistrerContexte'])->name('creation.contexte.enregistrer');

        Route::get('/confirmation', [CreationPersonnageController::class, 'etapeConfirmation'])->name('creation.confirmation');
        Route::post('/', [CreationPersonnageController::class, 'finaliser'])->name('creation.finaliser');
    });

    Route::post('/personnages/{personnage}/experience', [FichePersonnageController::class, 'ajouterExperience'])->name('personnages.experience.ajouter');

    Route::get('/personnages/{personnage}/montee-niveau', [MonteeNiveauController::class, 'show'])->name('personnages.montee-niveau');
    Route::post('/personnages/{personnage}/montee-niveau/lancer-de-vie', [MonteeNiveauController::class, 'lancerDeVie'])->name('personnages.montee-niveau.lancer-de-vie');
    Route::post('/personnages/{personnage}/montee-niveau', [MonteeNiveauController::class, 'store'])->name('personnages.montee-niveau.store');

    Route::get('/personnages/{personnage}', [FichePersonnageController::class, 'show'])->name('personnages.show');

    // Sorts connus
    Route::get('/personnages/{personnage}/sorts', [PersonnageSortController::class, 'index'])->name('personnages.sorts.index');
    Route::patch('/personnages/{personnage}/sorts/{personnageSort}', [PersonnageSortController::class, 'modifierPreparation'])->name('personnages.sorts.preparer');
    Route::post('/personnages/{personnage}/sorts', [PersonnageSortController::class, 'ajouterPreparation'])->name('personnages.sorts.ajouter');

    Route::prefix('personnages/{personnage}/inventaire')->name('personnages.inventaire.')->group(function () {
        Route::get('/', [InventaireController::class, 'index'])->name('index');
        Route::get('/ajouter', [InventaireController::class, 'pageAjout'])->name('ajouter-page');
        Route::get('/ajouter/personnalise', [InventaireController::class, 'pageAjoutPersonnalise'])->name('ajouter-personnalise-page');
        Route::post('/', [InventaireController::class, 'ajouter'])->name('ajouter');
        Route::post('/personnalise', [InventaireController::class, 'ajouterPersonnalise'])->name('ajouter-personnalise');
        Route::patch('/{personnageObjet}/quantite', [InventaireController::class, 'ajusterQuantite'])->name('quantite');
        Route::patch('/{personnageObjet}/equiper', [InventaireController::class, 'equiper'])->name('equiper');
        Route::patch('/{personnageObjet}/desequiper', [InventaireController::class, 'desequiper'])->name('desequiper');
        Route::post('/{personnageObjet}/utiliser', [InventaireController::class, 'utiliser'])->name('utiliser');
        Route::delete('/{personnageObjet}', [InventaireController::class, 'retirer'])->name('retirer');
        Route::patch('/effets/{effetActif}/tours', [InventaireController::class, 'ajusterTours'])->name('effets.tours');
    });
    Route::get('/personnages/{personnage}/edit', [FichePersonnageController::class, 'edit'])->name('personnages.edit');
    Route::get('/personnages/{personnage}/traits', [FichePersonnageController::class, 'afficherTraits'])->name('personnages.traits');
    Route::patch('/personnages/{personnage}', [FichePersonnageController::class, 'update'])->name('personnages.update');
    Route::patch('/personnages/{personnage}/photo', [FichePersonnageController::class, 'modifierPhoto'])->name('personnages.photo');
    Route::delete('/personnages/{personnage}', [FichePersonnageController::class, 'destroy'])->name('personnages.destroy');

    Route::get('/api/sous-classes/{classeId}/{niveauMax?}', [CreationPersonnageController::class, 'listerSousClasses'])->name('api.sous-classes');
    Route::get('/api/sous-races/{raceId}', [CreationPersonnageController::class, 'listerSousRaces'])->name('api.sous-races');
    Route::get('/api/historiques/{historiqueId}', [CreationPersonnageController::class, 'detailsHistorique'])->name('api.historique-details');
});
