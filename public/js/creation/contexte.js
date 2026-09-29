// Étape "Contexte" de la création de personnage.
// Les données (window.DonneesContexte) sont injectées par contexte.blade.php :
// les historiques (avec leur équipement de départ, leurs idéaux, défauts,
// attachements et personnalités).
const donneesContexte = window.DonneesContexte || {};
const historiquesParId = {};
(donneesContexte.historiques || []).forEach(h => {
    historiquesParId[h.id] = h;
});

// L'historique détermine l'équipement de départ : on met à jour l'aperçu
// dès que le choix change. Les outils accordés automatiquement sans choix
// (outils_fixes) font partie de l'équipement final (voir enregistrerContexte()
// côté serveur), donc on les affiche ici aussi.
function majApercuEquipement(historiqueId) {
    const listeEquipement = document.getElementById('apercuEquipement');
    if (!listeEquipement) return;

    const historique = historiquesParId[historiqueId];
    const equipement = historique
        ? (historique.equipement_depart || []).concat(historique.outils_fixes || [])
        : null;

    listeEquipement.innerHTML = (equipement && equipement.length)
        ? equipement.map(element => '<li>' + element + '</li>').join('')
        : '<li>Choisissez un historique pour voir votre équipement.</li>';
}

// L'outil de départ n'est jamais un choix totalement libre : il dépend de
// l'historique choisi (voir Historique::outils_disponibles /
// outils_fixes). On reconstruit donc les options du select à chaque
// changement d'historique. "preselection" ne sert qu'au chargement initial de
// la page (retour en arrière, erreur de validation qui renvoie l'ancienne
// valeur) pour resélectionner l'outil déjà choisi.
function majOptionsOutils(historiqueId, preselection) {
    const liste = document.getElementById('outil_liste');
    const indice = document.getElementById('outil_indice');
    if (!liste) return;

    const historique = historiquesParId[historiqueId];

    if (!historique) {
        liste.innerHTML = '<option value="">-- Choisissez d\'abord un historique --</option>';
        liste.disabled = true;
        liste.required = false;
        if (indice) indice.textContent = 'Choisissez un historique pour voir les outils disponibles.';
        return;
    }

    const disponibles = historique.outils_disponibles || [];
    const fixes = historique.outils_fixes || [];

    if (disponibles.length > 0) {
        liste.innerHTML = '<option value="">-- Choisir un outil --</option>' +
            disponibles.map(function (o) {
                const attributSelection = (preselection && preselection === o) ? ' selected' : '';
                return '<option value="' + o + '"' + attributSelection + '>' + o + '</option>';
            }).join('');
        liste.disabled = false;
        liste.required = true;
        indice && (indice.textContent = 'À choisir parmi ceux offerts par votre historique.');
    } else {
        liste.innerHTML = '<option value="">Aucun choix pour cet historique</option>';
        liste.disabled = true;
        liste.required = false;
        if (indice) {
            indice.textContent = fixes.length
                ? 'Cet historique accorde directement ' + fixes.join(', ') + ' (voir Équipement ci-dessous).'
                : 'Cet historique n\'accorde aucun outil.';
        }
    }
}

// Lit l'attribut data-alignement de l'<option> actuellement sélectionnée
// dans un <select> donné (vide si rien n'est sélectionné ou si le trait ne
// porte pas d'étiquette d'alignement).
function alignementChoisi(idListe) {
    const liste = document.getElementById(idListe);
    const option = liste ? liste.options[liste.selectedIndex] : null;
    return option ? (option.dataset.alignement || '') : '';
}

// L'alignement final combine les 4 traits choisis (idéal, défaut,
// attachement, trait de personnalité) : chaque trait qui porte une
// étiquette d'alignement "vote" sur les deux axes (loi/chaos,
// bien/mal). Cette logique doit rester identique à
// CreationPersonnageController::calculateAlignement() côté serveur, qui
// fait foi (cet aperçu n'est là que pour l'affichage en temps réel).
function majApercuAlignement() {
    const listeAlignement = document.getElementById('apercuAlignement');
    if (!listeAlignement) return;

    const alignements = [
        alignementChoisi('ideal_liste'),
        alignementChoisi('defaut_liste'),
        alignementChoisi('attachement_liste'),
        alignementChoisi('personnalite_liste'),
    ];

    if (!alignements[0]) {
        // Sans idéal choisi, on n'a encore rien à afficher.
        listeAlignement.innerHTML = '<li>Choisissez un idéal pour déterminer l\'alignement.</li>';
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

    listeAlignement.innerHTML = '<li>' + resultat + '</li>';
}

// Idéal, défaut, attachement et personnalité dépendent de l'historique
// choisi : chaque <option> porte un data-historique, on masque celles qui
// ne correspondent pas et on réinitialise le select si sa valeur actuelle
// n'est plus valide.
function filtrerParHistorique(idListe, historiqueId) {
    const liste = document.getElementById(idListe);
    if (!liste) return;

    let selectionToujoursValide = false;
    Array.from(liste.options).forEach(option => {
        if (!option.value) {
            option.hidden = false;
            option.disabled = false;
            return;
        }
        const correspond = String(historiqueId) !== '' && option.dataset.historique === String(historiqueId);
        option.hidden = !correspond;
        option.disabled = !correspond;
        if (correspond && option.selected) {
            selectionToujoursValide = true;
        }
    });

    if (!selectionToujoursValide) {
        liste.value = '';
    }
}

// Même présentation que pour la race/classe choisie : un encadré ("help-box")
// avec le nom de l'historique en titre et sa description en contenu,
// masqué tant qu'aucun historique n'est sélectionné (voir race.js /
// afficherDescriptionRace()).
function majDescriptionHistorique(historiqueId) {
    const boite = document.getElementById('historique_description');
    if (!boite) return;

    const historique = historiquesParId[historiqueId];
    if (!historique || !historique.description) {
        boite.style.display = 'none';
        return;
    }

    document.getElementById('historique_description_titre').textContent = historique.nom;
    document.getElementById('historique_description_contenu').textContent = historique.description;
    boite.style.display = '';
}

function changerHistorique(historiqueId, outilPreselectionne) {
    filtrerParHistorique('ideal_liste', historiqueId);
    filtrerParHistorique('defaut_liste', historiqueId);
    filtrerParHistorique('attachement_liste', historiqueId);
    filtrerParHistorique('personnalite_liste', historiqueId);
    majDescriptionHistorique(historiqueId);
    majApercuEquipement(historiqueId);
    majOptionsOutils(historiqueId, outilPreselectionne);
    majApercuAlignement();
}

function validerFormulaireContexte() {
    // Les champs obligatoires (historique, idéal, défaut, attachement,
    // personnalité, nom) portent déjà l'attribut HTML "required" : la
    // validation native du navigateur suffit ici. Le select "outil" ne
    // porte l'attribut "required" que lorsque l'historique choisi propose
    // effectivement un choix (voir majOptionsOutils()) : un select
    // désactivé n'est jamais bloquant.
    return true;
}

// Initialisation : si un historique et/ou un idéal étaient déjà
// sélectionnés (retour en arrière dans le formulaire, erreur de
// validation renvoyant les anciennes valeurs, ...), on réaffiche les
// aperçus et on ré-applique le filtrage des options correspondantes.
document.addEventListener('DOMContentLoaded', function () {
    const choixHistorique = document.getElementById('historique_liste');
    const choixOutil = document.getElementById('outil_liste');
    const ancienOutil = choixOutil ? choixOutil.dataset.old : '';

    if (choixHistorique && choixHistorique.value) {
        changerHistorique(choixHistorique.value, ancienOutil);
    } else {
        majApercuAlignement();
        majOptionsOutils('', ancienOutil);
    }
});
