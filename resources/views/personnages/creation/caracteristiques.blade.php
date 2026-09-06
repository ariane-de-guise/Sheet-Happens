@extends('layouts.app')

@section('title', 'Créer un personnage - Caractéristiques')

@section('content')
<div class="card">
    <div class="text-center mb-3">
        <span class="text-gold">Créer un personnage</span>
    </div>

    <div class="progress-bar">
        <div class="progress-line"></div>
        <div class="progress-fill" style="width: 50%;"></div>
        <div class="progress-step completed">1</div>
        <div class="progress-step completed">2</div>
        <div class="progress-step completed">3</div>
        <div class="progress-step active">4</div>
        <div class="progress-step">5</div>
        <div class="progress-step">6</div>
    </div>

    <h2 class="title-section text-center">Répartissez vos caractéristiques</h2>

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if($methode === 'points')
        @include('personnages.creation._caracteristiques_points', ['abilites' => $abilites])
    @elseif($methode === 'standard')
        @include('personnages.creation._caracteristiques_standard', ['abilites' => $abilites])
    @else
        @include('personnages.creation._caracteristiques_dice', ['abilites' => $abilites])
    @endif
</div>
@endsection
