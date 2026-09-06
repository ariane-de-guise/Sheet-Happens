@extends('layouts.app')

@section('title', 'Sheet Happens')

@section('content')
<div class="card text-center" style="min-height: 80vh; display: flex; flex-direction: column; justify-content: center;">
    <h1 class="title-main">Sheet Happens</h1>
    <p class="subtitle">créez et gérez vos personnages de D&amp;D</p>

    <div style="display: flex; flex-direction: column; gap: 1rem; max-width: 300px; margin: 0 auto;">
        <a href="{{ route('creation.race') }}" class="btn btn-primary">Créez un personnage</a>
        <a href="{{ route('personnages.index') }}" class="btn btn-secondary">Mes personnages</a>
    </div>
</div>
@endsection
