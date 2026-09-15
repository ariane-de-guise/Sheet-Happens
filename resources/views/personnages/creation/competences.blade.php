@extends('layouts.app')

@section('title', 'Créer un personnage - Compétences')

@section('content')
    <div class="card">
        <div class="text-center mb-3">
            <span class="text-gold">Créer un personnage</span>
        </div>

        <div class="progress-bar">
            <div class="progress-line">
                <div class="progress-fill" style="width: 80%;"></div>
            </div>
            <div class="progress-step completed">1</div>
            <div class="progress-step completed">2</div>
            <div class="progress-step completed">3</div>
            <div class="progress-step completed">4</div>
            <div class="progress-step active">5</div>
            <div class="progress-step">6</div>
        </div>

        <h2 class="title-section text-center">Compétences</h2>
        <p class="text-muted text-center mb-3">
            Choisissez {{ $nombreCompetences }} compétences{{ $classe ? ' parmi celles offertes par votre classe (' . $classe->nom . ')' : '' }}
        </p>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error" id="competencesErrors">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @else
            <div class="alert alert-error" id="competencesErrors" style="display: none;">
                Vous devez choisir {{ $nombreCompetences }} compétences avant de continuer.
            </div>
        @endif

        <form action="{{ route('creation.competences.save') }}" method="POST" id="competencesForm">
            @csrf
            <div class="grid-cards mb-4">
                @foreach($competencesDisponibles as $comp)
                    @php
                        $isSelected = in_array($comp, $competencesSelectionnees);
                    @endphp
                    <div class="card-choice {{ $isSelected ? 'selected' : '' }}"
                         onclick="toggleCompetence('{{ $comp }}', this)">
                        <input type="checkbox" name="competences[]" value="{{ $comp }}"
                               {{ $isSelected ? 'checked' : '' }}
                               style="display: none;">
                        <div class="card-title">{{ $comp }}</div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mb-3">
                <span class="text-gold" id="countDisplay">{{ count($competencesSelectionnees) }} / {{ $nombreCompetences }} sélectionnées</span>
            </div>

            <div class="btn-group-row">
                <a href="{{ route('creation.caracteristiques') }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Données injectées par le serveur pour cette page ; toute la logique
            // vit dans un fichier séparé (public/js/creation/competences.js).
            window.CompetencesCreationData = {
                max: {{ $nombreCompetences }},
                selected: @json($competencesSelectionnees),
            };
        </script>
        <script src="{{ asset('js/creation/competences.js') }}"></script>
    @endpush
@endsection
