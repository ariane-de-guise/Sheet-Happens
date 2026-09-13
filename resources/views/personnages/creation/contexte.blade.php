@extends('layouts.app')

@section('title', 'Créer un personnage - Contexte')

@section('content')
    <div class="card">
        <div class="text-center mb-3">
            <span class="text-gold">Créer un personnage</span>
        </div>

        <div class="progress-bar">
            <div class="progress-line"></div>
            <div class="progress-fill" style="width: 83.3%;"></div>
            <div class="progress-step completed">1</div>
            <div class="progress-step completed">2</div>
            <div class="progress-step completed">3</div>
            <div class="progress-step completed">4</div>
            <div class="progress-step completed">5</div>
            <div class="progress-step active">6</div>
        </div>

        <h2 class="title-section text-center">Contexte</h2>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

    <form action="{{ route('creation.contexte.save') }}" method="POST" id="contexteForm" onsubmit="return validateContextForm()">
            @csrf

        <div class="form-group mb-3">
            <label class="form-label">Nom du personnage *</label>
            <input type="text" name="nom" value="{{ old('nom', $data['nom'] ?? '') }}"
                   class="form-input" required>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Description / Backstory (optionnel)</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Maîtrise *</span>
                <span class="hint">Détermine votre équipement de départ et votre alignement</span>
            </div>
            <select name="maitrise" id="maitriseSelect" class="form-select mt-2" required onchange="updateMaitrisePreview()">
                <option value="">-- Choisir --</option>
                @foreach($maitrises as $m)
                    <option value="{{ $m }}" {{ (old('maitrise', $selectedMaitrise) == $m) ? 'selected' : '' }}>
                        {{ $m }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Historique *</span>
            </div>
            <select name="historique_id" id="historique_select" class="form-select mt-2" onchange="onHistoriqueChange(this.value)" required>
                <option value="">-- Choisir un historique --</option>
                @foreach($historiques as $h)
                    <option value="{{ $h->id }}" {{ (old('historique_id', $selectedHistorique) == $h->id) ? 'selected' : '' }}>
                        {{ $h->nom }}
                    </option>
                @endforeach
            </select>
            <div id="historique_description" class="hint mt-2" style="font-style: italic;">
                {{ $selectedHistoriqueModel ? $selectedHistoriqueModel->description : '' }}
            </div>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Idéal *</span>
            </div>
            <select name="ideal_id" id="ideal_select" class="form-select mt-2" required>
                <option value="">-- Choisir un idéal --</option>
                @foreach($historiques as $h)
                    @foreach($h->ideaux as $item)
                        <option value="{{ $item->id }}" data-historique="{{ $h->id }}" {{ (old('ideal_id', $selectedIdeal) == $item->id) ? 'selected' : '' }}>
                            {{ $item->valeur }}{{ $item->alignement ? ' (' . $item->alignement . ')' : '' }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Défaut *</span>
            </div>
            <select name="defaut_id" id="defaut_select" class="form-select mt-2" required>
                <option value="">-- Choisir un défaut --</option>
                @foreach($historiques as $h)
                    @foreach($h->defauts as $item)
                        <option value="{{ $item->id }}" data-historique="{{ $h->id }}" {{ (old('defaut_id', $selectedDefaut) == $item->id) ? 'selected' : '' }}>
                            {{ $item->valeur }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Attachement *</span>
            </div>
            <select name="attachement_id" id="attachement_select" class="form-select mt-2" required>
                <option value="">-- Choisir un attachement --</option>
                @foreach($historiques as $h)
                    @foreach($h->attachements as $item)
                        <option value="{{ $item->id }}" data-historique="{{ $h->id }}" {{ (old('attachement_id', $selectedAttachement) == $item->id) ? 'selected' : '' }}>
                            {{ $item->valeur }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Trait de personnalité *</span>
            </div>
            <select name="personnalite_id" id="personnalite_select" class="form-select mt-2" required>
                <option value="">-- Choisir une personnalité --</option>
                @foreach($historiques as $h)
                    @foreach($h->personnalites as $item)
                        <option value="{{ $item->id }}" data-historique="{{ $h->id }}" {{ (old('personnalite_id', $selectedPersonnalite) == $item->id) ? 'selected' : '' }}>
                            {{ $item->valeur }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Alignement</span>
            </div>
            <ul class="items mt-2" id="alignementPreview">
                <li>Choisissez une maîtrise pour déterminer l'alignement.</li>
            </ul>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Outil</span>
            </div>
            <select name="outil" class="form-select mt-2">
                @foreach($outils as $o)
                    <option value="{{ $o }}" {{ (old('outil', $selectedOutil ?? 'Aucun') == $o) ? 'selected' : '' }}>
                        {{ $o }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Langue</span>
                <span class="hint">Déterminée par votre race</span>
            </div>
            <ul class="items mt-2">
                @foreach($languesRace as $lang)
                    <li>{{ $lang }}</li>
                @endforeach
            </ul>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Équipement</span>
                <span class="hint">Fourni par votre maîtrise (avec sa maîtrise d'utilisation)</span>
            </div>
            <ul class="items mt-2" id="equipementPreview">
                <li>Choisissez une maîtrise pour voir votre équipement.</li>
            </ul>
        </div>

            <div class="btn-group-row">
                <a href="{{ route('creation.competences') }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary">Suivant</button>
            </div>
        </form>
    </div>

@push('scripts')
    <script>
        window.ContexteData = {
            maitriseEquipement: @json($maitriseEquipement),
            maitriseAlignement: @json($maitriseAlignement),
            historiques: @json($historiques),
            maxLangues: 2,
        };
    </script>
    <script src="{{ asset('js/creation/contexte.js') }}"></script>
@endpush
@endsection
