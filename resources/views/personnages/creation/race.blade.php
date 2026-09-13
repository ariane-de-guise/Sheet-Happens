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

    <form action="{{ route('creation.race.save') }}" method="POST">
        @csrf
        <div class="grid-cards mb-4" id="racesGrid">
            @foreach($races as $race)
                <div class="card-choice {{ $selectedRace == $race->id ? 'selected' : '' }}"
                     onclick="selectRace({{ $race->id }}, this)">
                    <input type="radio" name="race_id" value="{{ $race->id }}"
                           {{ $selectedRace == $race->id ? 'checked' : '' }}
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

        <div id="sousRaceContainer">
            @if($selectedRace && $races->find($selectedRace)->sousRaces->count() > 0)
                <h3 class="title-section mb-2">Sous-race (optionnel)</h3>
                <div class="grid-cards mb-4" id="sousRacesGrid">
                    <div class="card-choice {{ $selectedSousRace === null ? 'selected' : '' }}"
                         onclick="selectSousRace(null, this)">
                        <input type="radio" name="sous_race_id" value=""
                               {{ $selectedSousRace === null ? 'checked' : '' }}
                               style="display: none;">
                        <div class="card-title">Aucune sous-race</div>
                    </div>
                    @foreach($races->find($selectedRace)->sousRaces as $sousRace)
                        <div class="card-choice {{ $selectedSousRace == $sousRace->id ? 'selected' : '' }}"
                             onclick="selectSousRace({{ $sousRace->id }}, this)">
                            <input type="radio" name="sous_race_id" value="{{ $sousRace->id }}"
                                   {{ $selectedSousRace == $sousRace->id ? 'checked' : '' }}
                                   style="display: none;">
                            <div class="card-title">{{ $sousRace->nom }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="btn-group-row">
            <a href="{{ route('welcome') }}" class="btn btn-secondary">Retour</a>
            <button type="submit" class="btn btn-primary">Suivant</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function selectRace(raceId, element) {
        document.querySelectorAll('#racesGrid .card-choice').forEach(el => {
            el.classList.remove('selected');
        });
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;

        fetchSousRaces(raceId);
    }

    function selectSousRace(id, element) {
        document.querySelectorAll('#sousRacesGrid .card-choice').forEach(el => {
            el.classList.remove('selected');
        });
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;
    }

    function fetchSousRaces(raceId) {
        fetch('/api/sous-races/' + raceId)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('sousRaceContainer');
                if (data.length === 0) {
                    container.innerHTML = '';
                    return;
                }

                let html = '<h3 class="title-section mb-2">Sous-race (optionnel)</h3>';
                html += '<div class="grid-cards mb-4" id="sousRacesGrid">';
                html += '<div class="card-choice selected" onclick="selectSousRace(null, this)">';
                html += '<input type="radio" name="sous_race_id" value="" checked style="display: none;">';
                html += '<div class="card-title">Aucune sous-race</div></div>';

                data.forEach(sr => {
                    html += '<div class="card-choice" onclick="selectSousRace(' + sr.id + ', this)">';
                    html += '<input type="radio" name="sous_race_id" value="' + sr.id + '" style="display: none;">';
                    html += '<div class="card-title">' + sr.nom + '</div>';
                    html += '</div>';
                });

                html += '</div>';
                container.innerHTML = html;
            });
    }
</script>
@endpush
@endsection
