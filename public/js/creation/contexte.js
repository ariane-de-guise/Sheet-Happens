// Étape "Contexte" de la création de personnage.
// Les données (window.ContexteData) sont injectées par contexte.blade.php :
// l'équipement et l'alignement associés à chaque maîtrise, ainsi que les
// historiques (avec leurs idéaux, défauts, attachements et personnalités).
const contexteData = window.ContexteData || {};
const maitriseEquipement = contexteData.maitriseEquipement || {};
const maitriseAlignement = contexteData.maitriseAlignement || {};
const historiquesById = {};
(contexteData.historiques || []).forEach(h => {
    historiquesById[h.id] = h;
});

// La maîtrise détermine à la fois l'équipement de départ et l'alignement :
// on met à jour les deux aperçus dès que le choix change.
function updateMaitrisePreview() {
    const select = document.getElementById('maitriseSelect');
    const maitrise = select ? select.value : '';

    const equipementList = document.getElementById('equipementPreview');
    const equipement = maitriseEquipement[maitrise];
    if (equipementList) {
        equipementList.innerHTML = (equipement && equipement.length)
            ? equipement.map(item => '<li>' + item + '</li>').join('')
            : '<li>Choisissez une maîtrise pour voir votre équipement.</li>';
    }

    const alignementList = document.getElementById('alignementPreview');
    const alignement = maitriseAlignement[maitrise];
    if (alignementList) {
        alignementList.innerHTML = alignement
            ? '<li>' + alignement + '</li>'
            : '<li>Choisissez une maîtrise pour déterminer l\'alignement.</li>';
    }
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

function updateHistoriqueDescription(historiqueId) {
    const box = document.getElementById('historique_description');
    if (!box) return;
    const historique = historiquesById[historiqueId];
    box.textContent = historique ? (historique.description || '') : '';
}

function onHistoriqueChange(historiqueId) {
    filterByHistorique('ideal_select', historiqueId);
    filterByHistorique('defaut_select', historiqueId);
    filterByHistorique('attachement_select', historiqueId);
    filterByHistorique('personnalite_select', historiqueId);
    updateHistoriqueDescription(historiqueId);
}

function validateContextForm() {
    // Les champs obligatoires (maîtrise, historique, idéal, défaut,
    // attachement, personnalité, nom) portent déjà l'attribut HTML
    // "required" : la validation native du navigateur suffit ici.
    return true;
}

// Initialisation : si une maîtrise et/ou un historique étaient déjà
// sélectionnés (retour en arrière dans le formulaire, erreur de
// validation renvoyant les anciennes valeurs, ...), on réaffiche les
// aperçus et on ré-applique le filtrage des options correspondantes.
document.addEventListener('DOMContentLoaded', function () {
    updateMaitrisePreview();

    const historiqueSelect = document.getElementById('historique_select');
    if (historiqueSelect && historiqueSelect.value) {
        onHistoriqueChange(historiqueSelect.value);
    }
});
