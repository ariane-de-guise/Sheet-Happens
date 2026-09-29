<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $personnage->nom }} - Sheet Happens</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_rmb.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{asset('css/fiche-pdf.css')}}">

    <style>

    </style>
</head>
<body>

    <div class="card" style="height: fit-content;">

        <div style="display: flex; gap: 2rem; align-items: flex-start; margin-bottom: 2rem;">
            <!-- Avatar & Level -->
            <div style="position: relative;">
                @if($photoDataUri)
                    <img src="{{ $photoDataUri }}"
                         alt="Photo de {{ $personnage->nom }}"
                         style="width: 120px; height: 120px; border-radius: 50%; object-fit: {{ $personnage->photo ? 'cover' : 'contain' }}; display: block;">
                @endif
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


                </div>
                <div style="font-size: 0.75rem; color: #b8a9c9; display: flex; justify-content: space-between; margin-top: 0.5rem;">
                    <span>Expériences</span>
                    <span>
                        @if($seuilExperience !== null)
                            {{ $personnage->experience }}/{{ $seuilExperience }}
                        @else
                            {{ $personnage->experience }} (niveau maximum)
                        @endif
                    </span>
                </div>
                <div style="height: 6px; background: rgba(25, 10, 50, 0.9); border-radius: 3px; margin-top: 0.25rem; overflow: hidden;">
                    <div style="width: {{ $seuilExperience ? min(100, (int) floor($personnage->experience / $seuilExperience * 100)) : 100 }}%; height: 100%; background: #e6b84d; border-radius: 3px;"></div>
                </div>
            </div>
        </div>

        {{-- Caractéristiques, jets de sauvegarde, PV/vitesse/bonus --}}
        <div class="sheet-grid">
            <div class="sheet-box" style="margin-bottom: 0;">
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

            <div class="sheet-box" style="margin-bottom: 0;">
                <div class="sheet-box-title">Jets de sauvegarde</div>
                @foreach($jetsDeSauvegarde as $jet)
                    @php $bonusStr = $jet['bonus'] >= 0 ? '+' . $jet['bonus'] : $jet['bonus']; @endphp
                    <div class="sheet-row">
                            <span>
                                <span class="maitrise-dot {{ $jet['maitrise'] ? 'actif' : '' }}"></span>
                                {{ $jet['libelle'] }}
                            </span>
                        <span class="valeur">{{ $bonusStr }}</span>
                    </div>
                @endforeach
            </div>

            <div class="sheet-box" style="margin-bottom: 0;">
                <div class="sheet-box-title">PV, vitesse &amp; bonus</div>
                <div class="sheet-row"><span>Points de vie</span><span class="valeur">{{ $personnage->point_de_vie_actuel ?? $personnage->point_de_vie }} / {{ $personnage->point_de_vie }}</span></div>
                <div class="sheet-row"><span>Classe d'Armure</span><span class="valeur">{{ $classeArmure }}</span></div>
                <div class="sheet-row"><span>Vitesse</span><span class="valeur">{{ $personnage->race->vitesse ? $personnage->race->vitesse . ' pi' : '—' }}</span></div>
                <div class="sheet-row"><span>Bonus de maîtrise</span><span class="valeur">+{{ $bonusMaitrise }}</span></div>
                <div class="sheet-row"><span>Perception passive</span><span class="valeur">{{ $perceptionPassive }}</span></div>
            </div>
        </div>

        {{-- Compétences --}}
        <div class="sheet-box">
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

                @php
                    $sectionsTraits = [
                        ['titre' => 'Traits de race', 'elements' => $raceTraits],
                        ['titre' => 'Traits de sous-race', 'elements' => $sousRaceTraits],
                    ];
                @endphp
                @foreach($sectionsTraits as $section)
                    <div class="sheet-box" style="margin-bottom: 0.75rem;">
                        <div class="sheet-box-title">{{ $section['titre'] }}</div>
                        @if($section['elements']->isEmpty())
                            <div class="text-muted" style="font-size: 0.9rem;">
                                {{ $section['titre'] === 'Traits de sous-race' && !$personnage->sousRace ? "Ce personnage n'a pas de sous-race." : 'Aucun trait associé.' }}
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @foreach($section['elements'] as $trait)
                                    <div>
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.25rem;">
                                            <span class="text-gold" style="font-weight: 600;">{{ $trait->nom }}</span>
                                            <span class="text-muted" style="font-size: 0.8rem; white-space: nowrap;">Niveau {{ $trait->niveau }}</span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.9rem;">{{ $trait->description ?: 'Aucune description disponible.' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                @foreach([
                    ['titre' => 'Capacités de classe', 'elements' => $capacites],
                    ['titre' => 'Dons', 'elements' => $dons],
                ] as $section)
                    <div class="sheet-box" style="margin-bottom: 0.75rem;">
                        <div class="sheet-box-title">{{ $section['titre'] }}</div>
                        @if($section['elements']->isEmpty())
                            <div class="text-muted" style="font-size: 0.9rem;">{{ $section['titre'] === 'Dons' ? 'Aucun don acquis.' : 'Aucune capacité de classe acquise.' }}</div>
                        @else
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                @foreach($section['elements'] as $element)
                                    <div>
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.25rem;">
                                            <span class="text-gold" style="font-weight: 600;">{{ $element->nom }}</span>
                                            @if($section['titre'] === 'Capacités de classe')
                                                <span class="text-muted" style="font-size: 0.8rem; white-space: nowrap;">{{ $element->sousClasse?->nom ?? $personnage->classe->nom }} · Niveau {{ $element->niveau }}</span>
                                            @endif
                                        </div>
                                        <div class="text-muted" style="font-size: 0.9rem;">{{ $element->description ?: ($section['titre'] === 'Dons' ? ($element->resume ?: 'Aucune description disponible.') : 'Aucune description disponible.') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

        {{-- Actions --}}
        <div class="sheet-box">
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

        {{-- Sorts --}}
        @if($caracteristiqueSorts)
            @if($sortsPourFiche->isEmpty())
                <div class="sheet-box">
                    <div class="sheet-box-title">Sorts connus</div>
                    <div class="text-muted spell-empty">Aucun sort accessible pour ce personnage.</div>
                </div>
            @else
                @foreach($sortsPourFiche->groupBy(fn (array $element) => (int) $element['sort']->niveau) as $niveau => $sortsDuNiveau)
                    <div class="sheet-box spell-level-section">
                        <div class="sheet-box-title">{{ $niveau === 0 ? 'Sorts — Tours de magie' : 'Sorts — Niveau '.$niveau }}</div>
                        <div class="spell-list">
                            @foreach($sortsDuNiveau as $element)
                                @php $sort = $element['sort']; @endphp
                                <div class="spell-card">
                                    <div class="spell-card-header">
                                        <strong>{{ $sort->nom }}</strong>
                                        <span class="spell-status {{ $element['preparation'] > 0 ? 'is-prepared' : '' }}">
                                            {{ $element['preparation'] > 0 ? ($element['preparation'] === 2 ? 'Accordé' : 'Préparé') : 'Non préparé' }}
                                        </span>
                                    </div>
                                    <div class="spell-meta">
                                        {{ $sort->ecole }} · {{ $sort->temps_activation }} · {{ $sort->distance }} · {{ $sort->duree }}
                                        @if($sort->composants) · {{ $sort->composants }} @endif
                                        @if($sort->attaque_sauvegarde) · {{ $sort->attaque_sauvegarde }} @endif
                                    </div>
                                    <div class="text-muted spell-description">{{ $sort->description ?: 'Aucune description disponible.' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        @else
            <div class="sheet-box">
                <div class="sheet-box-title">Sorts</div>
                <div class="text-muted spell-empty">Ce personnage ne dispose pas de sorts.</div>
            </div>
        @endif

        {{-- Historique & Contexte --}}
        <div class="sheet-box">
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


        <div class="sheet-box" style="padding: 1.25rem;">
            <div class="text-gold mb-2" style="font-weight: 600;">Inventaire</div>
            @if($inventaire->isNotEmpty())
                <ul style="list-style: disc; padding-left: 1.25rem; color: #b8a9c9; font-size: 0.9rem;">
                    @foreach($inventaire as $objet)
                        <li>
                            {{ $objet->nom }}
                            @if($objet->pivot->quantite > 1)
                                x{{ $objet->pivot->quantite }}
                            @endif
                            @if($objet->pivot->quantite_equipee > 0)
                                (équipé{{ $objet->pivot->quantite_equipee > 1 ? ' x' . $objet->pivot->quantite_equipee : '' }})
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-muted" style="font-size: 0.9rem;">Aucun équipement enregistré.</div>
            @endif
        </div>



    </div>

@include('partials.modal-suppression-personnage')

</body>
</html>
