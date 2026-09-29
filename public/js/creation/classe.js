// Étape "Classe" de la création de personnage.
// Les données (window.DonneesCreationClasse) sont injectées par
// classe.blade.php ; toutes les classes sont déjà chargées côté serveur
// avec leurs sous-classes, donc aucun appel réseau supplémentaire n'est
// nécessaire pour afficher les sous-classes ou leurs descriptions.
// Un personnage est toujours créé au niveau 1 : seules les sous-classes
// disponibles dès ce niveau sont proposées ici.
const NIVEAU_CREATION = 1;
const donneesClasses = (window.DonneesCreationClasse && window.DonneesCreationClasse.classes) || {};
window.sousClassesCourantes = (window.DonneesCreationClasse && window.DonneesCreationClasse.sousClassesInitiales) || [];

function choisirClasse(id, element) {
    document.querySelectorAll('#classesGrille .carte-choix').forEach(el => {
        el.classList.remove('selectionne');
    });
    element.classList.add('selectionne');
    element.querySelector('input[type="radio"]').checked = true;

    document.getElementById('classeErreurs').style.display = 'none';

    const classe = donneesClasses[id];
    afficherDescriptionClasse(classe);
    afficherSousClasses(classe);
    majEtatSuivant();
}

function afficherDescriptionClasse(classe) {
    const boite = document.getElementById('classeDescription');
    if (!classe || !classe.description) {
        boite.style.display = 'none';
        return;
    }
    document.getElementById('classeDescriptionTitre').textContent = classe.nom;
    document.getElementById('classeDescriptionContenu').textContent = classe.description;
    boite.style.display = '';
}

function choisirSousClasse(id, element) {
    document.querySelectorAll('#sousClassesGrille .carte-choix').forEach(el => {
        el.classList.remove('selectionne');
    });
    element.classList.add('selectionne');
    element.querySelector('input[type="radio"]').checked = true;

    const boiteErreur = document.getElementById('sousClasseErreurs');
    if (boiteErreur) boiteErreur.style.display = 'none';

    const sousClasse = (window.sousClassesCourantes || []).find(sc => sc.id === id);
    afficherDescriptionSousClasse(sousClasse);
    majEtatSuivant();
}

function afficherDescriptionSousClasse(sousClasse) {
    const boite = document.getElementById('sousClasseDescription');
    if (!boite) return;
    if (!sousClasse || !sousClasse.description) {
        boite.style.display = 'none';
        return;
    }
    document.getElementById('sousClasseDescriptionTitre').textContent = sousClasse.nom;
    document.getElementById('sousClasseDescriptionContenu').textContent = sousClasse.description;
    boite.style.display = '';
}

function afficherSousClasses(classe) {
    const conteneur = document.getElementById('sousClasseConteneur');
    const sousClasses = (classe && classe.niveau_sous_classe <= NIVEAU_CREATION && classe.sous_classes) || [];
    window.sousClassesCourantes = sousClasses;

    if (sousClasses.length === 0) {
        conteneur.innerHTML = '';
        return;
    }

    let html = '<h3 class="titre-section marge-bas-2">Sous-classe</h3>';
    html += '<div class="alerte alerte-erreur" id="sousClasseErreurs" style="display:none;">';
    html += 'Vous devez choisir une sous-classe avant de continuer.</div>';
    html += '<div class="grille-cartes marge-bas-4" id="sousClassesGrille">';

    sousClasses.forEach(sc => {
        html += '<div class="carte-choix" onclick="choisirSousClasse(' + sc.id + ', this)">';
        html += '<input type="radio" name="sous_classe_id" value="' + sc.id + '" required style="display: none;">';
        html += '<div class="carte-titre">' + sc.nom + '</div>';
        html += '<div class="carte-desc">Niveau ' + classe.niveau_sous_classe + '</div>';
        html += '</div>';
    });

    html += '</div>';
    html += '<div class="boite-aide marge-bas-4" id="sousClasseDescription" style="display:none;">';
    html += '<div class="titre" id="sousClasseDescriptionTitre"></div>';
    html += '<div class="contenu" id="sousClasseDescriptionContenu"></div>';
    html += '</div>';

    conteneur.innerHTML = html;
}

function majEtatSuivant() {
    const aClasse = !!document.querySelector('#classesGrille input[type="radio"]:checked');
    const grilleSous = document.getElementById('sousClassesGrille');
    const aSousClasse = !grilleSous || !!document.querySelector('#sousClassesGrille input[type="radio"]:checked');
    document.getElementById('boutonSuivant').disabled = !(aClasse && aSousClasse);
}

document.getElementById('classeFormulaire').addEventListener('submit', function (evenement) {
    const aUneSelection = document.querySelector('#classesGrille input[type="radio"]:checked');
    if (!aUneSelection) {
        evenement.preventDefault();
        document.getElementById('classeErreurs').style.display = 'block';
        return;
    }

    const grilleSous = document.getElementById('sousClassesGrille');
    if (grilleSous && !document.querySelector('#sousClassesGrille input[type="radio"]:checked')) {
        evenement.preventDefault();
        const boiteErreur = document.getElementById('sousClasseErreurs');
        if (boiteErreur) boiteErreur.style.display = 'block';
    }
});

majEtatSuivant();
