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

    <form action="{{ route('creation.contexte.save') }}" method="POST" onsubmit="return validateContextForm()">
        @csrf

        <div class="form-group mb-3">
            <label class="form-label">Nom du personnage *</label>
            <input type="text" name="nom" value="{{ old('nom', $data['nom'] ?? '') }}"
                   class="form-input" required>
        </div>

        <div class="form-group mb-3">
            <label class="form-label">Alignement *</label>
            <select name="alignement" class="form-select" required>
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
            <label class="form-label">Description / Backstory (optionnel)</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea>
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
                <span class="title">Outil *</span>
            </div>
            <select name="outil" class="form-select mt-2" required>
                @foreach($outils as $o)
                    <option value="{{ $o }}" {{ (old('outil', $selectedOutil ?? 'Aucun') == $o) ? 'selected' : '' }}>
                        {{ $o }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="context-section">
            <div class="header">
                <span class="title">Langue *</span>
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
                <span class="title">Équipement *</span>
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
    const historiquesData = @json($historiques);

    function filterOptions(selectId, historiqueId) {
        const select = document.getElementById(selectId);
        if (!select) return;

        let hasSelectedVisible = false;

        Array.from(select.options).forEach(opt => {
            if (!opt.value) {
                opt.hidden = false;
                opt.disabled = false;
                return;
            }

            const match = opt.dataset.historique == historiqueId;
            opt.hidden = !match;
            opt.disabled = !match;

            if (match && opt.selected) {
                hasSelectedVisible = true;
            }
        });

        if (!hasSelectedVisible) {
            select.value = '';
        }
    }

    function onHistoriqueChange(historiqueId) {
        const descDiv = document.getElementById('historique_description');
        const data = (historiquesData || []).find(h => h.id == historiqueId);

        if (descDiv) {
            descDiv.textContent = (data && data.description) ? data.description : '';
        }

        filterOptions('ideal_select', historiqueId);
        filterOptions('defaut_select', historiqueId);
        filterOptions('attachement_select', historiqueId);
        filterOptions('personnalite_select', historiqueId);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const histSelect = document.getElementById('historique_select');
        if (histSelect && histSelect.value) {
            onHistoriqueChange(histSelect.value);
        } else {
            filterOptions('ideal_select', '');
            filterOptions('defaut_select', '');
            filterOptions('attachement_select', '');
            filterOptions('personnalite_select', '');
        }
    });

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

    function validateContextForm() {
        const langues = getSelectedLangues();
        if (langues.length !== maxLangues) {
            alert('Veuillez choisir exactement ' + maxLangues + ' langues.');
            return false;
        }
        const equipements = Array.from(document.querySelectorAll('input[name="equipements[]"]:checked'));
        if (equipements.length < 1) {
            alert('Veuillez choisir au moins 1 équipement.');
            return false;
        }
        return true;
    }
</script>
@endpush
@endsection
