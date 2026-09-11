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

<form action="{{ route('creation.caracteristiques.save') }}" method="POST" id="mainForm">
    @csrf
    <div class="standard-points-container">
        <!-- 1. Left: Shortened dice roll table -->
        <div class="dice-roll-table">
            <div class="stat-header-row">
                <span>Caractéristique</span>
                <span>Base</span>
                <span>Racial</span>
                <span>Total</span>
            </div>

            @foreach($stats as $key => $label)
                @php
                    $raceBonus = (int) data_get($race, "bonus_aptitudes.$key", 0);
                    $subRaceBonus = (int) data_get($sousRace, "bonus_aptitudes.$key", 0);
                    $bonus = $raceBonus + $subRaceBonus;

                    $baseVal = (int) ($abilites[$key] ?? 0);
                    $finalScore = $baseVal > 0 ? ($baseVal + $bonus) : 0;
                @endphp

                <div class="stat-row">
                    <div class="stat-name-block">
                        <span class="stat-name">{{ $label }}</span>
                    </div>

                    <div class="stat-controls-wrapper">
                        <span class="stat-value" id="stat-{{ $key }}" data-key="{{ $key }}" data-bonus="{{ $bonus }}">{{ $baseVal > 0 ? $baseVal : '—' }}</span>
                        <input type="hidden" name="{{ $key }}" id="input-{{ $key }}" value="{{ $baseVal }}">
                    </div>

                    <div class="stat-bonus-box">
                        @if($bonus > 0)
                            <span class="badge-racial" title="Bonus de race et sous-race">+{{ $bonus }}</span>
                        @else
                            <span class="badge-empty">—</span>
                        @endif
                    </div>

                    <div class="stat-final-card">
                        <div class="stat-final-num" id="total-{{ $key }}">{{ $finalScore > 0 ? $finalScore : '—' }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 2. Right: Available Points -->
        <div class="available-points-list">
            <div class="available-header">
                <span>Valeurs disponibles</span>
            </div>

            @php
                $standardArray = [15, 14, 13, 12, 10, 8];
                $assigned = array_filter($abilites, fn($v) => $v > 0);
            @endphp
            @foreach($standardArray as $point)
                @php
                    $countInArray = count(array_filter($standardArray, fn($v) => $v === $point));
                    $countAssigned = count(array_filter($assigned, fn($v) => $v === $point));
                    $available = $countInArray - $countAssigned;
                    $disabled = $available <= 0;
                @endphp
                <button type="button" class="point-option"
                        {{ $disabled ? 'disabled' : '' }}
                        onclick="assignPoint({{ $point }})">
                    {{ $point }} points
                </button>
            @endforeach
        </div>
    </div>

    <input type="hidden" name="points_disponibles" id="pointsDisponibles">

    <!-- Bottom: Navigation buttons across full width -->
    <!-- Bottom: Navigation buttons aligned with the columns above -->
    <div class="btn-group-row">
        <div class="btn-group-left">
            <a href="{{ route('creation.methode') }}" class="btn btn-secondary">Retour</a>
        </div>
        <div class="btn-group-right">
            <button type="button" class="btn btn-secondary" onclick="resetStandard()">Réinitialiser</button>
            <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
        </div>
    </div>
</form>
</form>

@push('scripts')
    <script>
        const statsOrder = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];
        const standardArray = [15, 14, 13, 12, 10, 8];
        let currentAbilites = @json($abilites);

        function assignPoint(value) {
            const nextStat = statsOrder.find(s => !currentAbilites[s] || currentAbilites[s] === 0);
            if (!nextStat) return;

            currentAbilites[nextStat] = value;
            const statEl = document.getElementById('stat-' + nextStat);
            const inputEl = document.getElementById('input-' + nextStat);
            const totalEl = document.getElementById('total-' + nextStat);
            const bonus = parseInt(statEl.dataset.bonus, 10) || 0;

            statEl.textContent = value;
            inputEl.value = value;
            if (totalEl) {
                totalEl.textContent = value + bonus;
            }

            updateButtons();
            checkComplete();
        }

        function updateButtons() {
            const assigned = Object.values(currentAbilites).filter(v => v > 0);

            document.querySelectorAll('.point-option').forEach(btn => {
                const text = btn.textContent;
                const val = parseInt(text, 10);
                const countInArray = standardArray.filter(v => v === val).length;
                const countAssigned = assigned.filter(v => v === val).length;
                const available = countInArray - countAssigned;

                btn.disabled = available <= 0;
                btn.textContent = val + ' points' + (countInArray > 1 ? ' (' + available + ')' : '');
            });
        }

        function checkComplete() {
            const allAssigned = statsOrder.every(s => currentAbilites[s] > 0);
            document.getElementById('btnSuivant').disabled = !allAssigned;
            document.getElementById('pointsDisponibles').value = allAssigned ? '1' : '0';
        }

        function resetStandard() {
            statsOrder.forEach(s => {
                currentAbilites[s] = 0;
                document.getElementById('stat-' + s).textContent = '—';
                document.getElementById('input-' + s).value = '0';
                const totalEl = document.getElementById('total-' + s);
                if (totalEl) {
                    totalEl.textContent = '—';
                }
            });
            updateButtons();
            checkComplete();
        }

        checkComplete();
    </script>
@endpush
