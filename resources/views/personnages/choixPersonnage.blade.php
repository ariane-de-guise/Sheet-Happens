@extends('layouts.app')

@section('titre', 'Sheet Happens')

@section('contenu')
<div class="carte texte-centre" style="min-height: 80vh; display: flex; flex-direction: column; justify-content: center;">
    <h1 class="titre-principal">Sheet Happens</h1>
    <p class="sous-titre">créez et gérez vos personnages de D&amp;D</p>

    <div style="display: flex; flex-direction: column; gap: 1rem; max-width: 300px; margin: 0 auto;">
        <a href="{{ route('creation.race') }}" class="bouton bouton-principal">Créez un personnage</a>
        <a href="{{ route('personnages.index') }}" class="bouton bouton-secondaire">Mes personnages</a>
    </div>
</div>
@endsection