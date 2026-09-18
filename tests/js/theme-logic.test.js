import test from "node:test";
import assert from "node:assert/strict";

import {
    STORAGE_KEY,
    THEME_DARK,
    THEME_LIGHT,
    isValidTheme,
    readStoredTheme,
    writeStoredTheme,
    systemTheme,
    resolveInitialTheme,
    toggleTheme,
} from "../../public/js/theme-logic.js";

/** Petit faux localStorage pour les tests. */
function createFakeStorage(initial = {}) {
    const store = { ...initial };
    return {
        getItem(key) {
            return Object.prototype.hasOwnProperty.call(store, key)
                ? store[key]
                : null;
        },
        setItem(key, value) {
            store[key] = String(value);
        },
        _dump: () => ({ ...store }),
    };
}

test("isValidTheme accepte uniquement 'dark' et 'light'", () => {
    assert.equal(isValidTheme(THEME_DARK), true);
    assert.equal(isValidTheme(THEME_LIGHT), true);
    assert.equal(isValidTheme("sombre"), false);
    assert.equal(isValidTheme(""), false);
    assert.equal(isValidTheme(null), false);
    assert.equal(isValidTheme(undefined), false);
    assert.equal(isValidTheme(42), false);
});

test("readStoredTheme lit une valeur valide", () => {
    const storage = createFakeStorage({ [STORAGE_KEY]: THEME_LIGHT });
    assert.equal(readStoredTheme(storage), THEME_LIGHT);
});

test("readStoredTheme renvoie null quand rien n'est stocké", () => {
    const storage = createFakeStorage();
    assert.equal(readStoredTheme(storage), null);
});

test("readStoredTheme renvoie null pour une valeur corrompue", () => {
    const storage = createFakeStorage({ [STORAGE_KEY]: "not-a-theme" });
    assert.equal(readStoredTheme(storage), null);
});

test("readStoredTheme renvoie null si le stockage est absent", () => {
    assert.equal(readStoredTheme(null), null);
    assert.equal(readStoredTheme(undefined), null);
});

test("readStoredTheme renvoie null si le stockage lève une exception", () => {
    const storage = {
        getItem() {
            throw new Error("stockage indisponible");
        },
    };
    assert.equal(readStoredTheme(storage), null);
});

test("writeStoredTheme mémorise un thème valide", () => {
    const storage = createFakeStorage();
    const ok = writeStoredTheme(storage, THEME_DARK);
    assert.equal(ok, true);
    assert.equal(storage._dump()[STORAGE_KEY], THEME_DARK);
});

test("writeStoredTheme rejette un thème invalide", () => {
    const storage = createFakeStorage();
    assert.throws(() => writeStoredTheme(storage, "bleu"), /Thème invalide/);
});

test("writeStoredTheme renvoie false si le stockage est absent", () => {
    assert.equal(writeStoredTheme(null, THEME_DARK), false);
});

test("writeStoredTheme renvoie false si le stockage lève une exception", () => {
    const storage = {
        setItem() {
            throw new Error("quota dépassé");
        },
    };
    assert.equal(writeStoredTheme(storage, THEME_LIGHT), false);
});

test("systemTheme reflète la préférence système", () => {
    assert.equal(systemTheme(true), THEME_DARK);
    assert.equal(systemTheme(false), THEME_LIGHT);
});

test("resolveInitialTheme priorise le thème mémorisé sur la préférence système", () => {
    assert.equal(resolveInitialTheme(THEME_LIGHT, true), THEME_LIGHT);
    assert.equal(resolveInitialTheme(THEME_DARK, false), THEME_DARK);
});

test("resolveInitialTheme retombe sur la préférence système si rien n'est mémorisé", () => {
    assert.equal(resolveInitialTheme(null, true), THEME_DARK);
    assert.equal(resolveInitialTheme(null, false), THEME_LIGHT);
});

test("resolveInitialTheme ignore une valeur mémorisée invalide", () => {
    assert.equal(resolveInitialTheme("corrompu", true), THEME_DARK);
});

test("toggleTheme bascule entre les deux thèmes", () => {
    assert.equal(toggleTheme(THEME_DARK), THEME_LIGHT);
    assert.equal(toggleTheme(THEME_LIGHT), THEME_DARK);
});

test("toggleTheme est involutif (appliqué deux fois, revient au thème de départ)", () => {
    assert.equal(toggleTheme(toggleTheme(THEME_DARK)), THEME_DARK);
    assert.equal(toggleTheme(toggleTheme(THEME_LIGHT)), THEME_LIGHT);
});
