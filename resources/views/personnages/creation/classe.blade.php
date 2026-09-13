@extends('layouts.app')

@section('title', 'Créer un personnage - Classe')

@section('content')
    <div class="card">
        <div class="text-center mb-3">
            <span class="text-gold">Créer un personnage</span>
        </div>

        <div class="progress-bar">
            <div class="progress-line"></div>
            <div class="progress-fill" style="width: 16.6%;"></div>
            <div class="progress-step completed">1</div>
            <div class="progress-step active">2</div>
            <div class="progress-step">3</div>
            <div class="progress-step">4</div>
            <div class="progress-step">5</div>
            <div class="progress-step">6</div>
        </div>

        <h2 class="title-section text-center">Choisissez votre classe</h2>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error" id="classeErrors">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @else
            <div class="alert alert-error" id="classeErrors" style="display: none;">
                Vous devez choisir une classe avant de continuer.
            </div>
        @endif

        @php
            $selectedClasseModel = $selectedClasse ? $classes->find($selectedClasse) : null;
            // Une sous-classe ne se choisit qu'au niveau indiqué par la classe ;
            // à la création, le personnage est toujours de niveau 1.
            $sousClassesDisponibles = $selectedClasseModel && $selectedClasseModel->niveau_sous_classe <= 1
                ? $selectedClasseModel->sousClasses
                : collect();
            $selectedSousClasseModel = $sousClassesDisponibles->firstWhere('id', $selectedSousClasse);
        @endphp

        <form action="{{ route('creation.classe.save') }}" method="POST" id="classeForm">
            @csrf
            <div class="grid-cards mb-4" id="classesGrid">
                @foreach($classes as $classe)
                    <div class="card-choice {{ $selectedClasse == $classe->id ? 'selected' : '' }}"
                         onclick="selectClasse({{ $classe->id }}, this)">
                        <input type="radio" name="classe_id" value="{{ $classe->id }}"
                               {{ $selectedClasse == $classe->id ? 'checked' : '' }}
                               required
                               style="display: none;">
                        <div class="card-title">{{ $classe->nom }}</div>
                        @if($classe->de_de_vie)
                            <div class="card-desc">DV: {{ $classe->de_de_vie }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="help-box mb-4" id="classeDescription" style="{{ $selectedClasseModel && $selectedClasseModel->description ? '' : 'display:none;' }}">
                <div class="title" id="classeDescriptionTitle">{{ $selectedClasseModel->nom ?? '' }}</div>
                <div class="content" id="classeDescriptionContent">{{ $selectedClasseModel->description ?? '' }}</div>
            </div>

            <div id="sousClasseContainer">
                @if($sousClassesDisponibles->count() > 0)
                    <h3 class="title-section mb-2">Sous-classe</h3>
                    <div class="alert alert-error" id="sousClasseErrors" style="display: none;">
                        Vous devez choisir une sous-classe avant de continuer.
                    </div>
                    <div class="grid-cards mb-4" id="sousClassesGrid">
                        @foreach($sousClassesDisponibles as $sousClasse)
                            <div class="card-choice {{ $selectedSousClasse == $sousClasse->id ? 'selected' : '' }}"
                                 onclick="selectSousClasse({{ $sousClasse->id }}, this)">
                                <input type="radio" name="sous_classe_id" value="{{ $sousClasse->id }}"
                                       {{ $selectedSousClasse == $sousClasse->id ? 'checked' : '' }}
                                       required
                                       style="display: none;">
                                <div class="card-title">{{ $sousClasse->nom }}</div>
                                <div class="card-desc">Niveau {{ $selectedClasseModel->niveau_sous_classe }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="help-box mb-4" id="sousClasseDescription" style="{{ $selectedSousClasseModel && $selectedSousClasseModel->description ? '' : 'display:none;' }}">
                        <div class="title" id="sousClasseDescriptionTitle">{{ $selectedSousClasseModel->nom ?? '' }}</div>
                        <div class="content" id="sousClasseDescriptionContent">{{ $selectedSousClasseModel->description ?? '' }}</div>
                    </div>
                @endif
            </div>

            <div class="btn-group-row">
                <a href="{{ route('creation.race') }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Données injectées par le serveur pour cette page ; toute la logique
            // vit dans un fichier séparé (public/js/creation/classe.js).
            window.ClasseCreationData = {
                classes: @json($classes->keyBy('id')),
                initialSousClasses: @json($sousClassesDisponibles),
            };
        </script>
        <script src="{{ asset('js/creation/classe.js') }}"></script>
    @endpush
@endsection
