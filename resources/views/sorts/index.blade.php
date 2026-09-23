@extends('layouts.app')

@section('title', 'Sorts')

@section('content')
    <div class="card spells-page">
        <div class="card-header">
            <h2>Sorts</h2>
        </div>

        <div class="card-body">
            <div class="spell-controls">
                <div class="spell-presets">
                    {{-- Boutons d'ajout retirés --}}
                </div>

                @php
                    $abiliteMagieNom = $abiliteMagie ?? ($abilite ?? null);
                    $abiliteKey = null;
                    if (!empty($abiliteMagieNom)) {
                        $abiliteKey = strtolower($abiliteMagieNom);
                    }

                    $abiliteScore = null;
                    if (!empty($personnage) && $abiliteKey && isset($personnage->abilites->{$abiliteKey})) {
                        $abiliteScore = (int) $personnage->abilites->{$abiliteKey};
                    }

                    $modificateurAbilite = $abiliteScore !== null ? (int) floor(($abiliteScore - 10) / 2) : 0;
                    $niveauPersonnage = !empty($personnage->niveau) ? (int) $personnage->niveau : (int) ($niveau ?? 0);
                    $bonusMaitrise = 2 + intdiv(max(1, $niveauPersonnage) - 1, 4);

                    $ddSauvegardeSort = 8 + $bonusMaitrise + $modificateurAbilite;
                    $modificateurSort = $modificateurAbilite + $bonusMaitrise;

                    $limiteAffichee = isset($limiteSort) && $limiteSort !== null ? (int) $limiteSort : ($niveauPersonnage + $modificateurAbilite);
                @endphp

                <div class="spell-limit">Limite tour de magie : <strong>{{ $limiteAffichee }}</strong></div>
                @php $displayAbility = $abiliteMagie ?? ($abiliteMagieNom ?? ($abilite ?? null)); @endphp
                <div style="margin-top:0.5rem; color:#f1d484; font-weight:600;">
                    Abilité sort: {{ $displayAbility ?? '—' }} @if(!empty($abiliteSource) && $abiliteSource === 'sous_classe') <span style="color:#d8b86a">(sous-classe)</span>@endif @if($abiliteScore !== null)({{ $abiliteScore }})@endif
                    <br>
                    Sauvegarde sort: <strong>{{ $ddSauvegarde }}</strong>
                    <br>
                    Modificateur sort: <strong>{{ $modificateurSort >= 0 ? '+' . $modificateurSort : $modificateurSort }}</strong>
                </div>
            </div>

            <div class="spell-rounds card">
                <h3>Tour magie</h3>
                <div class="spell-grid">
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                    <label><input type="checkbox"> Sort</label>
                </div>
            </div>

            <div class="spell-levels">
                <div class="levels-row">
                    <div class="level-card card">
                        <h4>Niveau 1</h4>
                        <div class="slots">Emplacement : <span>{{ $slots['Niveau1'] ?? '..' }}</span></div>
                        <ul class="spell-list">
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                        </ul>
                    </div>

                    <div class="level-card card">
                        <h4>Niveau 2</h4>
                        <div class="slots">Emplacement : <span>{{ $slots['Niveau2'] ?? '..' }}</span></div>
                        <ul class="spell-list">
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                        </ul>
                    </div>

                    <div class="level-card card">
                        <h4>Niveau 3</h4>
                        <div class="slots">Emplacement : <span>{{ $slots['Niveau3'] ?? '..' }}</span></div>
                        <ul class="spell-list">
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                            <li><label><input type="checkbox"> Sort</label></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="actions mt-3">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Retour</a>
            </div>
        </div>
    </div>
@endsection
