/**
 * Logique pure de gestion du thème sombre / clair (aucune dépendance
 * au DOM). C'est ce fichier que couvrent les tests unitaires
 * (tests/js/theme-logic.test.js), conformément au critère « 90 % de
 * couverture de tests (logique seulement, non sur l'interface) » ;
 * l'intégration à la page (public/js/theme.js) s'appuie dessus mais
 * n'est volontairement pas comptée dans cette couverture, puisqu'il
 * s'agit d'interface (manipulation du DOM).
 */

export const CLE_STOCKAGE = "theme";
export const THEME_SOMBRE = "sombre";
export const THEME_CLAIR = "clair";

/**
 * @param {unknown} value
 * @returns {boolean} vrai si `value` est un nom de thème valide.
 */
export function estThemeValide(valeur) {
    return valeur === THEME_SOMBRE || valeur === THEME_CLAIR;
}

/**
 * Lit le thème mémorisé dans le stockage fourni (localStorage en
 * pratique). Retourne `null` si rien n'est stocké, si la valeur est
 * invalide, ou si l'accès au stockage échoue (navigation privée,
 * quota dépassé, storage désactivé, etc.).
 *
 * @param {{ getItem(key: string): string | null } | null | undefined} storage
 * @returns {"sombre" | "clair" | null}
 */
export function lireThemeMemorise(stockage) {
    if (!stockage) return null;
    try {
        const valeur = stockage.getItem(CLE_STOCKAGE);
        return estThemeValide(valeur) ? valeur : null;
    } catch {
        return null;
    }
}

/**
 * Mémorise le thème choisi dans le stockage fourni.
 *
 * @param {{ setItem(key: string, value: string): void } | null | undefined} storage
 * @param {"sombre" | "clair"} theme
 * @returns {boolean} vrai si l'écriture a réussi.
 * @throws {Error} si `theme` n'est pas un thème valide.
 */
export function ecrireThemeMemorise(stockage, theme) {
    if (!estThemeValide(theme)) {
        throw new Error(`Thème invalide : "${theme}"`);
    }
    if (!stockage) return false;
    try {
        stockage.setItem(CLE_STOCKAGE, theme);
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
 * @param {"sombre" | "clair" | null} themeMemorise
 * @returns {"sombre" | "clair"}
 */
export function resoudreThemeInitial(themeMemorise) {
    return estThemeValide(themeMemorise) ? themeMemorise : THEME_SOMBRE;
}

/**
 * @param {"sombre" | "clair"} themeCourant
 * @returns {"sombre" | "clair"} l'autre thème.
 */
export function basculerTheme(themeCourant) {
    return themeCourant === THEME_SOMBRE ? THEME_CLAIR : THEME_SOMBRE;
}
