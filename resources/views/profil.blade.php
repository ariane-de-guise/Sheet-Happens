@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
    <div class="profile-page">
        <section class="card profile-card">
            <div class="profile-header">
                <div>
                    <p class="profile-eyebrow">Mon compte</p>
                    <h1 class="title-main mb-1">Mon profil</h1>
                    <p class="text-muted">Consultez les informations associées à votre compte.</p>
                </div>
            </div>

            <div class="profile-information">
                <div class="profile-field">
                    <span class="profile-label">Adresse courriel</span>
                    <span class="profile-value">{{ $utilisateur->courriel }}</span>
                </div>

                <div class="profile-field">
                    <span class="profile-label">Membre depuis</span>
                    <span class="profile-value">{{ $utilisateur->created_at?->format('d/m/Y') ?? 'Non disponible' }}</span>
                </div>
            </div>

            <div class="profile-actions">
                <a href="{{ route('personnages.index') }}" class="btn btn-primary">Mes personnages</a>

                <form method="POST" action="{{ route('deconnexion') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-danger">Se déconnecter</button>
                </form>
            </div>
        </section>
    </div>
@endsection
