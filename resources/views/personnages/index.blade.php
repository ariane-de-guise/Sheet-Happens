@extends('layouts.app')

@section('titre', 'Mes personnages')

@section('contenu')
    <div class="carte">
        <h1 class="titre-principal texte-centre marge-bas-4">Mes personnages</h1>

        @if(session('erreur'))
            <div class="alerte alerte-erreur">{{ session('erreur') }}</div>
        @endif

        @if($personnages->isEmpty())
            <p class="texte-attenue texte-centre marge-bas-4">Vous n'avez pas encore de personnage.</p>
        @else
            <div class="grille-cartes marge-bas-4">
                @foreach($personnages as $p)
                    <div class="carte-choix" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <a href="{{ route('personnages.show', $p) }}" style="text-decoration: none; color: inherit;">
                            <div class="carte-titre">{{ $p->nom }}</div>
                            <div class="carte-desc">{{ $p->race->nom }} {{ $p->classe->nom }}</div>
                        </a>
                        <form id="formulaire-supprimer-{{ $p->id }}" action="{{ route('personnages.destroy', $p) }}" method="POST" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="bouton bouton-danger bouton-petit" style="width: 100%;"
                                    onclick="ouvrirModalSuppression('formulaire-supprimer-{{ $p->id }}', '{{ addslashes($p->nom) }}')">
                                Supprimer
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="texte-centre">
            <p class="texte-attenue marge-bas-2">{{ $personnages->count() }} / {{ \App\Models\Personnage::MAX_PAR_UTILISATEUR }} personnages</p>
            @if($personnages->count() >= \App\Models\Personnage::MAX_PAR_UTILISATEUR)
                <button type="button" class="bouton bouton-principal marge-bas-2" disabled title="Supprimez un personnage pour en créer un nouveau">
                    Limite de personnages atteinte
                </button>
            @else
                <a href="{{ route('creation.race') }}" class="bouton bouton-principal marge-bas-2">Créez un personnage</a>
            @endif
        </div>
    </div>

    @include('partials.modal-suppression-personnage')
@endsection
