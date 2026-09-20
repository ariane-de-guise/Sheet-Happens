/**
 * Logique pure de gestion du thème sombre / clair (aucune dépendance
 * au DOM). C'est ce fichier que couvrent les tests unitaires
 * (tests/js/theme-logic.test.js), conformément au critère « 90 % de
 * couverture de tests (logique seulement, non sur l'interface) » ;
 * l'intégration à la page (public/js/theme.js) s'appuie dessus mais
 * n'est volontairement pas comptée dans cette couverture, puisqu'il
 * s'agit d'interface (manipulation du DOM).
 */

export const STORAGE_KEY = "theme";
export const THEME_DARK = "dark";
export const THEME_LIGHT = "light";

/**
 * @param {unknown} value
 * @returns {boolean} vrai si `value` est un nom de thème valide.
 */
export function isValidTheme(value) {
    return value === THEME_DARK || value === THEME_LIGHT;
}

/**
 * Lit le thème mémorisé dans le stockage fourni (localStorage en
 * pratique). Retourne `null` si rien n'est stocké, si la valeur est
 * invalide, ou si l'accès au stockage échoue (navigation privée,
 * quota dépassé, storage désactivé, etc.).
 *
 * @param {{ getItem(key: string): string | null } | null | undefined} storage
 * @returns {"dark" | "light" | null}
 */
export function readStoredTheme(storage) {
    if (!storage) return null;
    try {
        const value = storage.getItem(STORAGE_KEY);
        return isValidTheme(value) ? value : null;
    } catch {
        return null;
    }
}

/**
 * Mémorise le thème choisi dans le stockage fourni.
 *
 * @param {{ setItem(key: string, value: string): void } | null | undefined} storage
 * @param {"dark" | "light"} theme
 * @returns {boolean} vrai si l'écriture a réussi.
 * @throws {Error} si `theme` n'est pas un thème valide.
 */
export function writeStoredTheme(storage, theme) {
    if (!isValidTheme(theme)) {
        throw new Error(`Thème invalide : "${theme}"`);
    }
    if (!storage) return false;
    try {
        storage.setItem(STORAGE_KEY, theme);
        return true;
    } catch {
        return false;
    }
}

/**
 * Détermine le thème à appliquer au chargement de la page : la
 * préférence explicitement enregistrée par l'utilisateur prévaut
 * toujours ; à défaut, le thème sombre est le défaut de
 * l'application (la préférence système du navigateur n'est
 * volontairement pas consultée ici).
 *
 * @param {"dark" | "light" | null} storedTheme
 * @returns {"dark" | "light"}
 */
export function resolveInitialTheme(storedTheme) {
    return isValidTheme(storedTheme) ? storedTheme : THEME_DARK;
}

/**
 * @param {"dark" | "light"} currentTheme
 * @returns {"dark" | "light"} l'autre thème.
 */
export function toggleTheme(currentTheme) {
    return currentTheme === THEME_DARK ? THEME_LIGHT : THEME_DARK;
}
