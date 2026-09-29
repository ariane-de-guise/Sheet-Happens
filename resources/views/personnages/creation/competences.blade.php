@extends('layouts.app')

@section('titre', 'Créer un personnage - Compétences')

@section('contenu')
    <div class="carte">
        <div class="texte-centre marge-bas-3">
            <span class="texte-or">Créer un personnage</span>
        </div>

        <div class="barre-progression">
            <div class="ligne-progression">
                <div class="remplissage-progression" style="width: 80%;"></div>
            </div>
            <div class="etape-progression terminee">1</div>
            <div class="etape-progression terminee">2</div>
            <div class="etape-progression terminee">3</div>
            <div class="etape-progression terminee">4</div>
            <div class="etape-progression active">5</div>
            <div class="etape-progression">6</div>
        </div>

        <h2 class="titre-section texte-centre">Compétences</h2>
        <p class="texte-attenue texte-centre marge-bas-3">
            Choisissez {{ $nombreCompetences }} compétences{{ $classe ? ' parmi celles offertes par votre classe (' . $classe->nom . ')' : '' }}
        </p>

        @if(session('erreur'))
            <div class="alerte alerte-erreur">{{ session('erreur') }}</div>
        @endif

        @if($errors->any())
            <div class="alerte alerte-erreur" id="competencesErreurs">
                @foreach($errors->all() as $erreur)
                    {{ $erreur }}<br>
                @endforeach
            </div>
        @else
            <div class="alerte alerte-erreur" id="competencesErreurs" style="display: none;">
                Vous devez choisir {{ $nombreCompetences }} compétences avant de continuer.
            </div>
        @endif

        <form action="{{ route('creation.competences.enregistrer') }}" method="POST" id="competencesFormulaire">
            @csrf
            <div class="grille-cartes marge-bas-4">
                @foreach($competencesDisponibles as $comp)
                    @php
                        $estSelectionne = in_array($comp, $competencesSelectionnees);
                    @endphp
                    <div class="carte-choix {{ $estSelectionne ? 'selectionne' : '' }}"
                         onclick="basculerCompetence('{{ $comp }}', this)">
                        <input type="checkbox" name="competences[]" value="{{ $comp }}"
                               {{ $estSelectionne ? 'checked' : '' }}
                               style="display: none;">
                        <div class="carte-titre">{{ $comp }}</div>
                    </div>
                @endforeach
            </div>

            <div class="texte-centre marge-bas-3">
                <span class="texte-or" id="affichageCompte">{{ count($competencesSelectionnees) }} / {{ $nombreCompetences }} sélectionnées</span>
            </div>

            <div class="bouton-groupe-ligne">
                <a href="{{ route('creation.caracteristiques') }}" class="bouton bouton-secondaire">Retour</a>
                <button type="submit" class="bouton bouton-principal" id="boutonSuivant">Suivant</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Données injectées par le serveur pour cette page ; toute la logique
            // vit dans un fichier séparé (public/js/creation/competences.js).
            window.DonneesCreationCompetences = {
                max: {{ $nombreCompetences }},
                selectionnees: @json($competencesSelectionnees),
            };
        </script>
        <script src="{{ asset('js/creation/competences.js') }}"></script>
    @endpush
@endsection
