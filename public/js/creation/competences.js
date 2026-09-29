// Étape "Compétences" de la création de personnage.
// Les données (window.DonneesCreationCompetences) sont injectées par
// competences.blade.php.
const maxCompetences = (window.DonneesCreationCompetences && window.DonneesCreationCompetences.max) || 0;
const selectionnees = (window.DonneesCreationCompetences && window.DonneesCreationCompetences.selectionnees) || [];

function basculerCompetence(comp, element) {
    const checkbox = element.querySelector('input[type="checkbox"]');

    if (checkbox.checked) {
        const idx = selectionnees.indexOf(comp);
        if (idx > -1) selectionnees.splice(idx, 1);
    } else {
        if (selectionnees.length >= maxCompetences) return;
        selectionnees.push(comp);
    }

    checkbox.checked = !checkbox.checked;
    element.classList.toggle('selectionne');
    document.getElementById('competencesErreurs').style.display = 'none';
    majCompteur();
}

function majCompteur() {
    document.getElementById('affichageCompte').textContent = selectionnees.length + ' / ' + maxCompetences + ' sélectionnées';
}

document.getElementById('competencesFormulaire').addEventListener('submit', function (evenement) {
    if (selectionnees.length < maxCompetences) {
        evenement.preventDefault();
        document.getElementById('competencesErreurs').style.display = 'block';
    }
});

majCompteur();
