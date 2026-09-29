<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sheet Happens')</title>
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
    <link rel="stylesheet" href="{{ asset('css/inscription.css') }}">
    @stack('styles')
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
@include('partials.auth-badge')

<div class="conteneur">
    @yield('content')
</div>

@include('partials.popup-succes')

<script type="module" src="{{ asset('js/theme.js') }}"></script>
@stack('scripts')
</body>
</html>
