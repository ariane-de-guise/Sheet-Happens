// Étape "Race" de la création de personnage.
// Les données (window.DonneesCreationRace) sont injectées par race.blade.php ;
// toutes les races sont déjà chargées côté serveur avec leurs sous-races,
// donc aucun appel réseau supplémentaire n'est nécessaire pour afficher les
// sous-races ou les descriptions au clic.
const donneesRaces = (window.DonneesCreationRace && window.DonneesCreationRace.races) || {};
window.sousRacesCourantes = (window.DonneesCreationRace && window.DonneesCreationRace.sousRacesInitiales) || [];

function choisirRace(raceId, element) {
    document.querySelectorAll('#racesGrille .carte-choix').forEach(el => {
        el.classList.remove('selectionne');
    });
    element.classList.add('selectionne');
    element.querySelector('input[type="radio"]').checked = true;

    document.getElementById('raceErreurs').style.display = 'none';

    const race = donneesRaces[raceId];
    afficherDescriptionRace(race);
    afficherSousRaces(race);
    majEtatSuivant();
}

function afficherDescriptionRace(race) {
    const boite = document.getElementById('raceDescription');
    if (!race || !race.description) {
        boite.style.display = 'none';
        return;
    }
    document.getElementById('raceDescriptionTitre').textContent = race.nom;
    document.getElementById('raceDescriptionContenu').textContent = race.description;
    boite.style.display = '';
}

function choisirSousRace(id, element) {
    document.querySelectorAll('#sousRacesGrille .carte-choix').forEach(el => {
        el.classList.remove('selectionne');
    });
    element.classList.add('selectionne');
    element.querySelector('input[type="radio"]').checked = true;

    const boiteErreur = document.getElementById('sousRaceErreurs');
    if (boiteErreur) boiteErreur.style.display = 'none';

    const sousRace = (window.sousRacesCourantes || []).find(sr => sr.id === id);
    afficherDescriptionSousRace(sousRace);
    majEtatSuivant();
}

function afficherDescriptionSousRace(sousRace) {
    const boite = document.getElementById('sousRaceDescription');
    if (!boite) return;
    if (!sousRace || !sousRace.description) {
        boite.style.display = 'none';
        return;
    }
    document.getElementById('sousRaceDescriptionTitre').textContent = sousRace.nom;
    document.getElementById('sousRaceDescriptionContenu').textContent = sousRace.description;
    boite.style.display = '';
}

function afficherSousRaces(race) {
    const conteneur = document.getElementById('sousRaceConteneur');
    const sousRaces = (race && race.sous_races) || [];
    window.sousRacesCourantes = sousRaces;

    if (sousRaces.length === 0) {
        conteneur.innerHTML = '';
        return;
    }

    let html = '<h3 class="titre-section marge-bas-2">Sous-race</h3>';
    html += '<div class="alerte alerte-erreur" id="sousRaceErreurs" style="display:none;">';
    html += 'Vous devez choisir une sous-race avant de continuer.</div>';
    html += '<div class="grille-cartes marge-bas-4" id="sousRacesGrille">';

    sousRaces.forEach(sr => {
        html += '<div class="carte-choix" onclick="choisirSousRace(' + sr.id + ', this)">';
        html += '<input type="radio" name="sous_race_id" value="' + sr.id + '" required style="display: none;">';
        html += '<div class="carte-titre">' + sr.nom + '</div>';
        html += '</div>';
    });

    html += '</div>';
    html += '<div class="boite-aide marge-bas-4" id="sousRaceDescription" style="display:none;">';
    html += '<div class="titre" id="sousRaceDescriptionTitre"></div>';
    html += '<div class="contenu" id="sousRaceDescriptionContenu"></div>';
    html += '</div>';

    conteneur.innerHTML = html;
}

function majEtatSuivant() {
    const aRace = !!document.querySelector('#racesGrille input[type="radio"]:checked');
    const grilleSous = document.getElementById('sousRacesGrille');
    const aSousRace = !grilleSous || !!document.querySelector('#sousRacesGrille input[type="radio"]:checked');
    document.getElementById('boutonSuivant').disabled = !(aRace && aSousRace);
}

document.getElementById('raceFormulaire').addEventListener('submit', function (evenement) {
    const aUneSelection = document.querySelector('#racesGrille input[type="radio"]:checked');
    if (!aUneSelection) {
        evenement.preventDefault();
        document.getElementById('raceErreurs').style.display = 'block';
        return;
    }

    const grilleSous = document.getElementById('sousRacesGrille');
    if (grilleSous && !document.querySelector('#sousRacesGrille input[type="radio"]:checked')) {
        evenement.preventDefault();
        const boiteErreur = document.getElementById('sousRaceErreurs');
        if (boiteErreur) boiteErreur.style.display = 'block';
    }
});

majEtatSuivant();
