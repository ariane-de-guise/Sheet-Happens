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
                    <div class="card-choice" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="{{ route('personnages.show', $p) }}" style="text-decoration: none; color: inherit;">
                            <div class="card-title">{{ $p->nom }}</div>
                            <div class="card-desc">{{ $p->race->nom }} {{ $p->classe->nom }}</div>
                        </a>
                        <form id="form-supprimer-{{ $p->id }}" action="{{ route('personnages.destroy', $p) }}" method="POST" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-small" style="width: 100%;"
                                    onclick="ouvrirModalSuppression('form-supprimer-{{ $p->id }}', '{{ addslashes($p->nom) }}')">
                                Supprimer
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="text-center">
            <a href="{{ route('creation.race') }}" class="btn btn-primary mb-2">Créez un personnage</a>
        </div>
    </div>

    @include('partials.modal-suppression-personnage')
@endsection
