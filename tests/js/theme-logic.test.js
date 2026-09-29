import test from "node:test";
import assert from "node:assert/strict";

import {
    CLE_STOCKAGE,
    THEME_SOMBRE,
    THEME_CLAIR,
    estThemeValide,
    lireThemeMemorise,
    ecrireThemeMemorise,
    resoudreThemeInitial,
    basculerTheme,
} from "../../public/js/theme-logic.js";

/** Petit faux localStorage pour les tests. */
function createFakeStorage(initial = {}) {
    const reserve = { ...initial };
    return {
        getItem(cle) {
            return Object.prototype.hasOwnProperty.call(reserve, cle)
                ? reserve[cle]
                : null;
        },
        setItem(cle, valeur) {
            reserve[cle] = String(valeur);
        },
        _dump: () => ({ ...reserve }),
    };
}

test("estThemeValide accepte uniquement 'sombre' et 'clair'", () => {
    assert.equal(estThemeValide(THEME_SOMBRE), true);
    assert.equal(estThemeValide(THEME_CLAIR), true);
    assert.equal(estThemeValide("dark"), false);
    assert.equal(estThemeValide(""), false);
    assert.equal(estThemeValide(null), false);
    assert.equal(estThemeValide(undefined), false);
    assert.equal(estThemeValide(42), false);
});

test("lireThemeMemorise lit une valeur valide", () => {
    const stockage = createFakeStorage({ [CLE_STOCKAGE]: THEME_CLAIR });
    assert.equal(lireThemeMemorise(stockage), THEME_CLAIR);
});

test("lireThemeMemorise renvoie null quand rien n'est stocké", () => {
    const stockage = createFakeStorage();
    assert.equal(lireThemeMemorise(stockage), null);
});

test("lireThemeMemorise renvoie null pour une valeur corrompue", () => {
    const stockage = createFakeStorage({ [CLE_STOCKAGE]: "not-a-theme" });
    assert.equal(lireThemeMemorise(stockage), null);
});

test("lireThemeMemorise renvoie null si le stockage est absent", () => {
    assert.equal(lireThemeMemorise(null), null);
    assert.equal(lireThemeMemorise(undefined), null);
});

test("lireThemeMemorise renvoie null si le stockage lève une exception", () => {
    const stockage = {
        getItem() {
            throw new Error("stockage indisponible");
        },
    };
    assert.equal(lireThemeMemorise(stockage), null);
});

test("ecrireThemeMemorise mémorise un thème valide", () => {
    const stockage = createFakeStorage();
    const ok = ecrireThemeMemorise(stockage, THEME_SOMBRE);
    assert.equal(ok, true);
    assert.equal(stockage._dump()[CLE_STOCKAGE], THEME_SOMBRE);
});

test("ecrireThemeMemorise rejette un thème invalide", () => {
    const stockage = createFakeStorage();
    assert.throws(() => ecrireThemeMemorise(stockage, "bleu"), /Thème invalide/);
});

test("ecrireThemeMemorise renvoie false si le stockage est absent", () => {
    assert.equal(ecrireThemeMemorise(null, THEME_SOMBRE), false);
});

test("ecrireThemeMemorise renvoie false si le stockage lève une exception", () => {
    const stockage = {
        setItem() {
            throw new Error("quota dépassé");
        },
    };
    assert.equal(ecrireThemeMemorise(stockage, THEME_CLAIR), false);
});

test("resoudreThemeInitial respecte le thème mémorisé, qu'il soit clair ou sombre", () => {
    assert.equal(resoudreThemeInitial(THEME_CLAIR), THEME_CLAIR);
    assert.equal(resoudreThemeInitial(THEME_SOMBRE), THEME_SOMBRE);
});

test("resoudreThemeInitial retombe sur le thème sombre par défaut si rien n'est mémorisé", () => {
    assert.equal(resoudreThemeInitial(null), THEME_SOMBRE);
    assert.equal(resoudreThemeInitial(undefined), THEME_SOMBRE);
});

test("resoudreThemeInitial retombe sur le thème sombre si la valeur mémorisée est invalide", () => {
    assert.equal(resoudreThemeInitial("corrompu"), THEME_SOMBRE);
});

test("basculerTheme bascule entre les deux thèmes", () => {
    assert.equal(basculerTheme(THEME_SOMBRE), THEME_CLAIR);
    assert.equal(basculerTheme(THEME_CLAIR), THEME_SOMBRE);
});

test("basculerTheme est involutif (appliqué deux fois, revient au thème de départ)", () => {
    assert.equal(basculerTheme(basculerTheme(THEME_SOMBRE)), THEME_SOMBRE);
    assert.equal(basculerTheme(basculerTheme(THEME_CLAIR)), THEME_CLAIR);
});
