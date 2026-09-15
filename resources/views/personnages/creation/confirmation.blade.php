@extends('layouts.app')

@section('title', 'Créer un personnage - Confirmation')

@section('content')
    <div class="card">
        <div class="text-center mb-3">
            <span class="text-gold">Créer un personnage</span>
        </div>

        <div class="progress-bar">
            <div class="progress-line">
                <div class="progress-fill" style="width: 100%;"></div>
            </div>
            <div class="progress-step completed">1</div>
            <div class="progress-step completed">2</div>
            <div class="progress-step completed">3</div>
            <div class="progress-step completed">4</div>
            <div class="progress-step completed">5</div>
            <div class="progress-step completed">6</div>
        </div>

        <h2 class="title-main text-center mb-4">Récapitulatif</h2>

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <div class="summary-card">
            <div class="title">Identité</div>
            <div class="value"><strong>{{ $data['nom'] }}</strong></div>
            <div class="value">{{ $race->nom }} {{ $sousRace ? '- ' . $sousRace->nom : '' }} / {{ $classe->nom }} {{ $sousClasse ? '- ' . $sousClasse->nom : '' }}</div>
            @if($data['alignement'])
                <div class="value">Alignement: {{ $data['alignement'] }}</div>
            @endif
            @if($data['description'])
                <div class="value mt-2">{{ $data['description'] }}</div>
            @endif
        </div>

        <div class="summary-card">
            <div class="title">Caractéristiques</div>
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
                @foreach($stats as $key => $label)
                    @php
                        $mod = floor(($abilites[$key] - 10) / 2);
                        $modStr = $mod >= 0 ? '+' . $mod : $mod;
                    @endphp
                    <div class="value">{{ $label }}: {{ $abilites[$key] }} ({{ $modStr }})</div>
                @endforeach
            </div>
            <div class="value mt-2">Points de vie: {{ $pv }}</div>
        </div>

        <div class="summary-card">
            <div class="title">Compétences</div>
            <div class="value">{{ implode(', ', $competencesFinal) }}</div>
            <div class="value mt-2" style="font-style: italic;">
                Classe : {{ implode(', ', $data['competences']) }} — Historique : {{ implode(', ', $historique->competences ?? []) }}
            </div>
        </div>

        <div class="summary-card">
            <div class="title">Contexte & Historique</div>
            @if($historique)
                <div class="value"><strong>Historique:</strong> {{ $historique->nom }}</div>
            @endif
            @if($ideal)
                <div class="value"><strong>Idéal:</strong> {{ $ideal->valeur }}</div>
            @endif
            @if($defaut)
                <div class="value"><strong>Défaut:</strong> {{ $defaut->valeur }}</div>
            @endif
            @if($attachement)
                <div class="value"><strong>Attachement:</strong> {{ $attachement->valeur }}</div>
            @endif
            @if($personnalite)
                <div class="value"><strong>Personnalité:</strong> {{ $personnalite->valeur }}</div>
            @endif
            @if($data['outil'])
                <div class="value"><strong>Outil:</strong> {{ $data['outil'] }}</div>
            @endif
            <div class="value"><strong>Langues:</strong> {{ implode(', ', $data['langues']) }}</div>
            @if(!empty($data['equipement']))
                <div class="value"><strong>Équipement:</strong> {{ implode(', ', $data['equipement']) }}</div>
            @endif
        </div>

        <form action="{{ route('creation.finalize') }}" method="POST">
            @csrf
            <div class="btn-group-row">
                <a href="{{ route('creation.contexte') }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary">Créer mon personnage</button>
            </div>
        </form>
    </div>
@endsection
