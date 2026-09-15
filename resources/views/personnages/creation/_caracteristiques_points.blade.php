<form action="{{ route('creation.caracteristiques.save') }}" method="POST">
    @csrf
    <div class="stats-container">
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
                    <input type="number" name="{{ $key }}" value="{{ $abilites[$key] }}"
                           min="8" max="15" class="stat-value" id="stat-{{ $key }}"
                           data-key="{{ $key }}">
                    <div class="stat-controls">
                        <button type="button" class="btn btn-primary btn-icon"
                                onclick="modifierStat('{{ $key }}', 1)">+</button>
                        <button type="button" class="btn btn-secondary btn-icon"
                                onclick="modifierStat('{{ $key }}', -1)">-</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            <div class="points-box">
                <div class="label">Points restants</div>
                <div class="value" id="pointsRestants">27</div>
            </div>

            <div class="help-box">
                <div class="title">Aide</div>
                <div class="content">
                    Chaque caractéristique commence à 8. Augmenter une valeur coûte des points croissants :
                    9(1), 10(2), 11(3), 12(4), 13(6), 14(8), 15(10).
                </div>
            </div>
        </div>
    </div>

    <div class="btn-group-row">
        <a href="{{ route('creation.methode') }}" class="btn btn-secondary">Retour</a>
        <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
    </div>
</form>

@push('scripts')
<script>
    const costTable = {
        8: 0, 9: 1, 10: 2, 11: 3, 12: 4, 13: 6, 14: 8, 15: 10
    };
    const totalBudget = 27;

    function getCost(value) {
        return costTable[value] ?? 0;
    }

    function getTotalCost() {
        let total = 0;
        document.querySelectorAll('.stat-value').forEach(input => {
            total += getCost(parseInt(input.value) || 8);
        });
        return total;
    }

    function updateUI() {
        const used = getTotalCost();
        const remaining = totalBudget - used;
        const display = document.getElementById('pointsRestants');
        display.textContent = remaining;
        display.style.color = remaining < 0 ? '#e05555' : '#e6b84d';
        document.getElementById('btnSuivant').disabled = remaining !== 0;
    }

    function modifierStat(key, delta) {
        const input = document.getElementById('stat-' + key);
        let value = parseInt(input.value) || 8;
        const newCost = getCost(value + delta);
        const currentCost = getCost(value);
        const used = getTotalCost();

        if (delta > 0 && value >= 15) return;
        if (delta > 0 && used + (newCost - currentCost) > totalBudget) return;
        if (delta < 0 && value <= 8) return;

        input.value = value + delta;
        updateUI();
    }

    document.querySelectorAll('.stat-value').forEach(input => {
        input.addEventListener('change', function() {
            let val = parseInt(this.value);
            if (isNaN(val)) val = 8;
            if (val < 8) val = 8;
            if (val > 15) val = 15;
            this.value = val;
            updateUI();
        });
    });

    updateUI();
</script>
@endpush
