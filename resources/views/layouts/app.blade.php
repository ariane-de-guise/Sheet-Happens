<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sheet Happens')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inscription.css') }}">
    @stack('styles')
</head>
<body>
    @include('partials.auth-badge')

    <div class="container">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
