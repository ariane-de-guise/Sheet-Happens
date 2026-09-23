<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_rmb.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

<main>
    <h1>Connexion</h1>

    @error('connexion')
    <p style="color: red; font-size: 0.9em; margin-top: -1rem; margin-bottom: 1.25rem;">
        {{ $message }}
    </p>
    @enderror

    <form action="{{ route('connexion.store') }}" method="POST">
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
            <span style="color: red; font-size: 0.9em; display: block; margin-top: 4px;">
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
                autocomplete="current-password"
            >
            @error('mot_de_passe')
            <span style="color: red; font-size: 0.9em; display: block; margin-top: 4px;">
                {{ $message }}
            </span>
            @enderror
        </div>

        <br>

        <button type="submit">Se connecter</button>
    </form>

    <p style="margin-top: 1.5rem; text-align: center; color: var(--texte-secondaire);">
        Pas encore de compte ? <a href="{{ route('utilisateurs.create') }}">Créez-en un</a>
    </p>
</main>

</body>
</html>
