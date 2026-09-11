<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
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

<div class="auth-badge">
    <span class="auth-status-dot dot-offline"></span>
    <div class="auth-info">
        <span style="color: #94a3b8;">Non connecté</span>
        <a href="{{ route('utilisateurs.create') }}" class="auth-link">S'inscrire</a>
    </div>
</div>

<main>
    <h1>Bienvenue</h1>
    <p>Accédez à votre espace ou rejoignez la plateforme en quelques secondes.</p>

    <nav>
        <a href="{{ route('connexion.create') }}"><button>Se connecter</button></a>
        <a href="{{ route('utilisateurs.create') }}"><button>Créer un compte</button></a>
    </nav>
</main>

</body>
</html>
