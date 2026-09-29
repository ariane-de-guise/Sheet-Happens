/**
 * Intégration du thème sombre / clair à la page.
 *
 * Toute la logique de décision (validation, lecture/écriture du
 * stockage, résolution du thème initial) vit dans le module pur
 * ./theme-logic.js, couvert par des tests unitaires. Ce fichier-ci ne
 * fait que relier cette logique au document : poser l'attribut
 * `data-theme` sur <html> (lu par les variables CSS de app.css) et
 * câbler les deux boutons de la page (soleil = clair, lune = sombre).
 * N'étant qu'une couche d'interface, il n'est volontairement pas
 * inclus dans l'objectif de couverture de tests (« logique seulement,
 * non sur l'interface »).
 *
 * Chaque bouton force explicitement son thème : cliquer sur le
 * soleil active toujours le clair, cliquer sur la lune active
 * toujours le sombre, peu importe le thème courant (ce n'est donc pas
 * un simple bouton bascule).
 *
 * Chargé en tant que module natif : <script type="module" src="...">.
 */

import {
    estThemeValide,
    lireThemeMemorise,
    ecrireThemeMemorise,
    resoudreThemeInitial,
} from "./theme-logic.js";

/**
 * @param {"sombre" | "clair"} theme
 * @param {Document} doc
 */
export function appliquerThemeAuDocument(theme, doc) {
    doc.documentElement.setAttribute("data-theme", theme);
}

/**
 * Résout et applique le thème initial de la page : la préférence
 * mémorisée si elle existe, sinon le thème sombre (défaut de
 * l'application, indépendant des préférences système du navigateur).
 *
 * @param {Window} win
 * @param {Document} doc
 * @returns {"sombre" | "clair"} le thème appliqué.
 */
export function initialiserTheme(fenetre, doc) {
    const memorise = lireThemeMemorise(fenetre.localStorage);
    const theme = resoudreThemeInitial(memorise);
    appliquerThemeAuDocument(theme, doc);
    return theme;
}

function majBoutonsTheme(boutons, theme) {
    boutons.forEach((bouton) => {
        bouton.setAttribute(
            "aria-pressed",
            String(bouton.dataset.themeChoisi === theme)
        );
    });
}

/**
 * Câble les boutons de thème (`[data-theme-choisi="clair"]` et
 * `[data-theme-choisi="sombre"]`) trouvés dans le document, s'ils
 * existent. Chaque bouton applique explicitement le thème qu'il
 * représente, le mémorise, et met à jour l'état visuel des deux
 * boutons (curseur qui glisse, `aria-pressed`).
 *
 * @param {Window} win
 * @param {Document} doc
 */
export function brancherBoutonsTheme(fenetre, doc) {
    const boutons = Array.from(doc.querySelectorAll("[data-theme-choisi]"));
    if (boutons.length === 0) return;

    let courant = doc.documentElement.getAttribute("data-theme");
    if (!estThemeValide(courant)) {
        courant = initialiserTheme(fenetre, doc);
    }
    majBoutonsTheme(boutons, courant);

    boutons.forEach((bouton) => {
        bouton.addEventListener("click", () => {
            const cible = bouton.dataset.themeChoisi;
            if (!estThemeValide(cible) || cible === courant) return;
            courant = cible;
            appliquerThemeAuDocument(courant, doc);
            ecrireThemeMemorise(fenetre.localStorage, courant);
            majBoutonsTheme(boutons, courant);
        });
    });
}

/* Auto-initialisation lorsque le fichier est chargé dans un navigateur
   (absent lors des imports depuis les tests Node). */
if (typeof window !== "undefined" && typeof document !== "undefined") {
    initialiserTheme(window, document);
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () =>
            brancherBoutonsTheme(window, document)
        );
    } else {
        brancherBoutonsTheme(window, document);
    }
}
