<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

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

</body>
</html>
