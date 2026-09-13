<form action="{{ route('creation.caracteristiques.save') }}" method="POST" id="mainForm">
    @csrf
    <p class="drag-hint mb-2">Glissez une valeur sur la caractéristique de votre choix (ou cliquez sur une valeur pour l'attribuer automatiquement). Cliquez sur une caractéristique déjà remplie pour la vider.</p>
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
                    <span class="stat-value drop-target {{ $abilites[$key] > 0 ? 'filled' : '' }}"
                          id="stat-{{ $key }}" data-key="{{ $key }}"
                          ondragover="onSlotDragOver(event, this)"
                          ondragleave="onSlotDragLeave(event, this)"
                          ondrop="onSlotDrop(event, this)"
                          onclick="clearSlot('{{ $key }}')">{{ $abilites[$key] > 0 ? $abilites[$key] : 'X' }}</span>
                    <input type="hidden" name="{{ $key }}" id="input-{{ $key }}" value="{{ $abilites[$key] }}">
                </div>
            @endforeach
        </div>

        <div>
            <div class="label mb-1" style="color:#e6b84d;">Valeurs disponibles</div>
            <div class="value-pool" id="valuePool">
                @foreach($standardArray as $value)
                    <div class="value-chip" draggable="true" data-value="{{ $value }}"
                         id="chip-{{ $value }}"
                         ondragstart="onChipDragStart(event, {{ $value }})"
                         ondragend="onChipDragEnd(event)"
                         onclick="assignToNextEmpty({{ $value }})">{{ $value }}</div>
                @endforeach
            </div>
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
        let currentAbilites = @json($abilites);
        let draggedValue = null;

        function chipEl(value) {
            return document.getElementById('chip-' + value);
        }

        function slotEl(key) {
            return document.getElementById('stat-' + key);
        }

        function setChipUsed(value, used) {
            const chip = chipEl(value);
            if (!chip) return;
            chip.classList.toggle('is-used', used);
            chip.draggable = !used;
        }

        function assignValue(key, value) {
            // Si la case contenait déjà une valeur, on la remet dans le lot.
            const previous = currentAbilites[key];
            if (previous && previous > 0) {
                setChipUsed(previous, false);
            }

            currentAbilites[key] = value;
            const slot = slotEl(key);
            slot.textContent = value;
            slot.classList.add('filled');
            document.getElementById('input-' + key).value = value;
            setChipUsed(value, true);

            checkComplete();
        }

        function clearSlot(key) {
            const previous = currentAbilites[key];
            if (!previous || previous <= 0) return;

            setChipUsed(previous, false);
            currentAbilites[key] = 0;
            const slot = slotEl(key);
            slot.textContent = 'X';
            slot.classList.remove('filled');
            document.getElementById('input-' + key).value = 0;

            checkComplete();
        }

        function assignToNextEmpty(value) {
            if (chipEl(value).classList.contains('is-used')) return;
            const nextKey = statsOrder.find(s => !currentAbilites[s] || currentAbilites[s] === 0);
            if (!nextKey) return;
            assignValue(nextKey, value);
        }

        function onChipDragStart(event, value) {
            if (chipEl(value).classList.contains('is-used')) {
                event.preventDefault();
                return;
            }
            draggedValue = value;
            event.dataTransfer.setData('text/plain', String(value));
            event.dataTransfer.effectAllowed = 'move';
            chipEl(value).classList.add('dragging');
        }

        function onChipDragEnd(event) {
            if (draggedValue !== null) {
                chipEl(draggedValue).classList.remove('dragging');
            }
            draggedValue = null;
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
            const value = parseInt(event.dataTransfer.getData('text/plain'), 10);
            if (!value) return;
            assignValue(slot.dataset.key, value);
        }

        function resetStandard() {
            statsOrder.forEach(s => clearSlot(s));
        }

        function checkComplete() {
            const allAssigned = statsOrder.every(s => currentAbilites[s] > 0);
            document.getElementById('btnSuivant').disabled = !allAssigned;
            document.getElementById('pointsDisponibles').value = allAssigned ? '1' : '0';
        }

        // Initialise l'état "utilisée" des jetons déjà attribués (retour en arrière).
        statsOrder.forEach(s => {
            if (currentAbilites[s] > 0) setChipUsed(currentAbilites[s], true);
        });

        checkComplete();
    </script>
@endpush
