<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_rmb.png') }}">
    <script>
        // Applique le thème mémorisé (ou la préférence système à défaut)
        // avant le premier affichage, pour éviter un flash visuel du
        // mauvais thème. Volontairement en ligne et synchrone : la
        // même logique, testée unitairement, vit dans
        // public/js/theme-logic.js (voir public/js/theme.js).
        (function () {
            try {
                var theme = localStorage.getItem('theme');
                if (theme !== 'dark' && theme !== 'light') {
                    theme = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
                        ? 'dark'
                        : 'light';
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inscription.css') }}">
</head>
<body style="
    background-image: url('{{ asset('images/background_montagne.webp') }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    min-height: 100vh;
">

<div class="theme-toggle-switch" role="group" aria-label="Thème de l'interface">
    <span class="theme-toggle-thumb" aria-hidden="true"></span>
    <button type="button" class="theme-toggle-icon theme-toggle-icon-sun" data-theme-set="light" aria-pressed="false" aria-label="Activer le thème clair">
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
    <button type="button" class="theme-toggle-icon theme-toggle-icon-moon" data-theme-set="dark" aria-pressed="true" aria-label="Activer le thème sombre">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </button>
</div>

<div class="auth-badge">
    @auth
        <img src="{{ asset('images/logo_rmb.png') }}" alt="Sheet Happens" class="auth-logo">
        <div class="auth-info">
            <span class="auth-email">{{ Auth::user()->courriel }}</span>
            <form action="{{ route('deconnexion') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="auth-btn-logout">Déconnexion</button>
            </form>
        </div>
    @else
        <span class="auth-status-dot dot-offline"></span>
        <div class="auth-info">
            <span style="color: var(--texte-secondaire);">Non connecté</span>
            <a href="/inscription" class="auth-link">S'inscrire</a>
        </div>
    @endauth
</div>

<main>
    <h1>Bienvenue</h1>
    <p>Accédez à votre espace ou rejoignez la plateforme en quelques secondes.</p>

    <nav>
        <a href="{{ route('connexion.create') }}"><button>Se connecter</button></a>
        <a href="{{ route('utilisateurs.create') }}"><button>Créer un compte</button></a>
    </nav>
</main>

<script type="module" src="{{ asset('js/theme.js') }}"></script>

</body>
</html>
