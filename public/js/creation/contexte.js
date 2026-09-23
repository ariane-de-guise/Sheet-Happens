// Étape "Contexte" de la création de personnage.
// Les données (window.ContexteData) sont injectées par contexte.blade.php :
// les historiques (avec leur équipement de départ, leurs idéaux, défauts,
// attachements et personnalités).
const contexteData = window.ContexteData || {};
const historiquesById = {};
(contexteData.historiques || []).forEach(h => {
    historiquesById[h.id] = h;
});

// L'historique détermine l'équipement de départ : on met à jour l'aperçu
// dès que le choix change. Les outils accordés automatiquement sans choix
// (outils_fixes) font partie de l'équipement final (voir saveContexte()
// côté serveur), donc on les affiche ici aussi.
function updateEquipementPreview(historiqueId) {
    const equipementList = document.getElementById('equipementPreview');
    if (!equipementList) return;

    const historique = historiquesById[historiqueId];
    const equipement = historique
        ? (historique.equipement_depart || []).concat(historique.outils_fixes || [])
        : null;

    equipementList.innerHTML = (equipement && equipement.length)
        ? equipement.map(item => '<li>' + item + '</li>').join('')
        : '<li>Choisissez un historique pour voir votre équipement.</li>';
}

// L'outil de départ n'est jamais un choix totalement libre : il dépend de
// l'historique choisi (voir Historique::outils_disponibles /
// outils_fixes). On reconstruit donc les options du select à chaque
// changement d'historique. "preselect" ne sert qu'au chargement initial de
// la page (retour en arrière, erreur de validation qui renvoie l'ancienne
// valeur) pour resélectionner l'outil déjà choisi.
function updateOutilOptions(historiqueId, preselect) {
    const select = document.getElementById('outil_select');
    const hint = document.getElementById('outil_hint');
    if (!select) return;

    const historique = historiquesById[historiqueId];

    if (!historique) {
        select.innerHTML = '<option value="">-- Choisissez d\'abord un historique --</option>';
        select.disabled = true;
        select.required = false;
        if (hint) hint.textContent = 'Choisissez un historique pour voir les outils disponibles.';
        return;
    }

    const disponibles = historique.outils_disponibles || [];
    const fixes = historique.outils_fixes || [];

    if (disponibles.length > 0) {
        select.innerHTML = '<option value="">-- Choisir un outil --</option>' +
            disponibles.map(function (o) {
                const selectedAttr = (preselect && preselect === o) ? ' selected' : '';
                return '<option value="' + o + '"' + selectedAttr + '>' + o + '</option>';
            }).join('');
        select.disabled = false;
        select.required = true;
        hint && (hint.textContent = 'À choisir parmi ceux offerts par votre historique.');
    } else {
        select.innerHTML = '<option value="">Aucun choix pour cet historique</option>';
        select.disabled = true;
        select.required = false;
        if (hint) {
            hint.textContent = fixes.length
                ? 'Cet historique accorde directement ' + fixes.join(', ') + ' (voir Équipement ci-dessous).'
                : 'Cet historique n\'accorde aucun outil.';
        }
    }
}

// Lit l'attribut data-alignement de l'<option> actuellement sélectionnée
// dans un <select> donné (vide si rien n'est sélectionné ou si le trait ne
// porte pas d'étiquette d'alignement).
function selectedAlignement(selectId) {
    const select = document.getElementById(selectId);
    const option = select ? select.options[select.selectedIndex] : null;
    return option ? (option.dataset.alignement || '') : '';
}

// L'alignement final combine les 4 traits choisis (idéal, défaut,
// attachement, trait de personnalité) : chaque trait qui porte une
// étiquette d'alignement "vote" sur les deux axes (loi/chaos,
// bien/mal). Cette logique doit rester identique à
// CreationPersonnageController::calculateAlignement() côté serveur, qui
// fait foi (cet aperçu n'est là que pour l'affichage en temps réel).
function updateAlignementPreview() {
    const alignementList = document.getElementById('alignementPreview');
    if (!alignementList) return;

    const alignements = [
        selectedAlignement('ideal_select'),
        selectedAlignement('defaut_select'),
        selectedAlignement('attachement_select'),
        selectedAlignement('personnalite_select'),
    ];

    if (!alignements[0]) {
        // Sans idéal choisi, on n'a encore rien à afficher.
        alignementList.innerHTML = '<li>Choisissez un idéal pour déterminer l\'alignement.</li>';
        return;
    }

    let loiScore = 0;
    let bienScore = 0;

    alignements.forEach(alignement => {
        if (!alignement) return;

        if (alignement.includes('Loyal')) {
            loiScore--;
        } else if (alignement.includes('Chaotique')) {
            loiScore++;
        }

        if (alignement.includes('Bon')) {
            bienScore--;
        } else if (alignement.includes('Mauvais')) {
            bienScore++;
        }
    });

    const loi = loiScore < 0 ? 'Loyal' : (loiScore > 0 ? 'Chaotique' : 'Neutre');
    const bien = bienScore < 0 ? 'Bon' : (bienScore > 0 ? 'Mauvais' : 'Neutre');

    let resultat;
    if (loi === 'Neutre' && bien === 'Neutre') {
        resultat = 'Neutre';
    } else if (loi === 'Neutre') {
        resultat = 'Neutre ' + bien;
    } else if (bien === 'Neutre') {
        resultat = loi + ' Neutre';
    } else {
        resultat = loi + ' ' + bien;
    }

    alignementList.innerHTML = '<li>' + resultat + '</li>';
}

// Idéal, défaut, attachement et personnalité dépendent de l'historique
// choisi : chaque <option> porte un data-historique, on masque celles qui
// ne correspondent pas et on réinitialise le select si sa valeur actuelle
// n'est plus valide.
function filterByHistorique(selectId, historiqueId) {
    const select = document.getElementById(selectId);
    if (!select) return;

    let selectedStillValid = false;
    Array.from(select.options).forEach(option => {
        if (!option.value) {
            option.hidden = false;
            option.disabled = false;
            return;
        }
        const matches = String(historiqueId) !== '' && option.dataset.historique === String(historiqueId);
        option.hidden = !matches;
        option.disabled = !matches;
        if (matches && option.selected) {
            selectedStillValid = true;
        }
    });

    if (!selectedStillValid) {
        select.value = '';
    }
}

// Même présentation que pour la race/classe choisie : un encadré ("help-box")
// avec le nom de l'historique en titre et sa description en contenu,
// masqué tant qu'aucun historique n'est sélectionné (voir race.js /
// showRaceDescription()).
function updateHistoriqueDescription(historiqueId) {
    const box = document.getElementById('historique_description');
    if (!box) return;

    const historique = historiquesById[historiqueId];
    if (!historique || !historique.description) {
        box.style.display = 'none';
        return;
    }

    document.getElementById('historique_description_title').textContent = historique.nom;
    document.getElementById('historique_description_content').textContent = historique.description;
    box.style.display = '';
}

function onHistoriqueChange(historiqueId, preselectOutil) {
    filterByHistorique('ideal_select', historiqueId);
    filterByHistorique('defaut_select', historiqueId);
    filterByHistorique('attachement_select', historiqueId);
    filterByHistorique('personnalite_select', historiqueId);
    updateHistoriqueDescription(historiqueId);
    updateEquipementPreview(historiqueId);
    updateOutilOptions(historiqueId, preselectOutil);
    updateAlignementPreview();
}

function validateContextForm() {
    // Les champs obligatoires (historique, idéal, défaut, attachement,
    // personnalité, nom) portent déjà l'attribut HTML "required" : la
    // validation native du navigateur suffit ici. Le select "outil" ne
    // porte l'attribut "required" que lorsque l'historique choisi propose
    // effectivement un choix (voir updateOutilOptions()) : un select
    // désactivé n'est jamais bloquant.
    return true;
}

// Initialisation : si un historique et/ou un idéal étaient déjà
// sélectionnés (retour en arrière dans le formulaire, erreur de
// validation renvoyant les anciennes valeurs, ...), on réaffiche les
// aperçus et on ré-applique le filtrage des options correspondantes.
document.addEventListener('DOMContentLoaded', function () {
    const historiqueSelect = document.getElementById('historique_select');
    const outilSelect = document.getElementById('outil_select');
    const oldOutil = outilSelect ? outilSelect.dataset.old : '';

    if (historiqueSelect && historiqueSelect.value) {
        onHistoriqueChange(historiqueSelect.value, oldOutil);
    } else {
        updateAlignementPreview();
        updateOutilOptions('', oldOutil);
    }
});
