@extends('layouts.app')

@section('title', 'Modifier ' . $personnage->nom)

@push('styles')
<style>
.sheet-container {
    max-width: 1100px;
}

.sheet-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.sheet-header-left {
    flex: 1;
    min-width: 250px;
}

.sheet-header-right {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    flex-wrap: wrap;
}

.stat-block {
    background: rgba(25, 10, 50, 0.9);
    border: 2px solid #c49a3c;
    border-radius: 12px;
    padding: 0.75rem;
    text-align: center;
    min-width: 80px;
}

.stat-block-label {
    font-size: 0.7rem;
    color: #b8a9c9;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.stat-block-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #e6b84d;
}

.stat-block input {
    width: 50px;
    text-align: center;
    background: rgba(60, 15, 90, 0.8);
    border: 1px solid #c49a3c;
    border-radius: 6px;
    color: #e6b84d;
    font-size: 1.3rem;
    font-weight: 700;
    padding: 0.2rem;
}

.sheet-section {
    background: rgba(25, 10, 50, 0.9);
    border: 1px solid #c49a3c;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}

.sheet-section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #e6b84d;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid rgba(196, 154, 60, 0.3);
}

.sheet-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.sheet-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}

.sheet-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.sheet-grid-6 {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 0.75rem;
}

.ability-box {
    background: rgba(15, 5, 30, 0.9);
    border: 2px solid #c49a3c;
    border-radius: 10px;
    padding: 0.75rem 0.5rem;
    text-align: center;
}

.ability-name {
    font-size: 0.75rem;
    color: #b8a9c9;
    text-transform: uppercase;
    margin-bottom: 0.4rem;
}

.ability-input {
    width: 50px;
    text-align: center;
    background: rgba(60, 15, 90, 0.8);
    border: 1px solid #c49a3c;
    border-radius: 6px;
    color: #e6b84d;
    font-size: 1.4rem;
    font-weight: 700;
    padding: 0.3rem;
    margin-bottom: 0.3rem;
}

.ability-mod {
    font-size: 0.9rem;
    color: #e6b84d;
    font-weight: 600;
}

.form-input-small {
    width: 100%;
    padding: 0.4rem 0.6rem;
    background: rgba(25, 10, 50, 0.9);
    border: 1px solid #c49a3c;
    border-radius: 6px;
    color: #e8d5b5;
    font-size: 0.85rem;
    outline: none;
}

.form-input-small:focus {
    border-color: #e6b84d;
}

.form-select-small {
    width: 100%;
    padding: 0.4rem 0.6rem;
    background: rgba(25, 10, 50, 0.9);
    border: 1px solid #c49a3c;
    border-radius: 6px;
    color: #e8d5b5;
    font-size: 0.85rem;
    outline: none;
    cursor: pointer;
}

.form-select-small:focus {
    border-color: #e6b84d;
}

.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 0.5rem;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.5rem;
    background: rgba(15, 5, 30, 0.6);
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem;
    color: #e8d5b5;
}

.checkbox-item:hover {
    background: rgba(40, 15, 70, 0.8);
}

.checkbox-item input[type="checkbox"] {
    accent-color: #e6b84d;
}

.textarea-small {
    width: 100%;
    padding: 0.5rem 0.7rem;
    background: rgba(25, 10, 50, 0.9);
    border: 1px solid #c49a3c;
    border-radius: 8px;
    color: #e8d5b5;
    font-size: 0.85rem;
    outline: none;
    resize: vertical;
    min-height: 60px;
}

.textarea-small:focus {
    border-color: #e6b84d;
}

@media (max-width: 900px) {
    .sheet-grid-6 {
        grid-template-columns: repeat(3, 1fr);
    }
    .sheet-grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .sheet-grid-6 {
        grid-template-columns: repeat(2, 1fr);
    }
    .sheet-grid-3,
    .sheet-grid-2 {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
<div class="sheet-container">
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>La sauvegarde a échoué.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('personnages.update', $personnage) }}" method="POST" id="editForm">
        @csrf
        @method('PATCH')

    <div class="sheet-header">
        <div class="sheet-header-left">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <a href="{{ route('personnages.show', $personnage) }}" class="text-muted" style="text-decoration: none; font-size: 0.85rem;">
                    ← Retour à la fiche
                </a>
            </div>
            <h2 class="title-main" style="margin-bottom: 0;">Modifier le personnage</h2>
        </div>
        <div class="sheet-header-right">
            <div class="stat-block">
                <div class="stat-block-label">Niveau</div>
                <input type="number" name="niveau" value="{{ old('niveau', $personnage->niveau) }}" min="1" max="20"
                    style="width:45px;">
            </div>
            <div class="stat-block">
                <div class="stat-block-label">PV</div>
                <input type="number" name="point_de_vie" value="{{ old('point_de_vie', $personnage->point_de_vie) }}" min="1"
                    style="width:55px;">
            </div>
        </div>
    </div>

        <div class="sheet-section">
            <div class="sheet-section-title">Identité</div>
            <div class="sheet-grid-4">
                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" value="{{ old('nom', $personnage->nom) }}" class="form-input-small" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Race</label>
                    <select name="race_id" id="raceSelect" class="form-select-small" required onchange="loadSousRaces()">
                        <option value="">-- Choisir --</option>
                        @foreach($races as $race)
                            <option value="{{ $race->id }}" {{ old('race_id', $personnage->race_id) == $race->id ? 'selected' : '' }}>
                                {{ $race->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sous-race</label>
                    @php
                        $raceActuelle = $races->firstWhere('id', old('race_id', $personnage->race_id));
                        $sousRaceRequise = $raceActuelle && $raceActuelle->sousRaces->isNotEmpty();
                    @endphp
                    <select name="sous_race_id" id="sousRaceSelect" class="form-select-small" {{ $sousRaceRequise ? 'required' : '' }}>
                        <option value="">-- Aucune --</option>
                        @if($raceActuelle)
                            @foreach($raceActuelle->sousRaces as $sousRace)
                                <option value="{{ $sousRace->id }}" {{ old('sous_race_id', $personnage->sous_race_id) == $sousRace->id ? 'selected' : '' }}>
                                    {{ $sousRace->nom }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Alignement</label>
                    <select name="alignement" class="form-select-small">
                        <option value="">-- Choisir --</option>
                        @foreach([
                            'Loyal bon', 'Neutre bon', 'Chaotique bon',
                            'Loyal neutre', 'Neutre', 'Chaotique neutre',
                            'Loyal méchant', 'Neutre méchant', 'Chaotique méchant',
                        ] as $align)
                            <option value="{{ $align }}" {{ old('alignement', $personnage->alignement ?? '') == $align ? 'selected' : '' }}>
                                {{ $align }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="sheet-grid-2 mt-2">
                <div class="form-group">
                    <label class="form-label">Classe</label>
                    <select name="classe_id" id="classeSelect" class="form-select-small" required onchange="loadSousClasses()">
                        <option value="">-- Choisir --</option>
                        @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" data-niveau-sous-classe="{{ $classe->niveau_sous_classe }}" data-a-des-sous-classes="{{ $classe->sousClasses->isNotEmpty() ? '1' : '0' }}" {{ old('classe_id', $personnage->classe_id) == $classe->id ? 'selected' : '' }}>
                                {{ $classe->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sous-classe</label>
                    @php
                        $classeActuelle = $classes->firstWhere('id', old('classe_id', $personnage->classe_id));
                        $sousClasseRequise = $classeActuelle
                            && $classeActuelle->sousClasses->isNotEmpty()
                            && $personnage->niveau >= $classeActuelle->niveau_sous_classe;
                        $sousClasseBloquee = $classeActuelle && $classeActuelle->sousClasses->isNotEmpty() && $personnage->niveau < $classeActuelle->niveau_sous_classe;
                    @endphp
                    <select name="sous_classe_id" id="sousClasseSelect" class="form-select-small" {{ $sousClasseBloquee ? 'disabled' : '' }} {{ $sousClasseRequise ? 'required' : '' }}>
                        <option value="">-- Aucune --</option>
                        @if($classeActuelle && ! $sousClasseBloquee)
                            @foreach($classeActuelle->sousClasses as $sousClasse)
                                <option value="{{ $sousClasse->id }}" {{ old('sous_classe_id', $personnage->sous_classe_id) == $sousClasse->id ? 'selected' : '' }}>
                                    {{ $sousClasse->nom }} (Niv. {{ $classeActuelle->niveau_sous_classe }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div id="sousClasseMessage" class="text-muted" style="font-size: 0.75rem; margin-top: 0.3rem;">
                        @if($sousClasseBloquee)
                            Sous-classe disponible au niveau {{ $classeActuelle->niveau_sous_classe }} (personnage actuellement niveau {{ $personnage->niveau }}).
                        @endif
                    </div>
                </div>
            </div>
            <div class="form-group mt-2">
                <label class="form-label">Description / Backstory</label>
                <textarea name="description" class="textarea-small" rows="3">{{ old('description', $personnage->description ?? '') }}</textarea>
            </div>
        </div>

        <div class="sheet-section">
            <div class="sheet-section-title">Caractéristiques</div>
            <div class="sheet-grid-6">
                @php
                    $stats = [
                        'force' => 'Force',
                        'dexterite' => 'Dex',
                        'constitution' => 'Con',
                        'intelligence' => 'Int',
                        'sagesse' => 'Sag',
                        'charisme' => 'Cha',
                    ];
                @endphp
                @foreach($stats as $key => $label)
                    @php
                        $value = old($key, $personnage->abilites->$key ?? 10);
                        $mod = floor(($value - 10) / 2);
                        $modStr = $mod >= 0 ? '+' . $mod : $mod;
                    @endphp
                    <div class="ability-box">
                        <div class="ability-name">{{ $label }}</div>
                        <input type="number" name="{{ $key }}" value="{{ $value }}" min="1" max="20"
                            class="ability-input"
                            oninput="updateMod(this)">
                        <div class="ability-mod" id="mod-{{ $key }}">{{ $modStr }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="sheet-grid-2">
            <div class="sheet-section">
                <div class="sheet-section-title">Compétences de classe (max {{ $personnage->classe->nombre_competences ?? 2 }})</div>
                @if($personnage->historique?->competences)
                    <div class="text-muted" style="font-size: 0.8rem; margin-bottom: 0.75rem;">
                        Compétences d’historique incluses et non modifiables : {{ implode(', ', $personnage->historique->competences) }}
                    </div>
                @endif
                <div class="checkbox-grid">
                    @php
                        $currentCompetences = old('competences', $personnage->listeCompetence->competences ?? []);
                        $competencesHistorique = $personnage->historique?->competences ?? [];
                    @endphp
                    @foreach($allCompetences as $comp)
                        @php $estAccordeeParHistorique = in_array($comp, $competencesHistorique, true); @endphp
                        <label class="checkbox-item">
                            <input type="checkbox" name="competences[]" value="{{ $comp }}"
                                {{ in_array($comp, $currentCompetences, true) ? 'checked' : '' }}
                                {{ $estAccordeeParHistorique ? 'disabled' : '' }}>
                            @if($estAccordeeParHistorique)
                                <input type="hidden" name="competences[]" value="{{ $comp }}">
                            @endif
                            {{ $comp }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="sheet-section">
                <div class="sheet-section-title">Historique & Contexte</div>
                <div class="form-group">
                    <label class="form-label">Historique *</label>
                    <select name="historique_id" id="edit_historique_select" class="form-select-small" required onchange="onEditHistoriqueChange(this.value)">
                        <option value="">-- Choisir un historique --</option>
                        @foreach($historiques as $h)
                            <option value="{{ $h->id }}" {{ old('historique_id', $personnage->historique_id) == $h->id ? 'selected' : '' }}>
                                {{ $h->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Idéal *</label>
                    <select name="ideal_id" id="edit_ideal_select" class="form-select-small" required>
                        <option value="">-- Choisir un idéal --</option>
                        @foreach($historiques as $h)
                            @foreach($h->ideaux as $item)
                                <option value="{{ $item->id }}" data-historique="{{ $h->id }}" {{ old('ideal_id', $personnage->ideal_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->valeur }}{{ $item->alignement ? ' (' . $item->alignement . ')' : '' }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Défaut *</label>
                    <select name="defaut_id" id="edit_defaut_select" class="form-select-small" required>
                        <option value="">-- Choisir un défaut --</option>
                        @foreach($historiques as $h)
                            @foreach($h->defauts as $item)
                                <option value="{{ $item->id }}" data-historique="{{ $h->id }}" {{ old('defaut_id', $personnage->defaut_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->valeur }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Attachement *</label>
                    <select name="attachement_id" id="edit_attachement_select" class="form-select-small" required>
                        <option value="">-- Choisir un attachement --</option>
                        @foreach($historiques as $h)
                            @foreach($h->attachements as $item)
                                <option value="{{ $item->id }}" data-historique="{{ $h->id }}" {{ old('attachement_id', $personnage->attachement_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->valeur }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Trait de personnalité *</label>
                    <select name="personnalite_id" id="edit_personnalite_select" class="form-select-small" required>
                        <option value="">-- Choisir une personnalité --</option>
                        @foreach($historiques as $h)
                            @foreach($h->personnalites as $item)
                                <option value="{{ $item->id }}" data-historique="{{ $h->id }}" {{ old('personnalite_id', $personnage->personnalite_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->valeur }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Langues (max {{ 2 + (int)floor(($personnage->niveau - 1) / 3) }})</label>
                    <div class="checkbox-grid">
                        @php
                            $currentLangues = old('langues', $personnage->langues ?? []);
                        @endphp
                        @foreach($langues as $lang)
                            <label class="checkbox-item">
                                <input type="checkbox" name="langues[]" value="{{ $lang }}"
                                    {{ in_array($lang, $currentLangues) ? 'checked' : '' }}>
                                {{ $lang }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="btn-group-row">
            <a href="{{ route('personnages.show', $personnage) }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Sauvegarder</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Les <option hidden> ne sont pas fiables dans une <select> native (surtout
// Firefox, qui les affiche quand même dans la liste déroulante) : on garde
// donc la liste complète de chaque select en mémoire et on reconstruit le
// contenu du select à partir de là à chaque changement d'historique, au
// lieu de simplement masquer les options qui ne correspondent pas.
const editHistoriqueOptionsStore = {};

function initEditHistoriqueOptionsStore(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;

    editHistoriqueOptionsStore[selectId] = {
        placeholder: select.options[0].outerHTML,
        current: select.value,
        options: Array.from(select.options).slice(1).map(opt => ({
            value: opt.value,
            historique: opt.dataset.historique,
            html: opt.innerHTML,
        })),
    };
}

function filterEditOptions(selectId, historiqueId) {
    const select = document.getElementById(selectId);
    const store = editHistoriqueOptionsStore[selectId];
    if (!select || !store) return;

    // Tant qu'aucun historique n'est choisi, ces champs sont entièrement
    // bloqués (comme "Outil" à l'étape Contexte de la création) : ils ne
    // dépendent que de l'historique, donc rien n'est sélectionnable avant.
    const bloque = historiqueId === '' || historiqueId === null || historiqueId === undefined;
    select.disabled = bloque;

    const optionsCorrespondantes = bloque
        ? []
        : store.options.filter(opt => opt.historique == historiqueId);

    select.innerHTML = store.placeholder + optionsCorrespondantes.map(opt =>
        '<option value="' + opt.value + '"' + (opt.value === store.current ? ' selected' : '') + '>' + opt.html + '</option>'
    ).join('');
}

function onEditHistoriqueChange(historiqueId) {
    filterEditOptions('edit_ideal_select', historiqueId);
    filterEditOptions('edit_defaut_select', historiqueId);
    filterEditOptions('edit_attachement_select', historiqueId);
    filterEditOptions('edit_personnalite_select', historiqueId);
}

document.addEventListener('DOMContentLoaded', function () {
    initEditHistoriqueOptionsStore('edit_ideal_select');
    initEditHistoriqueOptionsStore('edit_defaut_select');
    initEditHistoriqueOptionsStore('edit_attachement_select');
    initEditHistoriqueOptionsStore('edit_personnalite_select');

    const form = document.getElementById('editForm');
    form.addEventListener('invalid', function (event) {
        if (event.target.validity.valueMissing) {
            event.target.setCustomValidity(
                event.target.tagName === 'SELECT'
                    ? 'Veuillez sélectionner un élément dans la liste.'
                    : 'Veuillez remplir ce champ.'
            );
        }
    }, true);
    form.addEventListener('input', event => event.target.setCustomValidity(''));
    form.addEventListener('change', event => event.target.setCustomValidity(''));

    const histSel = document.getElementById('edit_historique_select');
    if (histSel && histSel.value) {
        onEditHistoriqueChange(histSel.value);
    } else {
        filterEditOptions('edit_ideal_select', '');
        filterEditOptions('edit_defaut_select', '');
        filterEditOptions('edit_attachement_select', '');
        filterEditOptions('edit_personnalite_select', '');
    }
});

function updateMod(input) {
    const key = input.name;
    const value = parseInt(input.value) || 10;
    const mod = Math.floor((value - 10) / 2);
    const modStr = mod >= 0 ? '+' + mod : mod;
    document.getElementById('mod-' + key).textContent = modStr;
}

function loadSousRaces() {
    const raceId = document.getElementById('raceSelect').value;
    const select = document.getElementById('sousRaceSelect');
    const sousRacesParRace = @json($races->mapWithKeys(fn ($race) => [$race->id => $race->sousRaces->map(fn ($sousRace) => ['id' => $sousRace->id, 'nom' => $sousRace->nom])]));
    const sousRaces = sousRacesParRace[raceId] || [];
    const conserverSelection = raceId === @json((string) old('race_id', $personnage->race_id));
    const current = conserverSelection ? @json(old('sous_race_id', $personnage->sous_race_id ?? '')) : '';

    select.innerHTML = '<option value="">-- Aucune --</option>';
    select.required = sousRaces.length > 0;

    sousRaces.forEach(sr => {
        const opt = document.createElement('option');
        opt.value = sr.id;
        opt.textContent = sr.nom;
        if (sr.id == current) opt.selected = true;
        select.appendChild(opt);
    });
}

function loadSousClasses() {
    const classeId = document.getElementById('classeSelect').value;
    const select = document.getElementById('sousClasseSelect');
    const message = document.getElementById('sousClasseMessage');
    const sousClassesParClasse = @json($classes->mapWithKeys(fn ($classe) => [$classe->id => $classe->sousClasses->map(fn ($sousClasse) => ['id' => $sousClasse->id, 'nom' => $sousClasse->nom])]));
    const sousClasses = sousClassesParClasse[classeId] || [];
    const classeOption = document.querySelector('#classeSelect option[value="' + classeId + '"]');
    const niveauRequis = classeOption ? parseInt(classeOption.dataset.niveauSousClasse, 10) : 0;
    const niveauPersonnage = {{ $personnage->niveau }};
    const current = @json(old('sous_classe_id', $personnage->sous_classe_id ?? ''));

    select.innerHTML = '<option value="">-- Aucune --</option>';
    select.disabled = false;
    select.required = sousClasses.length > 0;
    if (message) message.textContent = '';

    if (!classeId) return;

    const aDesSousClasses = classeOption && classeOption.dataset.aDesSousClasses === '1';

    if (aDesSousClasses && niveauPersonnage < niveauRequis) {
        select.disabled = true;
        select.required = false;
        if (message) {
            message.textContent = 'Sous-classe disponible au niveau ' + niveauRequis + ' (personnage actuellement niveau ' + niveauPersonnage + ').';
        }
        return;
    }

    sousClasses.forEach(sc => {
        const opt = document.createElement('option');
        opt.value = sc.id;
        opt.textContent = sc.nom + ' (Niv. ' + niveauRequis + ')';
        if (sc.id == current) opt.selected = true;
        select.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Les options initiales sont déjà rendues côté serveur (voir le select
    // ci-dessus) : on ne recharge via l'API que lors d'un vrai changement
    // de race/classe par l'utilisateur (onchange), pas au chargement de la
    // page, sinon un échec réseau viderait un sélecteur déjà correct.
});
</script>
@endpush
