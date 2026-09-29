@extends('layouts.app')

@section('title', 'Créer un personnage - Confirmation')

@section('content')
    <div class="carte">
        <div class="texte-centre marge-bas-3">
            <span class="texte-or">Créer un personnage</span>
        </div>

        <div class="barre-progression">
            <div class="ligne-progression">
                <div class="remplissage-progression" style="width: 100%;"></div>
            </div>
            <div class="etape-progression terminee">1</div>
            <div class="etape-progression terminee">2</div>
            <div class="etape-progression terminee">3</div>
            <div class="etape-progression terminee">4</div>
            <div class="etape-progression terminee">5</div>
            <div class="etape-progression terminee">6</div>
        </div>

        <h2 class="titre-principal texte-centre marge-bas-4">Récapitulatif</h2>

        @if($errors->any())
            <div class="alerte alerte-erreur">
                @foreach($errors->all() as $erreur)
                    {{ $erreur }}<br>
                @endforeach
            </div>
        @endif

        <div class="carte-resume">
            <div class="titre">Identité</div>
            <div class="valeur-affichee"><strong>{{ $donnees['nom'] }}</strong></div>
            <div class="valeur-affichee">{{ $race->nom }} {{ $sousRace ? '- ' . $sousRace->nom : '' }} / {{ $classe->nom }} {{ $sousClasse ? '- ' . $sousClasse->nom : '' }}</div>
            @if($donnees['alignement'])
                <div class="valeur-affichee">Alignement: {{ $donnees['alignement'] }}</div>
            @endif
            @if($donnees['description'])
                <div class="valeur-affichee marge-haut-2">{{ $donnees['description'] }}</div>
            @endif
        </div>

        <div class="carte-resume">
            <div class="titre">Caractéristiques</div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-top: 0.5rem;">
                @php
                    $stats = [
                        'force' => 'Force',
                        'dexterite' => 'Dex',
                        'constitution' => 'Con',
                        'intelligence' => 'Int',
                        'sagesse' => 'Sag',
                        'charisme' => 'Cha',
                    ];
                @endphp
                @foreach($stats as $cle => $libelle)
                    @php
                        $mod = floor(($abilites[$cle] - 10) / 2);
                        $modChaine = $mod >= 0 ? '+' . $mod : $mod;
                    @endphp
                    <div class="valeur-affichee">{{ $libelle }}: {{ $abilites[$cle] }} ({{ $modChaine }})</div>
                @endforeach
            </div>
            <div class="valeur-affichee marge-haut-2">Points de vie: {{ $pv }}</div>
        </div>

        <div class="carte-resume">
            <div class="titre">Compétences</div>
            <div class="valeur-affichee">{{ implode(', ', $competencesFinal) }}</div>
            <div class="valeur-affichee marge-haut-2" style="font-style: italic;">
                Classe : {{ implode(', ', $donnees['competences']) }} — Historique : {{ implode(', ', $historique?->competences ?? []) }}
            </div>
        </div>

        <div class="carte-resume">
            <div class="titre">Contexte & Historique</div>
            @if($historique)
                <div class="valeur-affichee"><strong>Historique:</strong> {{ $historique->nom }}</div>
            @endif
            @if($ideal)
                <div class="valeur-affichee"><strong>Idéal:</strong> {{ $ideal->valeur }}</div>
            @endif
            @if($defaut)
                <div class="valeur-affichee"><strong>Défaut:</strong> {{ $defaut->valeur }}</div>
            @endif
            @if($attachement)
                <div class="valeur-affichee"><strong>Attachement:</strong> {{ $attachement->valeur }}</div>
            @endif
            @if($personnalite)
                <div class="valeur-affichee"><strong>Personnalité:</strong> {{ $personnalite->valeur }}</div>
            @endif
            @if($donnees['outil'])
                <div class="valeur-affichee"><strong>Outil:</strong> {{ $donnees['outil'] }}</div>
            @endif
            <div class="valeur-affichee"><strong>Langues:</strong> {{ implode(', ', $donnees['langues']) }}</div>
            @if(!empty($donnees['equipement']))
                <div class="valeur-affichee"><strong>Équipement:</strong> {{ implode(', ', $donnees['equipement']) }}</div>
            @endif
        </div>

        <form action="{{ route('creation.finaliser') }}" method="POST">
            @csrf
            <div class="bouton-groupe-ligne">
                <a href="{{ route('creation.contexte') }}" class="bouton bouton-secondaire">Retour</a>
                <button type="submit" class="bouton bouton-principal">Créer mon personnage</button>
            </div>
        </form>
    </div>
@endsection
