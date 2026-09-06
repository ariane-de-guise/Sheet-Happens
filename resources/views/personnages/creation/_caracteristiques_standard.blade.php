<form action="{{ route('creation.caracteristiques.save') }}" method="POST">
    @csrf
    <div class="standard-points-container">
        <div class="stats-list">
            @php
                $stats = [
                    'force' => 'Force',
                    'dexterite' => 'Dextérité',
                    'constitution' => 'Constitution',
                    'intelligence' => 'Intelligence',
                    'sagesse' => 'Sagesse',
                    'charisme' => 'Charisme',
                ];
            @endphp
            @foreach($stats as $key => $label)
                <div class="stat-row">
                    <div class="stat-icon"></div>
                    <span class="stat-name">{{ $label }}</span>
                    <span class="stat-value" id="stat-{{ $key }}">{{ $abilites[$key] > 0 ? $abilites[$key] : 'X' }}</span>
                    <input type="hidden" name="{{ $key }}" id="input-{{ $key }}" value="{{ $abilites[$key] }}">
                </div>
            @endforeach
        </div>

        <div class="available-points">
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

    <div class="btn-group-row">
        <a href="{{ route('creation.methode') }}" class="btn btn-secondary">Retour</a>
        <div>
            <button type="button" class="btn btn-secondary" onclick="resetStandard()">Réinitialiser</button>
            <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    const statsOrder = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];
    const standardArray = [15, 14, 13, 12, 10, 8];
    let currentAbilites = @json($abilites);

    function assignPoint(value) {
        const nextStat = statsOrder.find(s => currentAbilites[s] === 0);
        if (!nextStat) return;

        currentAbilites[nextStat] = value;
        document.getElementById('stat-' + nextStat).textContent = value;
        document.getElementById('input-' + nextStat).value = value;

        updateButtons();
        checkComplete();
    }

    function updateButtons() {
        const assigned = Object.values(currentAbilites).filter(v => v > 0);

        document.querySelectorAll('.point-option').forEach(btn => {
            const text = btn.textContent;
            const val = parseInt(text);
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
            document.getElementById('stat-' + s).textContent = 'X';
            document.getElementById('input-' + s).value = '0';
        });
        updateButtons();
        checkComplete();
    }

    checkComplete();
</script>
@endpush
