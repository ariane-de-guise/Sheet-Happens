@extends('layouts.app')

@section('title', $personnage->nom . ' - Sheet Happens')

@section('content')
<div style="display: grid; grid-template-columns: 250px 1fr; gap: 1.5rem;">
    <div class="card" style="height: fit-content;">
        <div class="text-gold mb-3" style="font-size: 1.1rem;">Sheet Happens</div>
        <div style="border-top: 1px solid #c49a3c; padding-top: 1rem;">
            <a href="{{ route('personnages.index') }}" class="text-muted" style="text-decoration: none; font-size: 0.85rem;">
                ← Retour à la sélection de personnages
            </a>
        </div>
    </div>

    <div>
        <div class="card" style="height: fit-content;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <a href="{{ route('personnages.index') }}" class="text-gold" style="text-decoration: none; font-size: 0.9rem;">
                    ← Retour à la sélection de personnages
                </a>
                <button class="btn btn-secondary btn-small">Édité</button>
            </div>

            <div style="display: flex; gap: 2rem; margin-bottom: 2rem;">
                <div style="position: relative;">
                    <div style="width: 120px; height: 120px; border-radius: 50%; background: #e6b84d;"></div>
                    <div style="position: absolute; bottom: -5px; right: -5px; width: 45px; height: 45px; border-radius: 50%; background: #e6b84d; display: flex; align-items: center; justify-content: center; color: #1a0533; font-size: 0.7rem; font-weight: 600;">
                        Niveaux<br>{{ $personnage->niveau }}
                    </div>
                </div>

                <div style="flex: 1;">
                    <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1.25rem;">
                        <div style="font-size: 1.3rem; font-weight: 700; color: #e6b84d; margin-bottom: 0.5rem;">
                            {{ $personnage->nom }}
                        </div>
                        <div style="font-size: 0.9rem; color: #b8a9c9; margin-bottom: 0.25rem;">
                            {{ $personnage->classe->nom }} - {{ $personnage->race->nom }}
                            @if($personnage->sousRace) {{ $personnage->sousRace->nom }} @endif
                        </div>
                        @if($personnage->alignement)
                            <div style="font-size: 0.9rem; color: #b8a9c9; margin-bottom: 0.75rem;">
                                {{ $personnage->alignement }}
                            </div>
                        @endif
                        <div style="font-size: 0.75rem; color: #b8a9c9; display: flex; justify-content: space-between;">
                            <span>Expériences</span>
                            <span>0/300</span>
                        </div>
                        <div style="height: 6px; background: rgba(25, 10, 50, 0.9); border-radius: 3px; margin-top: 0.25rem; overflow: hidden;">
                            <div style="width: 0%; height: 100%; background: #e6b84d; border-radius: 3px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1rem;">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #e6b84d; margin-bottom: 0.5rem;">Caractéristiques</div>
                    @php
                        $stats = [
                            'force' => 'For',
                            'dexterite' => 'Dex',
                            'constitution' => 'Con',
                            'intelligence' => 'Int',
                            'sagesse' => 'Sag',
                            'charisme' => 'Cha',
                        ];
                    @endphp
                    @foreach($stats as $key => $label)
                        @php
                            $mod = floor(($personnage->abilites->$key - 10) / 2);
                            $modStr = $mod >= 0 ? '+' . $mod : $mod;
                        @endphp
                        <div style="font-size: 0.8rem; color: #b8a9c9;">
                            {{ $label }}: {{ $personnage->abilites->$key }} ({{ $modStr }})
                        </div>
                    @endforeach
                </div>

                <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1rem;">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #e6b84d; margin-bottom: 0.5rem;">Compétences</div>
                    @if($personnage->listeCompetence && $personnage->listeCompetence->competences)
                        @foreach($personnage->listeCompetence->competences as $comp)
                            <div style="font-size: 0.8rem; color: #b8a9c9;">{{ $comp }}</div>
                        @endforeach
                    @endif
                </div>

                <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1rem;">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #e6b84d; margin-bottom: 0.5rem;">Détails</div>
                    <div style="font-size: 0.8rem; color: #b8a9c9;">PV: {{ $personnage->point_de_vie }}</div>
                    @if($personnage->race->vitesse)
                        <div style="font-size: 0.8rem; color: #b8a9c9;">Vitesse: {{ $personnage->race->vitesse }} pi</div>
                    @endif
                    <div style="font-size: 0.8rem; color: #b8a9c9;">Niveau: {{ $personnage->niveau }}</div>
                </div>
            </div>

            <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.95rem; font-weight: 600; color: #e6b84d;">Équipements</span>
                    <button class="btn btn-secondary btn-small">Tous voir</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
