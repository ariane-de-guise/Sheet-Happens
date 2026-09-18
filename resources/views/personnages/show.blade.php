@extends('layouts.app')

@section('title', $personnage->nom . ' - Sheet Happens')

@push('styles')
    <style>
        .sheet-container {
            max-width: 1100px;
        }

        .sheet-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .sheet-grid {
                grid-template-columns: 1fr;
            }
        }

        .sheet-box {
            background: rgba(25, 10, 50, 0.9);
            border: 1px solid #c49a3c;
            border-radius: 12px;
            padding: 1rem;
        }

        .sheet-box-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #e6b84d;
            margin-bottom: 0.5rem;
        }

        .sheet-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #b8a9c9;
            padding: 0.2rem 0;
        }

        .sheet-row .maitrise-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.4rem;
            background: rgba(184, 169, 201, 0.25);
            vertical-align: middle;
        }

        .sheet-row .maitrise-dot.actif {
            background: #e6b84d;
        }

        .sheet-row .valeur {
            color: #e8d5b5;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="sheet-container" style="display: grid; grid-template-columns: 250px 1fr; gap: 1.5rem;">
        <!-- Left Sidebar -->
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

                <div style="display: flex; gap: 2rem; align-items: flex-start; margin-bottom: 2rem;">
                    <!-- Avatar & Level -->
                    <div style="position: relative;">
                        <div style="width: 120px; height: 120px; border-radius: 50%; background: #e6b84d;"></div>
                        <div style="position: absolute; bottom: -5px; right: -5px; width: 45px; height: 45px; border-radius: 50%; background: #e6b84d; display: flex; align-items: center; justify-content: center; text-align: center; color: #1a0533; font-size: 0.7rem; font-weight: 600;">
                            Niveau<br>{{ $personnage->niveau }}
                        </div>
                    </div>

                    <div style="flex: 1; background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
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
                            </div>

                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('personnages.edit', $personnage) }}" class="btn btn-secondary btn-small">Modifier</a>
                            </div>
                        </div>

                        <div style="font-size: 0.75rem; color: #b8a9c9; display: flex; justify-content: space-between; margin-top: 0.5rem;">
                            <span>Expériences</span>
                            <span>0/300</span>
                        </div>
                        <div style="height: 6px; background: rgba(25, 10, 50, 0.9); border-radius: 3px; margin-top: 0.25rem; overflow: hidden;">
                            <div style="width: 0%; height: 100%; background: #e6b84d; border-radius: 3px;"></div>
                        </div>
                    </div>
                </div>

                {{-- Caractéristiques, jets de sauvegarde, PV/vitesse/bonus --}}
                <div class="sheet-grid">
                    <div class="sheet-box">
                        <div class="sheet-box-title">Caractéristiques</div>
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
                            <div class="sheet-row">
                                <span>{{ $label }}</span>
                                <span class="valeur">{{ $personnage->abilites->$key }} ({{ $modStr }})</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="sheet-box">
                        <div class="sheet-box-title">Jets de sauvegarde</div>
                        @foreach($jetsDeSauvegarde as $jet)
                            @php $bonusStr = $jet['bonus'] >= 0 ? '+' . $jet['bonus'] : $jet['bonus']; @endphp
                            <div class="sheet-row">
                            <span>
                                <span class="maitrise-dot {{ $jet['maitrise'] ? 'actif' : '' }}"></span>
                                {{ $jet['label'] }}
                            </span>
                                <span class="valeur">{{ $bonusStr }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="sheet-box">
                        <div class="sheet-box-title">PV, vitesse &amp; bonus</div>
                        <div class="sheet-row"><span>Points de vie</span><span class="valeur">{{ $personnage->point_de_vie }}</span></div>
                        <div class="sheet-row"><span>Vitesse</span><span class="valeur">{{ $personnage->race->vitesse ? $personnage->race->vitesse . ' pi' : '—' }}</span></div>
                        <div class="sheet-row"><span>Bonus de maîtrise</span><span class="valeur">+{{ $bonusMaitrise }}</span></div>
                        <div class="sheet-row"><span>Perception passive</span><span class="valeur">{{ $perceptionPassive }}</span></div>
                    </div>
                </div>

                {{-- Compétences --}}
                <div class="sheet-box mb-3">
                    <div class="sheet-box-title">Compétences</div>
                    <div style="columns: 2; column-gap: 1.5rem;">
                        @foreach($competences as $competence)
                            @php $bonusStr = $competence['bonus'] >= 0 ? '+' . $competence['bonus'] : $competence['bonus']; @endphp
                            <div class="sheet-row" style="break-inside: avoid;">
                            <span>
                                <span class="maitrise-dot {{ $competence['maitrise'] ? 'actif' : '' }}"></span>
                                {{ $competence['nom'] }} <span class="text-muted">({{ $competence['aptitude'] }})</span>
                            </span>
                                <span class="valeur">{{ $bonusStr }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div style="border-top: 1px solid rgba(196, 154, 60, 0.35); margin-top: 0.75rem; padding-top: 0.75rem;">
                        <div style="font-size: 0.8rem; font-weight: 600; color: #e6b84d; margin-bottom: 0.35rem;">Langues et outils</div>
                        <div class="sheet-row">
                            <span>Langues</span>
                            <span class="valeur">{{ $personnage->langues && count($personnage->langues) > 0 ? implode(', ', $personnage->langues) : 'Aucune' }}</span>
                        </div>
                        <div class="sheet-row">
                            <span>Outil</span>
                            <span class="valeur">{{ $personnage->outil ?? 'Aucun' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Traits (bonus et capacités) : contenu à venir --}}
                <div class="sheet-box mb-3">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span class="sheet-box-title" style="margin-bottom: 0;">Traits</span>
                        <a href="{{ route('personnages.traits', $personnage) }}" class="btn btn-secondary btn-small">Tous voir</a>
                    </div>
                </div>

                {{-- Actions : sorts ou attaques selon la classe --}}
                <div class="sheet-box mb-3">
                    <div class="sheet-box-title">Actions</div>
                    @if($personnage->sousClasse)
                        <div class="text-muted mb-2" style="font-size: 0.8rem;">Sous-classe : {{ $personnage->sousClasse->nom }}</div>
                    @endif
                    @if($actions['type'] === 'sorts')
                        @php $incantation = $actions['incantation']; @endphp
                        <div class="text-muted mb-2" style="font-size: 0.8rem;">
                            {{ $personnage->classe->nom }} incante avec {{ $incantation['caracteristique'] }}.
                        </div>
                        <div class="sheet-row"><span>Jet de sauvegarde de tes sorts</span><span class="valeur">{{ $incantation['dd'] }}</span></div>
                        <div class="sheet-row"><span>Bonus d'attaque de sorts</span><span class="valeur">+{{ $incantation['bonusAttaque'] }}</span></div>
                    @else
                        @forelse($actions['attaques'] as $attaque)
                            @php $bonusStr = $attaque['bonusAttaque'] >= 0 ? '+' . $attaque['bonusAttaque'] : $attaque['bonusAttaque']; @endphp
                            <div class="sheet-row">
                                <span>{{ $attaque['nom'] }} <span class="text-muted">({{ $attaque['type'] }})</span></span>
                                <span class="valeur">{{ $bonusStr }} pour toucher, {{ $attaque['degats'] }} dégâts</span>
                            </div>
                        @empty
                            <div class="text-muted" style="font-size: 0.85rem;">Aucune arme reconnue dans l'équipement porté.</div>
                        @endforelse
                    @endif
                </div>

                {{-- Historique & Contexte --}}
                <div class="sheet-box mb-3">
                    <div class="sheet-box-title">Historique &amp; Contexte</div>
                    @if($personnage->historique)
                        <div class="sheet-row"><span>Historique</span><span class="valeur">{{ $personnage->historique->nom }}</span></div>
                    @endif
                    @if($personnage->personnalite)
                        <div class="sheet-row"><span>Personnalité</span><span class="valeur">{{ $personnage->personnalite->valeur }}</span></div>
                    @endif
                    @if($personnage->ideal)
                        <div class="sheet-row"><span>Idéal</span><span class="valeur">{{ $personnage->ideal->valeur }}</span></div>
                    @endif
                    @if($personnage->attachement)
                        <div class="sheet-row"><span>Attachement</span><span class="valeur">{{ $personnage->attachement->valeur }}</span></div>
                    @endif
                    @if($personnage->defaut)
                        <div class="sheet-row"><span>Défaut</span><span class="valeur">{{ $personnage->defaut->valeur }}</span></div>
                    @endif
                </div>

                {{-- Inventaire --}}
                <div class="sheet-box">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span class="sheet-box-title" style="margin-bottom: 0;">Inventaire</span>
                        <a href="{{ route('personnages.inventaire', $personnage) }}" class="btn btn-secondary btn-small">Tous voir</a>
                    </div>
                    @if($personnage->equipement && count($personnage->equipement) > 0)
                        <div class="text-muted" style="font-size: 0.85rem;">
                            {{ implode(', ', $personnage->equipement) }}
                        </div>
                    @else
                        <div class="text-muted" style="font-size: 0.85rem;">Aucun équipement enregistré.</div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
