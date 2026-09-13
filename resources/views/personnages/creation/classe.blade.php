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

            <div id="sousClasseContainer">
                @if($selectedClasse && $classes->find($selectedClasse)->sousClasses->count() > 0)
                    <h3 class="title-section mb-2">Sous-classe (optionnel)</h3>
                    <div class="grid-cards mb-4" id="sousClassesGrid">
                        <div class="card-choice {{ $selectedSousClasse === null ? 'selected' : '' }}"
                             onclick="selectSousClasse(null, this)">
                            <input type="radio" name="sous_classe_id" value=""
                                   {{ $selectedSousClasse === null ? 'checked' : '' }}
                                   style="display: none;">
                            <div class="card-title">Aucune sous-classe</div>
                        </div>
                        @foreach($classes->find($selectedClasse)->sousClasses as $sousClasse)
                            <div class="card-choice {{ $selectedSousClasse == $sousClasse->id ? 'selected' : '' }}"
                                 onclick="selectSousClasse({{ $sousClasse->id }}, this)">
                                <input type="radio" name="sous_classe_id" value="{{ $sousClasse->id }}"
                                       {{ $selectedSousClasse == $sousClasse->id ? 'checked' : '' }}
                                       style="display: none;">
                                <div class="card-title">{{ $sousClasse->nom }}</div>
                                <div class="card-desc">Niveau {{ $classes->find($selectedClasse)->niveau_sous_classe }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="btn-group-row">
                <a href="{{ route('creation.race') }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary" id="btnSuivant" {{ $selectedClasse ? '' : 'disabled' }}>Suivant</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function selectClasse(id, element) {
                document.querySelectorAll('#classesGrid .card-choice').forEach(el => {
                    el.classList.remove('selected');
                });
                element.classList.add('selected');
                element.querySelector('input[type="radio"]').checked = true;

                document.getElementById('btnSuivant').disabled = false;
                document.getElementById('classeErrors').style.display = 'none';

                fetchSousClasses(id);
            }

            document.getElementById('classeForm').addEventListener('submit', function (event) {
                const hasSelection = document.querySelector('#classesGrid input[type="radio"]:checked');
                if (!hasSelection) {
                    event.preventDefault();
                    document.getElementById('classeErrors').style.display = 'block';
                }
            });

            function selectSousClasse(id, element) {
                document.querySelectorAll('#sousClassesGrid .card-choice').forEach(el => {
                    el.classList.remove('selected');
                });
                element.classList.add('selected');
                element.querySelector('input[type="radio"]').checked = true;
            }

            function fetchSousClasses(classeId) {
                fetch('/api/sous-classes/' + classeId)
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('sousClasseContainer');
                        if (data.length === 0) {
                            container.innerHTML = '';
                            return;
                        }

                        let html = '<h3 class="title-section mb-2">Sous-classe (optionnel)</h3>';
                        html += '<div class="grid-cards mb-4" id="sousClassesGrid">';
                        html += '<div class="card-choice selected" onclick="selectSousClasse(null, this)">';
                        html += '<input type="radio" name="sous_classe_id" value="" checked style="display: none;">';
                        html += '<div class="card-title">Aucune sous-classe</div></div>';

                        data.forEach(sc => {
                            html += '<div class="card-choice" onclick="selectSousClasse(' + sc.id + ', this)">';
                            html += '<input type="radio" name="sous_classe_id" value="' + sc.id + '" style="display: none;">';
                            html += '<div class="card-title">' + sc.nom + '</div>';
                            html += '<div class="card-desc">Niveau ' + sc.niveau_sous_classe + '</div>';
                            html += '</div>';
                        });

                        html += '</div>';
                        container.innerHTML = html;
                    });
            }
        </script>
    @endpush
@endsection
