<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_rmb.png') }}">
    <script>
        // Applique le thème mémorisé, ou le thème sombre par défaut
        // avant le premier affichage, pour éviter un flash visuel du
        // mauvais thème. Volontairement en ligne et synchrone : la
        // même logique, testée unitairement, vit dans
        // public/js/theme-logic.js (voir public/js/theme.js).
        (function () {
            try {
                var theme = localStorage.getItem('theme');
                if (theme !== 'sombre' && theme !== 'clair') {
                    // Le thème sombre est le défaut de l'application ;
                    // on ne consulte pas la préférence système.
                    theme = 'sombre';
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'sombre');
            }
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

<div class="theme-bascule-interrupteur" role="group" aria-label="Thème de l'interface">
    <span class="theme-bascule-curseur" aria-hidden="true"></span>
    <button type="button" class="theme-bascule-icone theme-bascule-icone-soleil" data-theme-choisi="clair" aria-pressed="false" aria-label="Activer le thème clair">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="4"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>
    </button>
    <button type="button" class="theme-bascule-icone theme-bascule-icone-lune" data-theme-choisi="sombre" aria-pressed="true" aria-label="Activer le thème sombre">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </button>
</div>


<main>
    <h1>Création de compte</h1>

    <form action="{{ route('utilisateurs.store') }}" method="POST">
        @csrf
        <div>
            <label for="courriel">Adresse courriel :</label>
            <input
                type="email"
                id="courriel"
                name="courriel"
                value="{{ old('courriel') }}"
                required
                autocomplete="email"
                placeholder="exemple@domaine.com"
            >
            @error('courriel')
            <span style="color: var(--texte-negatif); font-size: 0.9em; display: block; margin-top: 4px;">
                {{ $message }}
            </span>
            @enderror
        </div>

        <br>

        <div>
            <label for="mot_de_passe">Mot de passe :</label>
            <input
                type="password"
                id="mot_de_passe"
                name="mot_de_passe"
                required
                minlength="8"
                autocomplete="new-password"
            >
        </div>

        <br>

        <div>
            <label for="confirmation_mot_de_passe">Confirmer le mot de passe :</label>
            <input
                type="password"
                id="confirmation_mot_de_passe"
                name="confirmation_mot_de_passe"
                required
                minlength="8"
                autocomplete="new-password"
            >
            @error('mot_de_passe')
            <span style="color: var(--texte-negatif); font-size: 0.9em; display: block; margin-top: 4px;">
                Les mots de passes inscrits ne sont pas correspondants.
            </span>
            @enderror
        </div>

        <br>

        <button type="submit">S'inscrire</button>
    </form>
</main>

<script type="module" src="{{ asset('js/theme.js') }}"></script>

</body>
</html>
