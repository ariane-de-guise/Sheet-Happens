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

    <form action="{{ route('creation.contexte.save') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label class="form-label">Nom du personnage *</label>
            <input type="text" name="nom" value="{{ old('nom', $data['nom'] ?? '') }}"
                   class="form-input" required>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Alignement</label>
            <select name="alignement" class="form-select">
                <option value="">-- Choisir --</option>
                @foreach([
                    'Loyal bon', 'Neutre bon', 'Chaotique bon',
                    'Loyal neutre', 'Neutre', 'Chaotique neutre',
                    'Loyal méchant', 'Neutre méchant', 'Chaotique méchant',
                ] as $align)
                    <option value="{{ $align }}" {{ (old('alignement', $data['alignement'] ?? '') == $align) ? 'selected' : '' }}>
                        {{ $align }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Description / Backstory</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Maîtrise</span>
            </div>
            <select name="maitrise" class="form-select mt-2">
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
                <span class="hint">Veuillez choisir 2 langues</span>
            </div>
            <div class="grid-cards mt-2" style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));">
                @foreach($langues as $lang)
                    @php
                        $isSelected = in_array($lang, old('langues', $selectedLangues ?? []));
                    @endphp
                    <div class="card-choice {{ $isSelected ? 'selected' : '' }}"
                         onclick="toggleLangue('{{ $lang }}', this)">
                        <input type="checkbox" name="langues[]" value="{{ $lang }}"
                               {{ $isSelected ? 'checked' : '' }}
                               style="display: none;">
                        <div class="card-title" style="font-size: 0.85rem;">{{ $lang }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Équipement</span>
            </div>
            <div class="grid-cards mt-2" style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));">
                @foreach($equipements as $eq)
                    @php
                        $isSelected = in_array($eq, old('equipements', $selectedEquipements ?? []));
                    @endphp
                    <div class="card-choice {{ $isSelected ? 'selected' : '' }}"
                         onclick="toggleEquipement('{{ $eq }}', this)">
                        <input type="checkbox" name="equipements[]" value="{{ $eq }}"
                               {{ $isSelected ? 'checked' : '' }}
                               style="display: none;">
                        <div class="card-title" style="font-size: 0.85rem;">{{ $eq }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="btn-group-row">
            <a href="{{ route('creation.competences') }}" class="btn btn-secondary">Retour</a>
            <button type="submit" class="btn btn-primary">Suivant</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const maxLangues = 2;

    function getSelectedLangues() {
        return Array.from(document.querySelectorAll('input[name="langues[]"]:checked')).map(cb => cb.value);
    }

    function toggleLangue(lang, element) {
        const checkbox = element.querySelector('input[type="checkbox"]');

        if (checkbox.checked) {
            checkbox.checked = false;
            element.classList.remove('selected');
        } else {
            const selected = getSelectedLangues();
            if (selected.length >= maxLangues) return;
            checkbox.checked = true;
            element.classList.add('selected');
        }
    }

    function toggleEquipement(eq, element) {
        const checkbox = element.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        element.classList.toggle('selected');
    }
</script>
@endpush
@endsection
