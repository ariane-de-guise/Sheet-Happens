@php
    $race = $race ?? \App\Models\Race::find(session('personnage_creation.race_id'));
    $sousRace = $sousRace ?? (session('personnage_creation.sous_race_id') ? \App\Models\SousRace::find(session('personnage_creation.sous_race_id')) : null);

    $stats = [
        'force' => 'Force',
        'dexterite' => 'Dextérité',
        'constitution' => 'Constitution',
        'intelligence' => 'Intelligence',
        'sagesse' => 'Sagesse',
        'charisme' => 'Charisme',
    ];
@endphp

<form action="{{ route('creation.caracteristiques.save') }}" method="POST" class="stat-sheet-form" id="mainForm">
    @csrf
    <div class="stats-container">
        <div class="dice-roll-table">
            <div class="stat-header-row">
                <span>Caractéristique</span>
                <span>Base (8–15)</span>
                <span>Racial</span>
                <span>Total</span>
            </div>

            @foreach($stats as $key => $label)
                @php
                    $raceBonus = (int) data_get($race, "bonus_aptitudes.$key", 0);
                    $subRaceBonus = (int) data_get($sousRace, "bonus_aptitudes.$key", 0);
                    $bonus = $raceBonus + $subRaceBonus;

                    $baseVal = (int) ($abilites[$key] ?? 8);
                    $finalScore = $baseVal + $bonus;
                @endphp

                <div class="stat-row">
                    <div class="stat-name-block">
                        <span class="stat-name">{{ $label }}</span>
                    </div>

                    <div class="stat-controls-wrapper">
                        <button type="button" class="btn-stat btn-stat-minus"
                                id="btn-minus-{{ $key }}"
                                onclick="modifierStat('{{ $key }}', -1)"
                            {{ $baseVal <= 8 ? 'disabled' : '' }}>−</button>

                        <input type="number" name="{{ $key }}" value="{{ $baseVal }}"
                               min="8" max="15" class="stat-value" id="stat-{{ $key }}"
                               data-key="{{ $key }}" data-bonus="{{ $bonus }}" readonly>

                        <button type="button" class="btn-stat btn-stat-plus"
                                id="btn-plus-{{ $key }}"
                                onclick="modifierStat('{{ $key }}', 1)">+</button>
                    </div>

                    <div class="stat-bonus-box">
                        @if($bonus > 0)
                            <span class="badge-racial" title="Bonus de race et sous-race">+{{ $bonus }}</span>
                        @else
                            <span class="badge-empty">—</span>
                        @endif
                    </div>

                    <div class="stat-final-card">
                        <div class="stat-final-num" id="total-{{ $key }}">{{ $finalScore }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="stats-sidebar">
            <div class="points-box">
                <div class="label">Points de création restants</div>
                <div class="value" id="pointsRestants">27</div>
                <div class="subtext">Budget initial : 27</div>
            </div>

            <div class="help-box">
                <div class="title">Règle d'achat par points (D&D 5e)</div>
                <div class="content">
                    Toutes les caractéristiques débutent à <strong>8</strong>.<br>
                    <strong>Coût par palier :</strong><br>
                    • 8 à 13 : 1 pt par niveau<br>
                    • 14 : coûte 2 pts (7 au total)<br>
                    • 15 : coûte 2 pts (9 au total)
                </div>
            </div>
        </div>
    </div>

    <div class="btn-group-row">
        <a href="{{ route('creation.methode') }}" class="btn btn-secondary">Retour</a>
        <button type="submit" class="btn btn-primary" id="btnSuivant">Confirmer les caractéristiques</button>
    </div>
</form>

@push('scripts')
    <script>
        const costTable = { 8: 0, 9: 1, 10: 2, 11: 3, 12: 4, 13: 5, 14: 7, 15: 9 };
        const totalBudget = 27;

        function getCost(val) {
            return costTable[val] ?? 0;
        }

        function getTotalCost() {
            let total = 0;
            document.querySelectorAll('.stat-value').forEach(input => {
                total += getCost(parseInt(input.value, 10) || 8);
            });
            return total;
        }

        function updateUI() {
            const used = getTotalCost();
            const remaining = totalBudget - used;
            const display = document.getElementById('pointsRestants');

            display.textContent = remaining;
            display.classList.toggle('negative', remaining < 0);
            display.classList.toggle('zero', remaining === 0);

            const nextBtn = document.getElementById('btnSuivant');
            nextBtn.disabled = remaining !== 0;

            document.querySelectorAll('.stat-value').forEach(input => {
                const key = input.dataset.key;
                const bonus = parseInt(input.dataset.bonus, 10) || 0;
                const baseVal = parseInt(input.value, 10) || 8;
                const finalScore = baseVal + bonus;

                const totalDisplay = document.getElementById('total-' + key);
                if (totalDisplay) totalDisplay.textContent = finalScore;

                const minusBtn = document.getElementById('btn-minus-' + key);
                const plusBtn = document.getElementById('btn-plus-' + key);

                if (minusBtn) minusBtn.disabled = baseVal <= 8;

                if (plusBtn) {
                    const nextCost = getCost(baseVal + 1) - getCost(baseVal);
                    plusBtn.disabled = baseVal >= 15 || remaining < nextCost;
                }
            });
        }

        function modifierStat(key, delta) {
            const input = document.getElementById('stat-' + key);
            const value = parseInt(input.value, 10) || 8;
            const targetValue = value + delta;

            if (targetValue < 8 || targetValue > 15) return;

            const costDelta = getCost(targetValue) - getCost(value);
            const used = getTotalCost();

            if (delta > 0 && (used + costDelta) > totalBudget) return;

            input.value = targetValue;
            updateUI();
        }

        document.addEventListener('DOMContentLoaded', updateUI);
    </script>
@endpush
