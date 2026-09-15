// Étape "Caractéristiques" (méthode "points par défaut" / tableau
// standard) de la création de personnage. Les données
// (window.CaracteristiquesStandardData) sont injectées par
// _caracteristiques_standard.blade.php.
const statsOrder = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];
let currentAbilites = window.CaracteristiquesStandardData.abilites;
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

function updateSlotDisplay(key) {
    const value = currentAbilites[key];
    const slot = slotEl(key);
    const input = document.getElementById('input-' + key);

    if (!value || value <= 0) {
        slot.textContent = 'X';
        slot.classList.remove('filled');
        slot.draggable = false;
        input.value = 0;
        return;
    }

    slot.textContent = value;
    slot.classList.add('filled');
    slot.draggable = true;
    input.value = value;
}

function assignValue(key, value) {
    // Si la case contenait déjà une valeur, on la remet dans le lot.
    const previous = currentAbilites[key];
    if (previous && previous > 0) {
        setChipUsed(previous, false);
    }

    currentAbilites[key] = value;
    updateSlotDisplay(key);
    setChipUsed(value, true);

    checkComplete();
}

function clearSlot(key) {
    const previous = currentAbilites[key];
    if (!previous || previous <= 0) return;

    setChipUsed(previous, false);
    currentAbilites[key] = 0;
    updateSlotDisplay(key);

    checkComplete();
}

function swapSlots(keyA, keyB) {
    if (keyA === keyB) return;
    const valueA = currentAbilites[keyA];
    const valueB = currentAbilites[keyB];

    currentAbilites[keyA] = valueB;
    currentAbilites[keyB] = valueA;

    updateSlotDisplay(keyA);
    updateSlotDisplay(keyB);
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
    const raw = event.dataTransfer.getData('text/plain');

    if (raw.startsWith('stat:')) {
        swapSlots(raw.slice(5), slot.dataset.key);
        return;
    }

    const value = parseInt(raw, 10);
    if (!value) return;
    assignValue(slot.dataset.key, value);
}

function onStatDragStart(event, key) {
    if (!currentAbilites[key] || currentAbilites[key] <= 0) {
        event.preventDefault();
        return;
    }
    event.dataTransfer.setData('text/plain', 'stat:' + key);
    event.dataTransfer.effectAllowed = 'move';
    slotEl(key).classList.add('dragging');
}

function onStatDragEnd(key) {
    const slot = slotEl(key);
    if (slot) slot.classList.remove('dragging');
}

function onPoolDragOver(event) {
    event.preventDefault();
    document.getElementById('valuePool').classList.add('drag-over');
}

function onPoolDragLeave() {
    document.getElementById('valuePool').classList.remove('drag-over');
}

function onPoolDrop(event) {
    event.preventDefault();
    document.getElementById('valuePool').classList.remove('drag-over');
    const raw = event.dataTransfer.getData('text/plain');
    if (raw.startsWith('stat:')) {
        clearSlot(raw.slice(5));
    }
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
