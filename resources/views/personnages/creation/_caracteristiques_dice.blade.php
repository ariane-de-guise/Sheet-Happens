<form action="{{ route('creation.caracteristiques.save') }}" method="POST" id="mainForm">
    @csrf
    <div class="roll-actions">
        <button type="button" class="btn btn-primary" id="btnRoll" onclick="rollDice()">
            {{ $dicePool ? 'Relancer les dés' : 'Lancer les dés' }}
        </button>
        <span class="drag-hint">4d6, on garde les 3 meilleurs — 6 lancers au total. Glissez ensuite chaque résultat sur la caractéristique de votre choix.</span>
    </div>

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
                    <span class="stat-value drop-target {{ $abilites[$key] > 0 ? 'filled' : '' }}"
                          id="stat-{{ $key }}" data-key="{{ $key }}"
                          ondragover="onSlotDragOver(event, this)"
                          ondragleave="onSlotDragLeave(event, this)"
                          ondrop="onSlotDrop(event, this)"
                          onclick="clearSlot('{{ $key }}')">{{ $abilites[$key] > 0 ? $abilites[$key] : '0' }}</span>
                    <input type="hidden" name="{{ $key }}" id="input-{{ $key }}" value="{{ $abilites[$key] }}">
                </div>
            @endforeach
        </div>

        <div>
            <div class="label mb-1" style="color:#e6b84d;">Résultats des lancers</div>
            <div class="value-pool" id="valuePool"></div>
        </div>
    </div>

    <div class="btn-group-row">
        <a href="{{ route('creation.methode') }}" class="btn btn-secondary">Retour</a>
        <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
    </div>
</form>

@push('scripts')
    <script>
        const statsOrder = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        let currentAbilites = @json($abilites);
        let dicePool = @json($dicePool ?? []);
        // slotIndex[key] = index (dans dicePool) du jeton actuellement placé sur cette caractéristique
        let slotIndex = {};
        statsOrder.forEach(s => { slotIndex[s] = null; });
        let draggedIndex = null;

        function chipEl(index) {
            return document.getElementById('chip-' + index);
        }

        function slotElement(key) {
            return document.getElementById('stat-' + key);
        }

        function renderPool() {
            const pool = document.getElementById('valuePool');
            pool.innerHTML = '';
            dicePool.forEach((value, index) => {
                const chip = document.createElement('div');
                chip.className = 'value-chip';
                chip.id = 'chip-' + index;
                chip.textContent = value;
                chip.draggable = true;
                chip.setAttribute('ondragstart', 'onChipDragStart(event, ' + index + ')');
                chip.setAttribute('ondragend', 'onChipDragEnd(event)');
                chip.setAttribute('onclick', 'assignToNextEmpty(' + index + ')');
                pool.appendChild(chip);
            });
        }

        function setChipUsed(index, used) {
            const chip = chipEl(index);
            if (!chip) return;
            chip.classList.toggle('is-used', used);
            chip.draggable = !used;
        }

        function assignValue(key, index) {
            const previousIndex = slotIndex[key];
            if (previousIndex !== null && previousIndex !== undefined) {
                setChipUsed(previousIndex, false);
            }

            slotIndex[key] = index;
            currentAbilites[key] = dicePool[index];
            const slot = slotElement(key);
            slot.textContent = dicePool[index];
            slot.classList.add('filled');
            document.getElementById('input-' + key).value = dicePool[index];
            setChipUsed(index, true);

            checkComplete();
        }

        function clearSlot(key) {
            const previousIndex = slotIndex[key];
            if (previousIndex === null || previousIndex === undefined) return;

            setChipUsed(previousIndex, false);
            slotIndex[key] = null;
            currentAbilites[key] = 0;
            const slot = slotElement(key);
            slot.textContent = '0';
            slot.classList.remove('filled');
            document.getElementById('input-' + key).value = 0;

            checkComplete();
        }

        function assignToNextEmpty(index) {
            if (chipEl(index).classList.contains('is-used')) return;
            const nextKey = statsOrder.find(s => slotIndex[s] === null || slotIndex[s] === undefined);
            if (!nextKey) return;
            assignValue(nextKey, index);
        }

        function onChipDragStart(event, index) {
            if (chipEl(index).classList.contains('is-used')) {
                event.preventDefault();
                return;
            }
            draggedIndex = index;
            event.dataTransfer.setData('text/plain', String(index));
            event.dataTransfer.effectAllowed = 'move';
            chipEl(index).classList.add('dragging');
        }

        function onChipDragEnd() {
            if (draggedIndex !== null) {
                const chip = chipEl(draggedIndex);
                if (chip) chip.classList.remove('dragging');
            }
            draggedIndex = null;
        }

        function onSlotDragOver(event, slot) {
            event.preventDefault();
            slot.classList.add('drag-over');
        }

        function onSlotDragLeave(event, slot) {
            slot.classList.remove('drag-over');
        }

        function onSlotDrop(event, slot) {
            event.preventDefault();
            slot.classList.remove('drag-over');
            const index = parseInt(event.dataTransfer.getData('text/plain'), 10);
            if (isNaN(index)) return;
            assignValue(slot.dataset.key, index);
        }

        function resetAssignments() {
            statsOrder.forEach(s => clearSlot(s));
        }

        function checkComplete() {
            const allAssigned = statsOrder.every(s => currentAbilites[s] > 0);
            document.getElementById('btnSuivant').disabled = !allAssigned;
        }

        function rollDice() {
            document.getElementById('btnRoll').disabled = true;
            fetch('{{ route('creation.roll') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({}),
            })
                .then(response => response.json())
                .then(data => {
                    dicePool = data.rolls;
                    statsOrder.forEach(s => {
                        slotIndex[s] = null;
                        currentAbilites[s] = 0;
                        const slot = slotElement(s);
                        slot.textContent = '0';
                        slot.classList.remove('filled');
                        document.getElementById('input-' + s).value = 0;
                    });
                    renderPool();
                    checkComplete();
                    document.getElementById('btnRoll').textContent = 'Relancer les dés';
                })
                .catch(err => console.error('Erreur lors du lancer de dés :', err))
                .finally(() => {
                    document.getElementById('btnRoll').disabled = false;
                });
        }

        // Reconstruction de l'affichage si l'on revient sur cette page après
        // avoir déjà réparti un lancer précédent (bouton "Retour" depuis
        // l'étape suivante). Comme deux dés peuvent donner la même valeur, on
        // réattribue chaque caractéristique déjà remplie au premier jeton
        // disponible de même valeur.
        if (dicePool.length === 6) {
            renderPool();
            const used = new Set();
            statsOrder.forEach(key => {
                const value = currentAbilites[key];
                if (!value || value <= 0) return;
                const index = dicePool.findIndex((v, i) => v === value && !used.has(i));
                if (index !== -1) {
                    used.add(index);
                    slotIndex[key] = index;
                    setChipUsed(index, true);
                }
            });
        }

        checkComplete();
    </script>
@endpush
