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
    isValidTheme,
    readStoredTheme,
    writeStoredTheme,
    resolveInitialTheme,
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

function updateToggleButtons(buttons, theme) {
    buttons.forEach((button) => {
        button.setAttribute(
            "aria-pressed",
            String(button.dataset.themeSet === theme)
        );
    });
}

/**
 * Câble les boutons de thème (`[data-theme-set="light"]` et
 * `[data-theme-set="dark"]`) trouvés dans le document, s'ils
 * existent. Chaque bouton applique explicitement le thème qu'il
 * représente, le mémorise, et met à jour l'état visuel des deux
 * boutons (curseur qui glisse, `aria-pressed`).
 *
 * @param {Window} win
 * @param {Document} doc
 */
export function setupThemeToggle(win, doc) {
    const buttons = Array.from(doc.querySelectorAll("[data-theme-set]"));
    if (buttons.length === 0) return;

    let current = doc.documentElement.getAttribute("data-theme");
    if (!isValidTheme(current)) {
        current = initTheme(win, doc);
    }
    updateToggleButtons(buttons, current);

    buttons.forEach((button) => {
        button.addEventListener("click", () => {
            const target = button.dataset.themeSet;
            if (!isValidTheme(target) || target === current) return;
            current = target;
            applyThemeToDocument(current, doc);
            writeStoredTheme(win.localStorage, current);
            updateToggleButtons(buttons, current);
        });
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
