@extends('layouts.app')

@section('titre', 'Mon profil')

@section('contenu')
    <div class="profil-page">
        <section class="carte profil-carte">
            <div class="profil-entete">
                <div>
                    <p class="profil-surtitre">Mon compte</p>
                    <h1 class="titre-principal marge-bas-1">Mon profil</h1>
                    <p class="texte-attenue">Consultez les informations associées à votre compte.</p>
                </div>
            </div>

            <div class="profil-information">
                <div class="profil-champ">
                    <span class="profil-etiquette">Adresse courriel</span>
                    <span class="profil-valeur">{{ $utilisateur->courriel }}</span>
                </div>

                <div class="profil-champ">
                    <span class="profil-etiquette">Membre depuis</span>
                    <span class="profil-valeur">{{ $utilisateur->created_at?->format('d/m/Y') ?? 'Non disponible' }}</span>
                </div>
            </div>

            <div class="profil-actions">
                <a href="{{ route('personnages.index') }}" class="bouton bouton-principal">Mes personnages</a>

                <form method="POST" action="{{ route('deconnexion') }}" class="formulaire-deconnexion">
                    @csrf
                    <button type="submit" class="bouton bouton-danger">Se déconnecter</button>
                </form>
            </div>
        </section>
    </div>
@endsection
