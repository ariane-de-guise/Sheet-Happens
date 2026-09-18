/**
 * Intégration du thème sombre / clair à la page.
 *
 * Toute la logique de décision (validation, lecture/écriture du
 * stockage, résolution du thème initial, bascule) vit dans le module
 * pur ./theme-logic.js, couvert par des tests unitaires. Ce fichier-ci
 * ne fait que relier cette logique au document : poser l'attribut
 * `data-theme` sur <html> (lu par les variables CSS de app.css) et
 * câbler le bouton de bascule. N'étant qu'une couche d'interface,
 * il n'est volontairement pas inclus dans l'objectif de couverture
 * de tests (« logique seulement, non sur l'interface »).
 *
 * Chargé en tant que module natif : <script type="module" src="...">.
 */

import {
    isValidTheme,
    readStoredTheme,
    writeStoredTheme,
    resolveInitialTheme,
    toggleTheme,
} from "./theme-logic.js";

/**
 * @param {"dark" | "light"} theme
 * @param {Document} doc
 */
export function applyThemeToDocument(theme, doc) {
    doc.documentElement.setAttribute("data-theme", theme);
}

function prefersDarkScheme(win) {
    return Boolean(
        win.matchMedia && win.matchMedia("(prefers-color-scheme: dark)").matches
    );
}

/**
 * Résout et applique le thème initial de la page.
 *
 * @param {Window} win
 * @param {Document} doc
 * @returns {"dark" | "light"} le thème appliqué.
 */
export function initTheme(win, doc) {
    const stored = readStoredTheme(win.localStorage);
    const theme = resolveInitialTheme(stored, prefersDarkScheme(win));
    applyThemeToDocument(theme, doc);
    return theme;
}

function updateToggleButton(button, theme) {
    const isDark = theme === "dark";
    // role="switch" : aria-checked="true" représente ici « thème
    // sombre actif ». Le déplacement visuel du curseur, lui, est
    // entièrement piloté par CSS via l'attribut data-theme posé sur
    // <html> (voir app.css) — cette fonction ne gère que la
    // sémantique d'accessibilité, jamais le style ou la position.
    button.setAttribute("aria-checked", String(isDark));
    button.setAttribute(
        "aria-label",
        isDark ? "Activer le thème clair" : "Activer le thème sombre"
    );
}

/**
 * Câble le bouton de bascule de thème (`[data-theme-toggle]`) trouvé
 * dans le document, s'il existe. Chaque clic bascule le thème,
 * l'applique au document, le mémorise, et met à jour l'état visuel
 * du bouton (icône, libellé accessible).
 *
 * @param {Window} win
 * @param {Document} doc
 */
export function setupThemeToggle(win, doc) {
    const button = doc.querySelector("[data-theme-toggle]");
    if (!button) return;

    let current = doc.documentElement.getAttribute("data-theme");
    if (!isValidTheme(current)) {
        current = initTheme(win, doc);
    }
    updateToggleButton(button, current);

    button.addEventListener("click", () => {
        current = toggleTheme(current);
        applyThemeToDocument(current, doc);
        writeStoredTheme(win.localStorage, current);
        updateToggleButton(button, current);
    });
}

/* Auto-initialisation lorsque le fichier est chargé dans un navigateur
   (absent lors des imports depuis les tests Node). */
if (typeof window !== "undefined" && typeof document !== "undefined") {
    initTheme(window, document);
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () =>
            setupThemeToggle(window, document)
        );
    } else {
        setupThemeToggle(window, document);
    }
}
