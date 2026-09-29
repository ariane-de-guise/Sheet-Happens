// Étape "Caractéristiques" (méthode "points par défaut" / tableau
// standard) de la création de personnage. Les données
// (window.DonneesCaracteristiquesStandard) sont injectées par
// _caracteristiques_standard.blade.php.
const ordreCaracteristiques = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];
let abilitesCourantes = window.DonneesCaracteristiquesStandard.abilites;
let valeurGlissee = null;

function elementJeton(valeur) {
    return document.getElementById('jeton-' + valeur);
}

function elementEmplacement(cle) {
    return document.getElementById('stat-' + cle);
}

function marquerJetonUtilise(valeur, utilise) {
    const jeton = elementJeton(valeur);
    if (!jeton) return;
    jeton.classList.toggle('est-utilise', utilise);
    jeton.draggable = !utilise;
}

function majAffichageEmplacement(cle) {
    const valeur = abilitesCourantes[cle];
    const emplacement = elementEmplacement(cle);
    const champ = document.getElementById('champ-' + cle);

    if (!valeur || valeur <= 0) {
        emplacement.textContent = 'X';
        emplacement.classList.remove('remplie');
        emplacement.draggable = false;
        champ.value = 0;
        return;
    }

    emplacement.textContent = valeur;
    emplacement.classList.add('remplie');
    emplacement.draggable = true;
    champ.value = valeur;
}

function attribuerValeur(cle, valeur) {
    // Si la case contenait déjà une valeur, on la remet dans le lot.
    const precedent = abilitesCourantes[cle];
    if (precedent && precedent > 0) {
        marquerJetonUtilise(precedent, false);
    }

    abilitesCourantes[cle] = valeur;
    majAffichageEmplacement(cle);
    marquerJetonUtilise(valeur, true);

    verifierCompletude();
}

function viderEmplacement(cle) {
    const precedent = abilitesCourantes[cle];
    if (!precedent || precedent <= 0) return;

    marquerJetonUtilise(precedent, false);
    abilitesCourantes[cle] = 0;
    majAffichageEmplacement(cle);

    verifierCompletude();
}

function echangerEmplacements(cleA, cleB) {
    if (cleA === cleB) return;
    const valeurA = abilitesCourantes[cleA];
    const valeurB = abilitesCourantes[cleB];

    abilitesCourantes[cleA] = valeurB;
    abilitesCourantes[cleB] = valeurA;

    majAffichageEmplacement(cleA);
    majAffichageEmplacement(cleB);
    verifierCompletude();
}

function attribuerEmplacementLibreSuivant(valeur) {
    if (elementJeton(valeur).classList.contains('est-utilise')) return;
    const cleSuivante = ordreCaracteristiques.find(s => !abilitesCourantes[s] || abilitesCourantes[s] === 0);
    if (!cleSuivante) return;
    attribuerValeur(cleSuivante, valeur);
}

function debutGlisserJeton(evenement, valeur) {
    if (elementJeton(valeur).classList.contains('est-utilise')) {
        evenement.preventDefault();
        return;
    }
    valeurGlissee = valeur;
    evenement.dataTransfer.setData('text/plain', String(valeur));
    evenement.dataTransfer.effectAllowed = 'move';
    elementJeton(valeur).classList.add('en-glissement');
}

function finGlisserJeton(evenement) {
    if (valeurGlissee !== null) {
        elementJeton(valeurGlissee).classList.remove('en-glissement');
    }
    valeurGlissee = null;
}

function survolerEmplacement(evenement, emplacement) {
    evenement.preventDefault();
    emplacement.classList.add('survol-glisser');
}

function quitterEmplacement(evenement, emplacement) {
    emplacement.classList.remove('survol-glisser');
}

function deposerSurEmplacement(evenement, emplacement) {
    evenement.preventDefault();
    emplacement.classList.remove('survol-glisser');
    const brut = evenement.dataTransfer.getData('text/plain');

    if (brut.startsWith('stat:')) {
        echangerEmplacements(brut.slice(5), emplacement.dataset.key);
        return;
    }

    const valeur = parseInt(brut, 10);
    if (!valeur) return;
    attribuerValeur(emplacement.dataset.key, valeur);
}

function debutGlisserCaracteristique(evenement, cle) {
    if (!abilitesCourantes[cle] || abilitesCourantes[cle] <= 0) {
        evenement.preventDefault();
        return;
    }
    evenement.dataTransfer.setData('text/plain', 'stat:' + cle);
    evenement.dataTransfer.effectAllowed = 'move';
    elementEmplacement(cle).classList.add('en-glissement');
}

function finGlisserCaracteristique(cle) {
    const emplacement = elementEmplacement(cle);
    if (emplacement) emplacement.classList.remove('en-glissement');
}

function survolerReserve(evenement) {
    evenement.preventDefault();
    document.getElementById('reserveValeurs').classList.add('survol-glisser');
}

function quitterReserve() {
    document.getElementById('reserveValeurs').classList.remove('survol-glisser');
}

function deposerSurReserve(evenement) {
    evenement.preventDefault();
    document.getElementById('reserveValeurs').classList.remove('survol-glisser');
    const brut = evenement.dataTransfer.getData('text/plain');
    if (brut.startsWith('stat:')) {
        viderEmplacement(brut.slice(5));
    }
}

function reinitialiserStandard() {
    ordreCaracteristiques.forEach(s => viderEmplacement(s));
}

function verifierCompletude() {
    const toutAttribue = ordreCaracteristiques.every(s => abilitesCourantes[s] > 0);
    document.getElementById('boutonSuivant').disabled = !toutAttribue;
    document.getElementById('pointsDisponibles').value = toutAttribue ? '1' : '0';
}

// Initialise l'état "utilisée" des jetons déjà attribués (retour en arrière).
ordreCaracteristiques.forEach(s => {
    if (abilitesCourantes[s] > 0) marquerJetonUtilise(abilitesCourantes[s], true);
});

verifierCompletude();
