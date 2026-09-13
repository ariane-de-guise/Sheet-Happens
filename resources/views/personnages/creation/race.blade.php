@extends('layouts.app')

@section('title', 'Créer un personnage - Race')

@section('content')
    <div class="card">
        <div class="text-center mb-3">
            <span class="text-gold">Créer un personnage</span>
        </div>

        <div class="progress-bar">
            <div class="progress-line"></div>
            <div class="progress-fill" style="width: 0%;"></div>
            @for($i = 1; $i <= 6; $i++)
                <div class="progress-step {{ $i === 1 ? 'active' : '' }}">
                    {{ $i }}
                </div>
            @endfor
        </div>

        <h2 class="title-section text-center">Choisissez votre race</h2>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error" id="raceErrors">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @else
            <div class="alert alert-error" id="raceErrors" style="display: none;">
                Vous devez choisir une race avant de continuer.
            </div>
        @endif

        @php
            $selectedRaceModel = $selectedRace ? $races->find($selectedRace) : null;
            $selectedSousRaceModel = $selectedRaceModel && $selectedSousRace
                ? $selectedRaceModel->sousRaces->firstWhere('id', $selectedSousRace)
                : null;
        @endphp

        <form action="{{ route('creation.race.save') }}" method="POST" id="raceForm">
            @csrf
            <div class="grid-cards mb-4" id="racesGrid">
                @foreach($races as $race)
                    <div class="card-choice {{ $selectedRace == $race->id ? 'selected' : '' }}"
                         onclick="selectRace({{ $race->id }}, this)">
                        <input type="radio" name="race_id" value="{{ $race->id }}"
                               {{ $selectedRace == $race->id ? 'checked' : '' }}
                               required
                               style="display: none;">
                        <div class="card-title">{{ $race->nom }}</div>
                        @if($race->taille)
                            <div class="card-desc">{{ $race->taille }}</div>
                        @endif
                        @if($race->vitesse)
                            <div class="card-desc">Vitesse: {{ $race->vitesse }} ft</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="help-box mb-4" id="raceDescription" style="{{ $selectedRaceModel && $selectedRaceModel->description ? '' : 'display:none;' }}">
                <div class="title" id="raceDescriptionTitle">{{ $selectedRaceModel->nom ?? '' }}</div>
                <div class="content" id="raceDescriptionContent">{{ $selectedRaceModel->description ?? '' }}</div>
            </div>

            <div id="sousRaceContainer">
                @if($selectedRaceModel && $selectedRaceModel->sousRaces->count() > 0)
                    <h3 class="title-section mb-2">Sous-race</h3>
                    <div class="alert alert-error" id="sousRaceErrors" style="display: none;">
                        Vous devez choisir une sous-race avant de continuer.
                    </div>
                    <div class="grid-cards mb-4" id="sousRacesGrid">
                        @foreach($selectedRaceModel->sousRaces as $sousRace)
                            <div class="card-choice {{ $selectedSousRace == $sousRace->id ? 'selected' : '' }}"
                                 onclick="selectSousRace({{ $sousRace->id }}, this)">
                                <input type="radio" name="sous_race_id" value="{{ $sousRace->id }}"
                                       {{ $selectedSousRace == $sousRace->id ? 'checked' : '' }}
                                       required
                                       style="display: none;">
                                <div class="card-title">{{ $sousRace->nom }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="help-box mb-4" id="sousRaceDescription" style="{{ $selectedSousRaceModel && $selectedSousRaceModel->description ? '' : 'display:none;' }}">
                        <div class="title" id="sousRaceDescriptionTitle">{{ $selectedSousRaceModel->nom ?? '' }}</div>
                        <div class="content" id="sousRaceDescriptionContent">{{ $selectedSousRaceModel->description ?? '' }}</div>
                    </div>
                @endif
            </div>

            <div class="btn-group-row">
                <a href="{{ route('welcome') }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Données injectées par le serveur pour cette page ; toute la logique
            // vit dans un fichier séparé (public/js/creation/race.js).
            window.RaceCreationData = {
                races: @json($races->keyBy('id')),
                initialSousRaces: @json($selectedRaceModel->sousRaces ?? []),
            };
        </script>
        <script src="{{ asset('js/creation/race.js') }}"></script>
    @endpush
@endsection
