@extends('layouts.app')

@section('title', 'Créer un personnage - Méthode')

@section('content')
<div class="card" style="min-height: 70vh; display: flex; flex-direction: column; justify-content: center;">
    <div class="text-center mb-3">
        <span class="text-gold">Créer un personnage</span>
    </div>

    <div class="progress-bar">
        <div class="progress-line"></div>
        <div class="progress-fill" style="width: 33.3%;"></div>
        <div class="progress-step completed">1</div>
        <div class="progress-step completed">2</div>
        <div class="progress-step active">3</div>
        <div class="progress-step">4</div>
        <div class="progress-step">5</div>
        <div class="progress-step">6</div>
    </div>

    <h2 class="title-main text-center mb-4">Veuillez sélectionnez comment vous voulez choisir vos caractéristiques</h2>

    <form action="{{ route('creation.methode.save') }}" method="POST">
        @csrf
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
            <button type="submit" name="methode" value="points" class="btn btn-option">
                Inscrivez vos points à répartir
            </button>
            <button type="submit" name="methode" value="dice" class="btn btn-option">
                Lancée de dé
            </button>
            <button type="submit" name="methode" value="standard" class="btn btn-option">
                Points par défaut
            </button>
        </div>

        <div class="btn-group-row">
            <a href="{{ route('creation.classe') }}" class="btn btn-secondary">Retour</a>
            <div></div>
        </div>
    </form>
</div>
@endsection
