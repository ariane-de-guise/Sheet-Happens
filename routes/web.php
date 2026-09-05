<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonnageController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/personnages', [PersonnageController::class, 'store']);
