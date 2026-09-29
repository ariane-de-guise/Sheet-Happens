@extends('layouts.app')

@section('titre', 'Créer un personnage - Race')

@section('contenu')
    <div class="carte">
        <div class="texte-centre marge-bas-3">
            <span class="texte-or">Créer un personnage</span>
        </div>

        <div class="barre-progression">
            <div class="ligne-progression">
                <div class="remplissage-progression" style="width: 0%;"></div>
            </div>
            @for($i = 1; $i <= 6; $i++)
                <div class="etape-progression {{ $i === 1 ? 'active' : '' }}">
                    {{ $i }}
                </div>
            @endfor
        </div>

        <h2 class="titre-section texte-centre">Choisissez votre race</h2>

        @if(session('erreur'))
            <div class="alerte alerte-erreur">{{ session('erreur') }}</div>
        @endif

        @if($errors->any())
            <div class="alerte alerte-erreur" id="raceErreurs">
                @foreach($errors->all() as $erreur)
                    {{ $erreur }}<br>
                @endforeach
            </div>
        @else
            <div class="alerte alerte-erreur" id="raceErreurs" style="display: none;">
                Vous devez choisir une race avant de continuer.
            </div>
        @endif

        @php
            $raceChoisieModele = $raceChoisie ? $races->find($raceChoisie) : null;
            $sousRaceChoisieModele = $raceChoisieModele && $sousRaceChoisie
                ? $raceChoisieModele->sousRaces->firstWhere('id', $sousRaceChoisie)
                : null;
        @endphp

        <form action="{{ route('creation.race.enregistrer') }}" method="POST" id="raceFormulaire">
            @csrf
            <div class="grille-cartes marge-bas-4" id="racesGrille">
                @foreach($races as $race)
                    <div class="carte-choix {{ $raceChoisie == $race->id ? 'selectionne' : '' }}"
                         onclick="choisirRace({{ $race->id }}, this)">
                        <input type="radio" name="race_id" value="{{ $race->id }}"
                               {{ $raceChoisie == $race->id ? 'checked' : '' }}
                               required
                               style="display: none;">
                        <div class="carte-titre">{{ $race->nom }}</div>
                        @if($race->taille)
                            <div class="carte-desc">{{ $race->taille }}</div>
                        @endif
                        @if($race->vitesse)
                            <div class="carte-desc">Vitesse: {{ $race->vitesse }} ft</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="boite-aide marge-bas-4" id="raceDescription" style="{{ $raceChoisieModele && $raceChoisieModele->description ? '' : 'display:none;' }}">
                <div class="titre" id="raceDescriptionTitre">{{ $raceChoisieModele->nom ?? '' }}</div>
                <div class="contenu" id="raceDescriptionContenu">{{ $raceChoisieModele->description ?? '' }}</div>
            </div>

            <div id="sousRaceConteneur">
                @if($raceChoisieModele && $raceChoisieModele->sousRaces->count() > 0)
                    <h3 class="titre-section marge-bas-2">Sous-race</h3>
                    <div class="alerte alerte-erreur" id="sousRaceErreurs" style="display: none;">
                        Vous devez choisir une sous-race avant de continuer.
                    </div>
                    <div class="grille-cartes marge-bas-4" id="sousRacesGrille">
                        @foreach($raceChoisieModele->sousRaces as $sousRace)
                            <div class="carte-choix {{ $sousRaceChoisie == $sousRace->id ? 'selectionne' : '' }}"
                                 onclick="choisirSousRace({{ $sousRace->id }}, this)">
                                <input type="radio" name="sous_race_id" value="{{ $sousRace->id }}"
                                       {{ $sousRaceChoisie == $sousRace->id ? 'checked' : '' }}
                                       required
                                       style="display: none;">
                                <div class="carte-titre">{{ $sousRace->nom }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="boite-aide marge-bas-4" id="sousRaceDescription" style="{{ $sousRaceChoisieModele && $sousRaceChoisieModele->description ? '' : 'display:none;' }}">
                        <div class="titre" id="sousRaceDescriptionTitre">{{ $sousRaceChoisieModele->nom ?? '' }}</div>
                        <div class="contenu" id="sousRaceDescriptionContenu">{{ $sousRaceChoisieModele->description ?? '' }}</div>
                    </div>
                @endif
            </div>

            <div class="bouton-groupe-ligne">
                <a href="{{ route('welcome') }}" class="bouton bouton-secondaire">Retour</a>
                <button type="submit" class="bouton bouton-principal" id="boutonSuivant">Suivant</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Données injectées par le serveur pour cette page ; toute la logique
            // vit dans un fichier séparé (public/js/creation/race.js).
            window.DonneesCreationRace = {
                races: @json($races->keyBy('id')),
                sousRacesInitiales: @json($raceChoisieModele->sousRaces ?? []),
            };
        </script>
        <script src="{{ asset('js/creation/race.js') }}"></script>
    @endpush
@endsection
