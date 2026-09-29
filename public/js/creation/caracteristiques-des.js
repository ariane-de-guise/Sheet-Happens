// Étape "Caractéristiques" (méthode "lancer de dés") de la création de
// personnage. Les données (window.DonneesCaracteristiquesDes) sont injectées
// par _caracteristiques_dice.blade.php.
const ordreCaracteristiques = ['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'];
const jetonCsrf = document.querySelector('meta[name="csrf-token"]').content;
const urlLancerDes = window.DonneesCaracteristiquesDes.urlLancer;

let abilitesCourantes = window.DonneesCaracteristiquesDes.abilites;
let reserveDes = window.DonneesCaracteristiquesDes.reserveDes || [];
// indexParEmplacement[key] = index (dans reserveDes) du jeton actuellement placé sur cette caractéristique
let indexParEmplacement = {};
ordreCaracteristiques.forEach(s => { indexParEmplacement[s] = null; });
let indexGlisse = null;

function elementJeton(index) {
    return document.getElementById('jeton-' + index);
}

function elementEmplacement(cle) {
    return document.getElementById('stat-' + cle);
}

function afficherReserve() {
    const pool = document.getElementById('reserveValeurs');
    pool.innerHTML = '';
    reserveDes.forEach((valeur, index) => {
        const jeton = document.createElement('div');
        jeton.className = 'jeton-valeur';
        jeton.id = 'jeton-' + index;
        jeton.textContent = valeur;
        jeton.draggable = true;
        jeton.setAttribute('ondragstart', 'debutGlisserJeton(event, ' + index + ')');
        jeton.setAttribute('ondragend', 'finGlisserJeton(event)');
        jeton.setAttribute('onclick', 'attribuerEmplacementLibreSuivant(' + index + ')');
        pool.appendChild(jeton);
    });
}

function marquerJetonUtilise(index, utilise) {
    const jeton = elementJeton(index);
    if (!jeton) return;
    jeton.classList.toggle('est-utilise', utilise);
    jeton.draggable = !utilise;
}

function majAffichageEmplacement(cle) {
    const index = indexParEmplacement[cle];
    const emplacement = elementEmplacement(cle);
    const champ = document.getElementById('champ-' + cle);

    if (index === null || index === undefined) {
        emplacement.textContent = '0';
        emplacement.classList.remove('remplie');
        emplacement.draggable = false;
        champ.value = 0;
        return;
    }

    emplacement.textContent = reserveDes[index];
    emplacement.classList.add('remplie');
    emplacement.draggable = true;
    champ.value = reserveDes[index];
}

function attribuerValeur(cle, index) {
    const indexPrecedent = indexParEmplacement[cle];
    if (indexPrecedent !== null && indexPrecedent !== undefined) {
        marquerJetonUtilise(indexPrecedent, false);
    }

    indexParEmplacement[cle] = index;
    abilitesCourantes[cle] = reserveDes[index];
    majAffichageEmplacement(cle);
    marquerJetonUtilise(index, true);

    verifierCompletude();
}

function viderEmplacement(cle) {
    const indexPrecedent = indexParEmplacement[cle];
    if (indexPrecedent === null || indexPrecedent === undefined) return;

    marquerJetonUtilise(indexPrecedent, false);
    indexParEmplacement[cle] = null;
    abilitesCourantes[cle] = 0;
    majAffichageEmplacement(cle);

    verifierCompletude();
}

function echangerEmplacements(cleA, cleB) {
    if (cleA === cleB) return;
    const indexA = indexParEmplacement[cleA];
    const indexB = indexParEmplacement[cleB];

    indexParEmplacement[cleA] = indexB ?? null;
    indexParEmplacement[cleB] = indexA ?? null;
    abilitesCourantes[cleA] = indexB !== null && indexB !== undefined ? reserveDes[indexB] : 0;
    abilitesCourantes[cleB] = indexA !== null && indexA !== undefined ? reserveDes[indexA] : 0;

    majAffichageEmplacement(cleA);
    majAffichageEmplacement(cleB);
    verifierCompletude();
}

function attribuerEmplacementLibreSuivant(index) {
    if (elementJeton(index).classList.contains('est-utilise')) return;
    const cleSuivante = ordreCaracteristiques.find(s => indexParEmplacement[s] === null || indexParEmplacement[s] === undefined);
    if (!cleSuivante) return;
    attribuerValeur(cleSuivante, index);
}

function debutGlisserJeton(evenement, index) {
    if (elementJeton(index).classList.contains('est-utilise')) {
        evenement.preventDefault();
        return;
    }
    indexGlisse = index;
    evenement.dataTransfer.setData('text/plain', String(index));
    evenement.dataTransfer.effectAllowed = 'move';
    elementJeton(index).classList.add('en-glissement');
}

function finGlisserJeton() {
    if (indexGlisse !== null) {
        const jeton = elementJeton(indexGlisse);
        if (jeton) jeton.classList.remove('en-glissement');
    }
    indexGlisse = null;
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
        const cleOrigine = brut.slice(5);
        echangerEmplacements(cleOrigine, emplacement.dataset.key);
        return;
    }

    const index = parseInt(brut, 10);
    if (isNaN(index)) return;
    attribuerValeur(emplacement.dataset.key, index);
}

function debutGlisserCaracteristique(evenement, cle) {
    const index = indexParEmplacement[cle];
    if (index === null || index === undefined) {
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
    const brut = evenement.dataTransfer.types.includes('text/plain');
    if (!brut) return;
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

function reinitialiserAttributions() {
    ordreCaracteristiques.forEach(s => viderEmplacement(s));
}

function verifierCompletude() {
    const toutAttribue = ordreCaracteristiques.every(s => abilitesCourantes[s] > 0);
    document.getElementById('boutonSuivant').disabled = !toutAttribue;
}

function lancerDes() {
    document.getElementById('boutonLancer').disabled = true;
    fetch(urlLancerDes, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': jetonCsrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({}),
    })
        .then(reponse => reponse.json())
        .then(donnees => {
            reserveDes = donnees.lancers;
            ordreCaracteristiques.forEach(s => {
                indexParEmplacement[s] = null;
                abilitesCourantes[s] = 0;
                majAffichageEmplacement(s);
            });
            afficherReserve();
            verifierCompletude();
            document.getElementById('boutonLancer').textContent = 'Relancer les dés';
        })
        .catch(erreur => console.error('Erreur lors du lancer de dés :', erreur))
        .finally(() => {
            document.getElementById('boutonLancer').disabled = false;
        });
}

// Reconstruction de l'affichage si l'on revient sur cette page après avoir
// déjà réparti un lancer précédent (bouton "Retour" depuis l'étape
// suivante). Comme deux dés peuvent donner la même valeur, on réattribue
// chaque caractéristique déjà remplie au premier jeton disponible de même
// valeur.
if (reserveDes.length === 6) {
    afficherReserve();
    const utilise = new Set();
    ordreCaracteristiques.forEach(cle => {
        const valeur = abilitesCourantes[cle];
        if (!valeur || valeur <= 0) return;
        const index = reserveDes.findIndex((v, i) => v === valeur && !utilise.has(i));
        if (index !== -1) {
            utilise.add(index);
            indexParEmplacement[cle] = index;
            marquerJetonUtilise(index, true);
        }
    });
}

verifierCompletude();
