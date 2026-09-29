@extends('layouts.app')

@section('title', 'Créer un personnage - Classe')

@section('content')
    <div class="carte">
        <div class="texte-centre marge-bas-3">
            <span class="texte-or">Créer un personnage</span>
        </div>

        <div class="barre-progression">
            <div class="ligne-progression">
                <div class="remplissage-progression" style="width: 20%;"></div>
            </div>
            <div class="etape-progression terminee">1</div>
            <div class="etape-progression active">2</div>
            <div class="etape-progression">3</div>
            <div class="etape-progression">4</div>
            <div class="etape-progression">5</div>
            <div class="etape-progression">6</div>
        </div>

        <h2 class="titre-section texte-centre">Choisissez votre classe</h2>

        @if(session('erreur'))
            <div class="alerte alerte-erreur">{{ session('erreur') }}</div>
        @endif

        @if($errors->any())
            <div class="alerte alerte-erreur" id="classeErreurs">
                @foreach($errors->all() as $erreur)
                    {{ $erreur }}<br>
                @endforeach
            </div>
        @else
            <div class="alerte alerte-erreur" id="classeErreurs" style="display: none;">
                Vous devez choisir une classe avant de continuer.
            </div>
        @endif

        @php
            $classeChoisieModele = $classeChoisie ? $classes->find($classeChoisie) : null;
            // Une sous-classe ne se choisit qu'au niveau indiqué par la classe ;
            // à la création, le personnage est toujours de niveau 1.
            $sousClassesDisponibles = $classeChoisieModele && $classeChoisieModele->niveau_sous_classe <= 1
                ? $classeChoisieModele->sousClasses
                : collect();
            $sousClasseChoisieModele = $sousClassesDisponibles->firstWhere('id', $sousClasseChoisie);
        @endphp

        <form action="{{ route('creation.classe.enregistrer') }}" method="POST" id="classeFormulaire">
            @csrf
            <div class="grille-cartes marge-bas-4" id="classesGrille">
                @foreach($classes as $classe)
                    <div class="carte-choix {{ $classeChoisie == $classe->id ? 'selectionne' : '' }}"
                         onclick="choisirClasse({{ $classe->id }}, this)">
                        <input type="radio" name="classe_id" value="{{ $classe->id }}"
                               {{ $classeChoisie == $classe->id ? 'checked' : '' }}
                               required
                               style="display: none;">
                        <div class="carte-titre">{{ $classe->nom }}</div>
                        @if($classe->de_de_vie)
                            <div class="carte-desc">DV: {{ $classe->de_de_vie }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="boite-aide marge-bas-4" id="classeDescription" style="{{ $classeChoisieModele && $classeChoisieModele->description ? '' : 'display:none;' }}">
                <div class="titre" id="classeDescriptionTitre">{{ $classeChoisieModele->nom ?? '' }}</div>
                <div class="contenu" id="classeDescriptionContenu">{{ $classeChoisieModele->description ?? '' }}</div>
            </div>

            <div id="sousClasseConteneur">
                @if($sousClassesDisponibles->count() > 0)
                    <h3 class="titre-section marge-bas-2">Sous-classe</h3>
                    <div class="alerte alerte-erreur" id="sousClasseErreurs" style="display: none;">
                        Vous devez choisir une sous-classe avant de continuer.
                    </div>
                    <div class="grille-cartes marge-bas-4" id="sousClassesGrille">
                        @foreach($sousClassesDisponibles as $sousClasse)
                            <div class="carte-choix {{ $sousClasseChoisie == $sousClasse->id ? 'selectionne' : '' }}"
                                 onclick="choisirSousClasse({{ $sousClasse->id }}, this)">
                                <input type="radio" name="sous_classe_id" value="{{ $sousClasse->id }}"
                                       {{ $sousClasseChoisie == $sousClasse->id ? 'checked' : '' }}
                                       required
                                       style="display: none;">
                                <div class="carte-titre">{{ $sousClasse->nom }}</div>
                                <div class="carte-desc">Niveau {{ $classeChoisieModele->niveau_sous_classe }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="boite-aide marge-bas-4" id="sousClasseDescription" style="{{ $sousClasseChoisieModele && $sousClasseChoisieModele->description ? '' : 'display:none;' }}">
                        <div class="titre" id="sousClasseDescriptionTitre">{{ $sousClasseChoisieModele->nom ?? '' }}</div>
                        <div class="contenu" id="sousClasseDescriptionContenu">{{ $sousClasseChoisieModele->description ?? '' }}</div>
                    </div>
                @endif
            </div>

            <div class="bouton-groupe-ligne">
                <a href="{{ route('creation.race') }}" class="bouton bouton-secondaire">Retour</a>
                <button type="submit" class="bouton bouton-principal" id="boutonSuivant">Suivant</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Données injectées par le serveur pour cette page ; toute la logique
            // vit dans un fichier séparé (public/js/creation/classe.js).
            window.DonneesCreationClasse = {
                classes: @json($classes->keyBy('id')),
                sousClassesInitiales: @json($sousClassesDisponibles),
            };
        </script>
        <script src="{{ asset('js/creation/classe.js') }}"></script>
    @endpush
@endsection
