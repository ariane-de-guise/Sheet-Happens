<form action="{{ route('creation.caracteristiques.save') }}" method="POST" id="mainForm">
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
                    <span class="stat-value" id="stat-{{ $key }}">{{ $abilites[$key] > 0 ? $abilites[$key] : '0' }}</span>
                    <input type="hidden" name="{{ $key }}" id="input-{{ $key }}" value="{{ $abilites[$key] }}">
                </div>
            @endforeach
        </div>

        <div class="dice-container">
            @foreach($stats as $key => $label)
                <button type="button" class="dice-btn" onclick="rollDice('{{ $key }}')" id="btn-{{ $key }}">
                    {{ $abilites[$key] > 0 ? 'Relancer' : 'Dé' }}
                </button>
            @endforeach
        </div>
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
        rolledStat.style.transition = 'transform 0.3s';
        rolledStat.style.transform = 'scale(1.3)';
        setTimeout(() => { rolledStat.style.transform = 'scale(1)'; }, 300);
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
                document.getElementById('stat-' + abilite).textContent = data.value;
                document.getElementById('input-' + abilite).value = data.value;

                const btn = document.getElementById('btn-' + abilite);
                btn.textContent = 'Relancer';

                const statEl = document.getElementById('stat-' + abilite);
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
            const val = parseInt(document.getElementById('stat-' + s).textContent);
            return val > 0;
        });
        document.getElementById('btnSuivant').disabled = !allRolled;
    }

    checkComplete();
</script>
@endpush
