// Étape "Caractéristiques" (méthode "lancer de dés") de la création de
// personnage. Les données (window.CaracteristiquesDiceData) sont injectées
// par _caracteristiques_dice.blade.php.
const statsOrder = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const rollDiceUrl = window.CaracteristiquesDiceData.rollUrl;

let currentAbilites = window.CaracteristiquesDiceData.abilites;
let dicePool = window.CaracteristiquesDiceData.dicePool || [];
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

function updateSlotDisplay(key) {
    const index = slotIndex[key];
    const slot = slotElement(key);
    const input = document.getElementById('input-' + key);

    if (index === null || index === undefined) {
        slot.textContent = '0';
        slot.classList.remove('filled');
        slot.draggable = false;
        input.value = 0;
        return;
    }

    slot.textContent = dicePool[index];
    slot.classList.add('filled');
    slot.draggable = true;
    input.value = dicePool[index];
}

function assignValue(key, index) {
    const previousIndex = slotIndex[key];
    if (previousIndex !== null && previousIndex !== undefined) {
        setChipUsed(previousIndex, false);
    }

    slotIndex[key] = index;
    currentAbilites[key] = dicePool[index];
    updateSlotDisplay(key);
    setChipUsed(index, true);

    checkComplete();
}

function clearSlot(key) {
    const previousIndex = slotIndex[key];
    if (previousIndex === null || previousIndex === undefined) return;

    setChipUsed(previousIndex, false);
    slotIndex[key] = null;
    currentAbilites[key] = 0;
    updateSlotDisplay(key);

    checkComplete();
}

function swapSlots(keyA, keyB) {
    if (keyA === keyB) return;
    const indexA = slotIndex[keyA];
    const indexB = slotIndex[keyB];

    slotIndex[keyA] = indexB ?? null;
    slotIndex[keyB] = indexA ?? null;
    currentAbilites[keyA] = indexB !== null && indexB !== undefined ? dicePool[indexB] : 0;
    currentAbilites[keyB] = indexA !== null && indexA !== undefined ? dicePool[indexA] : 0;

    updateSlotDisplay(keyA);
    updateSlotDisplay(keyB);
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
    const raw = event.dataTransfer.getData('text/plain');

    if (raw.startsWith('stat:')) {
        const fromKey = raw.slice(5);
        swapSlots(fromKey, slot.dataset.key);
        return;
    }

    const index = parseInt(raw, 10);
    if (isNaN(index)) return;
    assignValue(slot.dataset.key, index);
}

function onStatDragStart(event, key) {
    const index = slotIndex[key];
    if (index === null || index === undefined) {
        event.preventDefault();
        return;
    }
    event.dataTransfer.setData('text/plain', 'stat:' + key);
    event.dataTransfer.effectAllowed = 'move';
    slotElement(key).classList.add('dragging');
}

function onStatDragEnd(key) {
    const slot = slotElement(key);
    if (slot) slot.classList.remove('dragging');
}

function onPoolDragOver(event) {
    const raw = event.dataTransfer.types.includes('text/plain');
    if (!raw) return;
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

function resetAssignments() {
    statsOrder.forEach(s => clearSlot(s));
}

function checkComplete() {
    const allAssigned = statsOrder.every(s => currentAbilites[s] > 0);
    document.getElementById('btnSuivant').disabled = !allAssigned;
}

function rollDice() {
    document.getElementById('btnRoll').disabled = true;
    fetch(rollDiceUrl, {
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
                updateSlotDisplay(s);
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

// Reconstruction de l'affichage si l'on revient sur cette page après avoir
// déjà réparti un lancer précédent (bouton "Retour" depuis l'étape
// suivante). Comme deux dés peuvent donner la même valeur, on réattribue
// chaque caractéristique déjà remplie au premier jeton disponible de même
// valeur.
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
