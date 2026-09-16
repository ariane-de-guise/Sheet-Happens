// Étape "Classe" de la création de personnage.
// Les données (window.ClasseCreationData) sont injectées par
// classe.blade.php ; toutes les classes sont déjà chargées côté serveur
// avec leurs sous-classes, donc aucun appel réseau supplémentaire n'est
// nécessaire pour afficher les sous-classes ou leurs descriptions.
// Un personnage est toujours créé au niveau 1 : seules les sous-classes
// disponibles dès ce niveau sont proposées ici.
const NIVEAU_CREATION = 1;
const classesData = (window.ClasseCreationData && window.ClasseCreationData.classes) || {};
window.currentSousClasses = (window.ClasseCreationData && window.ClasseCreationData.initialSousClasses) || [];

function selectClasse(id, element) {
    document.querySelectorAll('#classesGrid .card-choice').forEach(el => {
        el.classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;

    document.getElementById('classeErrors').style.display = 'none';

    const classe = classesData[id];
    showClasseDescription(classe);
    renderSousClasses(classe);
    updateSuivantState();
}

function showClasseDescription(classe) {
    const box = document.getElementById('classeDescription');
    if (!classe || !classe.description) {
        box.style.display = 'none';
        return;
    }
    document.getElementById('classeDescriptionTitle').textContent = classe.nom;
    document.getElementById('classeDescriptionContent').textContent = classe.description;
    box.style.display = '';
}

function selectSousClasse(id, element) {
    document.querySelectorAll('#sousClassesGrid .card-choice').forEach(el => {
        el.classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;

    const errBox = document.getElementById('sousClasseErrors');
    if (errBox) errBox.style.display = 'none';

    const sousClasse = (window.currentSousClasses || []).find(sc => sc.id === id);
    showSousClasseDescription(sousClasse);
    updateSuivantState();
}

function showSousClasseDescription(sousClasse) {
    const box = document.getElementById('sousClasseDescription');
    if (!box) return;
    if (!sousClasse || !sousClasse.description) {
        box.style.display = 'none';
        return;
    }
    document.getElementById('sousClasseDescriptionTitle').textContent = sousClasse.nom;
    document.getElementById('sousClasseDescriptionContent').textContent = sousClasse.description;
    box.style.display = '';
}

function renderSousClasses(classe) {
    const container = document.getElementById('sousClasseContainer');
    const sousClasses = (classe && classe.niveau_sous_classe <= NIVEAU_CREATION && classe.sous_classes) || [];
    window.currentSousClasses = sousClasses;

    if (sousClasses.length === 0) {
        container.innerHTML = '';
        return;
    }

    let html = '<h3 class="title-section mb-2">Sous-classe</h3>';
    html += '<div class="alert alert-error" id="sousClasseErrors" style="display:none;">';
    html += 'Vous devez choisir une sous-classe avant de continuer.</div>';
    html += '<div class="grid-cards mb-4" id="sousClassesGrid">';

    sousClasses.forEach(sc => {
        html += '<div class="card-choice" onclick="selectSousClasse(' + sc.id + ', this)">';
        html += '<input type="radio" name="sous_classe_id" value="' + sc.id + '" required style="display: none;">';
        html += '<div class="card-title">' + sc.nom + '</div>';
        html += '<div class="card-desc">Niveau ' + classe.niveau_sous_classe + '</div>';
        html += '</div>';
    });

    html += '</div>';
    html += '<div class="help-box mb-4" id="sousClasseDescription" style="display:none;">';
    html += '<div class="title" id="sousClasseDescriptionTitle"></div>';
    html += '<div class="content" id="sousClasseDescriptionContent"></div>';
    html += '</div>';

    container.innerHTML = html;
}

function updateSuivantState() {
    const hasClasse = !!document.querySelector('#classesGrid input[type="radio"]:checked');
    const sousGrid = document.getElementById('sousClassesGrid');
    const hasSousClasse = !sousGrid || !!document.querySelector('#sousClassesGrid input[type="radio"]:checked');
    document.getElementById('btnSuivant').disabled = !(hasClasse && hasSousClasse);
}

document.getElementById('classeForm').addEventListener('submit', function (event) {
    const hasSelection = document.querySelector('#classesGrid input[type="radio"]:checked');
    if (!hasSelection) {
        event.preventDefault();
        document.getElementById('classeErrors').style.display = 'block';
        return;
    }

    const sousGrid = document.getElementById('sousClassesGrid');
    if (sousGrid && !document.querySelector('#sousClassesGrid input[type="radio"]:checked')) {
        event.preventDefault();
        const errBox = document.getElementById('sousClasseErrors');
        if (errBox) errBox.style.display = 'block';
    }
});

updateSuivantState();
