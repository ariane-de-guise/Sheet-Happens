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
    <div class="dice-roll-table">
        <div class="stat-header-row">
            <span>Caractéristique</span>
            <span>Base</span>
            <span>Racial</span>
            <span>Total</span>
            <span class="text-center">Action</span>
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
                    <span class="stat-value" id="stat-{{ $key }}" data-key="{{ $key }}" data-bonus="{{ $bonus }}">{{ $baseVal > 0 ? $baseVal : '0' }}</span>
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
                    <div class="stat-final-num" id="total-{{ $key }}">{{ $finalScore }}</div>
                </div>

                <div class="stat-action-box">
                    <button type="button" class="dice-btn" onclick="rollDice('{{ $key }}')" id="btn-{{ $key }}">
                        {{ $baseVal > 0 ? 'Relancer' : 'Lancer' }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="btn-group-row">
        <a href="{{ route('creation.methode') }}" class="btn btn-secondary">Retour</a>
        <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
    </div>
</form>

@if(session('rolled'))
    @push('scripts')
        <script>
            const rolledStat = document.getElementById('stat-{{ session('rolled') }}');
            if (rolledStat) {
                rolledStat.style.transition = 'transform 0.3s';
                rolledStat.style.transform = 'scale(1.3)';
                setTimeout(() => { rolledStat.style.transform = 'scale(1)'; }, 300);
            }
        </script>
    @endpush
@endif

@push('scripts')
    <script>
        const statsOrder = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        function rollDice(abilite) {
            fetch('{{ route('creation.roll') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ abilite: abilite })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.value) {
                        const statEl = document.getElementById('stat-' + abilite);
                        const inputEl = document.getElementById('input-' + abilite);
                        const totalEl = document.getElementById('total-' + abilite);
                        const bonus = parseInt(statEl.dataset.bonus, 10) || 0;
                        const baseVal = parseInt(data.value, 10);

                        statEl.textContent = baseVal;
                        inputEl.value = baseVal;

                        if (totalEl) {
                            totalEl.textContent = baseVal + bonus;
                        }

                        const btn = document.getElementById('btn-' + abilite);
                        btn.textContent = 'Relancer';

                        statEl.style.transition = 'transform 0.3s';
                        statEl.style.transform = 'scale(1.3)';
                        setTimeout(() => { statEl.style.transform = 'scale(1)'; }, 300);

                        checkComplete();
                    }
                })
                .catch(err => console.error('Error rolling dice:', err));
        }

        function checkComplete() {
            const allRolled = statsOrder.every(s => {
                const val = parseInt(document.getElementById('stat-' + s).textContent, 10);
                return val > 0;
            });
            document.getElementById('btnSuivant').disabled = !allRolled;
        }

        checkComplete();
    </script>
@endpush
