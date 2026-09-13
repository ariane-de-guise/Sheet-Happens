// Étape "Race" de la création de personnage.
// Les données (window.RaceCreationData) sont injectées par race.blade.php ;
// toutes les races sont déjà chargées côté serveur avec leurs sous-races,
// donc aucun appel réseau supplémentaire n'est nécessaire pour afficher les
// sous-races ou les descriptions au clic.
const racesData = (window.RaceCreationData && window.RaceCreationData.races) || {};
window.currentSousRaces = (window.RaceCreationData && window.RaceCreationData.initialSousRaces) || [];

function selectRace(raceId, element) {
    document.querySelectorAll('#racesGrid .card-choice').forEach(el => {
        el.classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;

    document.getElementById('raceErrors').style.display = 'none';

    const race = racesData[raceId];
    showRaceDescription(race);
    renderSousRaces(race);
    updateSuivantState();
}

function showRaceDescription(race) {
    const box = document.getElementById('raceDescription');
    if (!race || !race.description) {
        box.style.display = 'none';
        return;
    }
    document.getElementById('raceDescriptionTitle').textContent = race.nom;
    document.getElementById('raceDescriptionContent').textContent = race.description;
    box.style.display = '';
}

function selectSousRace(id, element) {
    document.querySelectorAll('#sousRacesGrid .card-choice').forEach(el => {
        el.classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;

    const errBox = document.getElementById('sousRaceErrors');
    if (errBox) errBox.style.display = 'none';

    const sousRace = (window.currentSousRaces || []).find(sr => sr.id === id);
    showSousRaceDescription(sousRace);
    updateSuivantState();
}

function showSousRaceDescription(sousRace) {
    const box = document.getElementById('sousRaceDescription');
    if (!box) return;
    if (!sousRace || !sousRace.description) {
        box.style.display = 'none';
        return;
    }
    document.getElementById('sousRaceDescriptionTitle').textContent = sousRace.nom;
    document.getElementById('sousRaceDescriptionContent').textContent = sousRace.description;
    box.style.display = '';
}

function renderSousRaces(race) {
    const container = document.getElementById('sousRaceContainer');
    const sousRaces = (race && race.sous_races) || [];
    window.currentSousRaces = sousRaces;

    if (sousRaces.length === 0) {
        container.innerHTML = '';
        return;
    }

    let html = '<h3 class="title-section mb-2">Sous-race</h3>';
    html += '<div class="alert alert-error" id="sousRaceErrors" style="display:none;">';
    html += 'Vous devez choisir une sous-race avant de continuer.</div>';
    html += '<div class="grid-cards mb-4" id="sousRacesGrid">';

    sousRaces.forEach(sr => {
        html += '<div class="card-choice" onclick="selectSousRace(' + sr.id + ', this)">';
        html += '<input type="radio" name="sous_race_id" value="' + sr.id + '" required style="display: none;">';
        html += '<div class="card-title">' + sr.nom + '</div>';
        html += '</div>';
    });

    html += '</div>';
    html += '<div class="help-box mb-4" id="sousRaceDescription" style="display:none;">';
    html += '<div class="title" id="sousRaceDescriptionTitle"></div>';
    html += '<div class="content" id="sousRaceDescriptionContent"></div>';
    html += '</div>';

    container.innerHTML = html;
}

function updateSuivantState() {
    const hasRace = !!document.querySelector('#racesGrid input[type="radio"]:checked');
    const sousGrid = document.getElementById('sousRacesGrid');
    const hasSousRace = !sousGrid || !!document.querySelector('#sousRacesGrid input[type="radio"]:checked');
    document.getElementById('btnSuivant').disabled = !(hasRace && hasSousRace);
}

document.getElementById('raceForm').addEventListener('submit', function (event) {
    const hasSelection = document.querySelector('#racesGrid input[type="radio"]:checked');
    if (!hasSelection) {
        event.preventDefault();
        document.getElementById('raceErrors').style.display = 'block';
        return;
    }

    const sousGrid = document.getElementById('sousRacesGrid');
    if (sousGrid && !document.querySelector('#sousRacesGrid input[type="radio"]:checked')) {
        event.preventDefault();
        const errBox = document.getElementById('sousRaceErrors');
        if (errBox) errBox.style.display = 'block';
    }
});

updateSuivantState();
