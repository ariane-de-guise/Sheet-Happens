@extends('layouts.app')

@section('title', 'Mes personnages')

@section('content')
<div class="card">
    <h1 class="title-main text-center mb-4">Mes personnages</h1>

    @if($personnages->isEmpty())
        <p class="text-muted text-center mb-4">Vous n'avez pas encore de personnage.</p>
    @else
        <div class="grid-cards mb-4">
            @foreach($personnages as $p)
                <a href="{{ route('personnages.show', $p) }}" class="card-choice" style="text-decoration: none;">
                    <div class="card-title">{{ $p->nom }}</div>
                    <div class="card-desc">{{ $p->race->nom }} {{ $p->classe->nom }}</div>
                </a>
            @endforeach
        </div>
    @endif

    <div class="text-center">
        <a href="{{ route('creation.race') }}" class="btn btn-primary mb-2">Créez un personnage</a>
    </div>
</div>
@endsection
