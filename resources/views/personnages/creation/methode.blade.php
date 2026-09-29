@extends('layouts.app')

@section('titre', 'Créer un personnage - Méthode')

@section('contenu')
    <div class="carte" style="min-height: 70vh; display: flex; flex-direction: column; justify-content: center;">
        <div class="texte-centre marge-bas-3">
            <span class="texte-or">Créer un personnage</span>
        </div>

        <div class="barre-progression">
            <div class="ligne-progression">
                <div class="remplissage-progression" style="width: 40%;"></div>
            </div>
            <div class="etape-progression terminee">1</div>
            <div class="etape-progression terminee">2</div>
            <div class="etape-progression active">3</div>
            <div class="etape-progression">4</div>
            <div class="etape-progression">5</div>
            <div class="etape-progression">6</div>
        </div>

        <h2 class="titre-principal texte-centre marge-bas-4">Veuillez sélectionnez comment vous voulez choisir vos caractéristiques</h2>

        <form action="{{ route('creation.methode.enregistrer') }}" method="POST">
            @csrf
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
                <button type="submit" name="methode" value="points" class="bouton bouton-option">
                    Inscrivez vos points à répartir
                </button>
                <button type="submit" name="methode" value="des" class="bouton bouton-option">
                    Lancée de dé
                </button>
                <button type="submit" name="methode" value="standard" class="bouton bouton-option">
                    Points par défaut
                </button>
            </div>

            <div class="bouton-groupe-ligne">
                <a href="{{ route('creation.classe') }}" class="bouton bouton-secondaire">Retour</a>
                <div></div>
            </div>
        </form>
    </div>
@endsection
