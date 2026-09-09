<<<<<<< HEAD
@extends('layouts.app')

@section('title', 'Sheet Happens')

@section('content')
<div class="card text-center" style="min-height: 80vh; display: flex; flex-direction: column; justify-content: center;">
    <h1 class="title-main">Sheet Happens</h1>
    <p class="subtitle">créez et gérez vos personnages de D&amp;D</p>

    <div style="display: flex; flex-direction: column; gap: 1rem; max-width: 300px; margin: 0 auto;">
        <a href="{{ route('creation.race') }}" class="btn btn-primary">Créez un personnage</a>
        <a href="{{ route('personnages.index') }}" class="btn btn-secondary">Mes personnages</a>
    </div>
</div>
@endsection
=======
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
    @auth
        <span class="auth-status-dot dot-online"></span>
        <div class="auth-info">
            <span class="auth-email">{{ Auth::user()->courriel }}</span>
            <span class="auth-id">ID: {{ Auth::id() }}</span>
        </div>
    @else
        <span class="auth-status-dot dot-offline"></span>
        <div class="auth-info">
            <span style="color: #94a3b8;">Non connecté</span>
            <a href="/inscription" class="auth-link">S'inscrire</a>
        </div>
    @endauth
</div>

<main>
    <h1>Bienvenue</h1>
    <p>Accédez à votre espace ou rejoignez la plateforme en quelques secondes.</p>

    <nav>
        <a href="/connexion"><button>Se connecter</button></a>
        <a href="/inscription"><button>Créer un compte</button></a>
    </nav>
</main>

</body>
</html>
>>>>>>> develop
