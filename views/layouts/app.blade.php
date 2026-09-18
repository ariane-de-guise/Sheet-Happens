<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sheet Happens')</title>
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
    @stack('styles')
</head>
<body>
<button type="button" class="theme-toggle-switch" data-theme-toggle role="switch" aria-checked="false" aria-label="Activer le thème sombre">
    <span class="theme-toggle-thumb" aria-hidden="true"></span>
    <span class="theme-toggle-icon theme-toggle-icon-sun" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <circle cx="12" cy="12" r="4.5"></circle>
            <path d="M12 2.5v2.2M12 19.3v2.2M2.5 12h2.2M19.3 12h2.2"></path>
        </svg>
    </span>
    <span class="theme-toggle-icon theme-toggle-icon-moon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </span>
</button>
@include('partials.auth-badge')

<div class="container">
    @yield('content')
</div>
<script type="module" src="{{ asset('js/theme.js') }}"></script>
@stack('scripts')
</body>
</html>
