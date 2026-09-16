// Étape "Compétences" de la création de personnage.
// Les données (window.CompetencesCreationData) sont injectées par
// competences.blade.php.
const maxCompetences = (window.CompetencesCreationData && window.CompetencesCreationData.max) || 0;
const selected = (window.CompetencesCreationData && window.CompetencesCreationData.selected) || [];

function toggleCompetence(comp, element) {
    const checkbox = element.querySelector('input[type="checkbox"]');

    if (checkbox.checked) {
        const idx = selected.indexOf(comp);
        if (idx > -1) selected.splice(idx, 1);
    } else {
        if (selected.length >= maxCompetences) return;
        selected.push(comp);
    }

    checkbox.checked = !checkbox.checked;
    element.classList.toggle('selected');
    document.getElementById('competencesErrors').style.display = 'none';
    updateCount();
}

function updateCount() {
    document.getElementById('countDisplay').textContent = selected.length + ' / ' + maxCompetences + ' sélectionnées';
}

document.getElementById('competencesForm').addEventListener('submit', function (event) {
    if (selected.length < maxCompetences) {
        event.preventDefault();
        document.getElementById('competencesErrors').style.display = 'block';
    }
});

updateCount();
