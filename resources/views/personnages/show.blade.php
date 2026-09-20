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
            gap: 0.75rem;
            font-size: 0.8rem;
            color: #b8a9c9;
            padding: 0.2rem 0;
        }

        .sheet-row > span:first-child {
            flex-shrink: 0;
        }

        /* Lignes à colonne unique (capacités, dons...) : pas de valeur à
           droite à préserver, donc le texte doit pouvoir rétrécir et
           passer à la ligne plutôt que déborder de la carte. */
        .sheet-row > span:first-child:only-child {
            flex-shrink: 1;
            min-width: 0;
            overflow-wrap: break-word;
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
            flex: 1;
            min-width: 0;
            text-align: right;
        }

        .avatar-label {
            cursor: pointer;
            display: block;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            background: rgba(15, 5, 30, 0.9);
            border: 2px solid #c49a3c;
            position: relative;
        }

        .avatar-label img {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Le logo par défaut contient déjà son propre médaillon circulaire :
           le recadrer en "cover" le décentrait (marges transparentes
           asymétriques dans le fichier source), il est donc affiché en
           entier ("contain") plutôt que rogné. Une vraie photo de
           personnage, elle, n'a pas ce médaillon et gagne à remplir tout
           le cercle. */
        .avatar-label img.avatar-photo {
            object-fit: cover;
        }

        .avatar-label img.avatar-logo-defaut {
            object-fit: contain;
        }

        .avatar-overlay {
            position: absolute;
            inset: 0;
            background: rgba(20, 5, 40, 0.7);
            color: #e6b84d;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .avatar-label:hover .avatar-overlay {
            opacity: 1;
        }

        .badge-ca {
            position: absolute;
            top: -5px;
            left: -5px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #1a0533;
            border: 2px solid #e6b84d;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #e6b84d;
            font-size: 0.65rem;
            font-weight: 700;
            line-height: 1.1;
        }
    </style>
@endpush
@section('content')
    <div class="sheet-container" style="display: grid; grid-template-columns: 250px 1fr; gap: 1.5rem;">
        <!-- Left Sidebar -->
        <div class="card" style="height: fit-content;">
            <div class="text-gold mb-3" style="font-size: 1.1rem;">
                Sheet Happens
            </div>
            <div style="border-top: 1px solid #c49a3c; padding-top: 1rem;">
                <a href="{{ route('personnages.index') }}" class="text-muted"
                    style="text-decoration: none; font-size: 0.85rem;">
                    ← Retour à la sélection de personnages
                </a>
            </div>
        </div>
        <div>
            <div class="card" style="height: fit-content;">
                <!-- Informations principales du personnage -->
                <div style="display: flex; gap: 2rem; align-items: flex-start; margin-bottom: 2rem;">
                    <!-- Avatar, CA et Niveau -->
                    <div style="position: relative;">
                        <form action="{{ route('personnages.photo', $personnage) }}" method="POST" enctype="multipart/form-data" style="margin: 0;">
                            @csrf
                            @method('PATCH')
                            <label for="input-photo-personnage" class="avatar-label" title="Changer la photo">
                                <img src="{{ $personnage->photo ? \Illuminate\Support\Facades\Storage::url($personnage->photo) : asset('images/logo_rmb.png') }}"
                                     alt="Portrait de {{ $personnage->nom }}"
                                     class="{{ $personnage->photo ? 'avatar-photo' : 'avatar-logo-defaut' }}">
                                <span class="avatar-overlay">Changer<br>la photo</span>
                            </label>
                            <input type="file" id="input-photo-personnage" name="photo" accept="image/*" style="display: none;" onchange="this.form.submit()">
                        </form>

                        <div class="badge-ca">CA<br>{{ $classeArmure }}</div>

                        <div style="position: absolute; bottom: -5px; right: -5px; width: 45px; height: 45px; border-radius: 50%; background: #e6b84d; display: flex; align-items: center; justify-content: center; text-align: center; color: #1a0533; font-size: 0.7rem; font-weight: 600;">
                            Niveau<br>{{ $personnage->niveau }}
                        </div>
                    </div>
                    <div
                        style="
                       flex: 1;
                       background: rgba(25, 10, 50, 0.9);
                       border: 1px solid #c49a3c;
                       border-radius: 12px;
                       padding: 1.25rem;
                   ">
                        <div
                            style="
                           display: flex;
                           justify-content: space-between;
                           align-items: flex-start;
                           gap: 1rem;
                       ">
                            <div>
                                <div
                                    style="
                                   font-size: 1.3rem;
                                   font-weight: 700;
                                   color: #e6b84d;
                                   margin-bottom: 0.5rem;
                               ">
                                    {{ $personnage->nom }}
                                </div>
                                <div
                                    style="
                                   font-size: 0.9rem;
                                   color: #b8a9c9;
                                   margin-bottom: 0.25rem;
                               ">
                                    {{ $personnage->classe->nom }} - {{ $personnage->race->nom }}
                                    @if ($personnage->sousRace)
                                        {{ $personnage->sousRace->nom }}
                                    @endif
                                </div>
                                @if ($personnage->alignement)
                                    <div
                                        style="
                                       font-size: 0.9rem;
                                       color: #b8a9c9;
                                       margin-bottom: 0.75rem;
                                   ">
                                        {{ $personnage->alignement }}
                                    </div>
                                @endif
                            </div>
                            <!-- Actions -->
                            <div
                                style="
                               display: flex;
                               flex-direction: column;
                               align-items: flex-end;
                               gap: 0.5rem;
                           ">
                                <!-- Modifier -->
                                <button type="button" class="btn btn-secondary btn-small" style="text-align: center;"
                                    onclick="window.location.href='{{ route('personnages.edit', $personnage) }}'">
                                    Modifier
                                </button>
                                <!-- Supprimer -->
                                <form id="form-supprimer-personnage"
                                    action="{{ route('personnages.destroy', $personnage) }}" method="POST"
                                    style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-small"
                                        onclick="ouvrirModalSuppression(
                                           'form-supprimer-personnage',
                                           '{{ addslashes($personnage->nom) }}'
                                       )">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                        <!-- Expérience -->
                        <div
                            style="
                           font-size: 0.75rem;
                           color: #b8a9c9;
                           display: flex;
                           justify-content: space-between;
                           margin-top: 0.5rem;
                       ">
                            <span>Expériences</span>
                            <span>
                                @if ($seuilExperience !== null)
                                    {{ $personnage->experience }}/{{ $seuilExperience }}
                                @else
                                    {{ $personnage->experience }} (niveau maximum)
                                @endif
                            </span>
                        </div>
                        <div
                            style="
                           height: 6px;
                           background: rgba(25, 10, 50, 0.9);
                           border-radius: 3px;
                           margin-top: 0.25rem;
                           overflow: hidden;
                       ">
                            <div
                                style="
                               width: {{ $seuilExperience ? min(100, (int) floor($personnage->experience / $seuilExperience * 100)) : 100 }}%;
                               height: 100%;
                               background: #e6b84d;
                               border-radius: 3px;
                           ">
                            </div>
                        </div>
                        @if ($seuilExperience !== null)
                            <form action="{{ route('personnages.experience.ajouter', $personnage) }}" method="POST"
                                style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.5rem;">
                                @csrf
                                <input type="number" name="experience" min="1" max="1000000" required
                                    placeholder="Points d'XP"
                                    style="
                                       flex: 1;
                                       min-width: 0;
                                       background: rgba(15, 5, 30, 0.9);
                                       border: 1px solid #c49a3c;
                                       border-radius: 6px;
                                       color: #e8d5b5;
                                       padding: 0.3rem 0.5rem;
                                       font-size: 0.8rem;
                                   ">
                                <button type="submit" class="btn btn-small"
                                    style="
                                       background: #e6b84d;
                                       color: #1a0533;
                                       font-weight: 800;
                                       text-align: center;
                                       white-space: nowrap;
                                   ">
                                    ✦ Ajouter de l'expérience
                                </button>
                            </form>
                            @error('experience')
                                <div style="color: #e07a7a; font-size: 0.75rem; margin-top: 0.25rem;">
                                    {{ $message }}
                                </div>
                            @enderror
                        @endif
                    </div>
                </div>
                {{-- Caractéristiques, jets de sauvegarde, PV/vitesse/bonus --}}
                <div class="sheet-grid">
                    <!-- Caractéristiques -->
                    <div class="sheet-box">
                        <div class="sheet-box-title">
                            Caractéristiques
                        </div>
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
                        @foreach ($stats as $key => $label)
                            @php
                                $mod = floor(($personnage->abilites->$key - 10) / 2);
                                $modStr = $mod >= 0 ? '+' . $mod : $mod;
                            @endphp
                            <div class="sheet-row">
                                <span>{{ $label }}</span>
                                <span class="valeur">
                                    {{ $personnage->abilites->$key }} ({{ $modStr }})
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <!-- Jets de sauvegarde -->
                    <div class="sheet-box">
                        <div class="sheet-box-title">
                            Jets de sauvegarde
                        </div>
                        @foreach ($jetsDeSauvegarde as $jet)
                            @php
                                $bonusStr = $jet['bonus'] >= 0 ? '+' . $jet['bonus'] : $jet['bonus'];
                            @endphp
                            <div class="sheet-row">
                                <span>
                                    <span class="maitrise-dot {{ $jet['maitrise'] ? 'actif' : '' }}"></span>
                                    {{ $jet['label'] }}
                                </span>
                                <span class="valeur">
                                    {{ $bonusStr }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <!-- PV, vitesse et bonus -->
                    <div class="sheet-box">
                        <div class="sheet-box-title">PV, vitesse &amp; bonus</div>
                        <div class="sheet-row"><span>Points de vie</span><span class="valeur">{{ $personnage->point_de_vie }}</span></div>
                        <div class="sheet-row"><span>Classe d'Armure</span><span class="valeur">{{ $classeArmure }}</span></div>
                        <div class="sheet-row"><span>Vitesse</span><span class="valeur">{{ $personnage->race->vitesse ? $personnage->race->vitesse . ' pi' : '—' }}</span></div>
                        <div class="sheet-row"><span>Bonus de maîtrise</span><span class="valeur">+{{ $bonusMaitrise }}</span></div>
                        <div class="sheet-row"><span>Perception passive</span><span class="valeur">{{ $perceptionPassive }}</span></div>
                    </div>
                </div>

                {{-- Compétences --}}
                <div class="sheet-box mb-3">
                    <div class="sheet-box-title">
                        Compétences
                    </div>
                    <div style="columns: 2; column-gap: 1.5rem;">
                        @foreach ($competences as $competence)
                            @php
                                $bonusStr =
                                    $competence['bonus'] >= 0 ? '+' . $competence['bonus'] : $competence['bonus'];
                            @endphp
                            <div class="sheet-row" style="break-inside: avoid;">
                                <span>
                                    <span class="maitrise-dot {{ $competence['maitrise'] ? 'actif' : '' }}"></span>
                                    {{ $competence['nom'] }}
                                    <span class="text-muted">
                                        ({{ $competence['aptitude'] }})
                                    </span>
                                </span>
                                <span class="valeur">
                                    {{ $bonusStr }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div style="border-top: 1px solid rgba(196, 154, 60, 0.35); margin-top: 0.75rem; padding-top: 0.75rem;">
                        <div style="font-size: 0.8rem; font-weight: 600; color: #e6b84d; margin-bottom: 0.35rem;">Langues</div>
                        <div class="sheet-row">
                            <span>Langues</span>
                            <span class="valeur">
                                {{ $personnage->langues && count($personnage->langues) > 0 ? implode(', ', $personnage->langues) : 'Aucune' }}
                            </span>
                        </div>
                    </div>
                </div>
                {{-- Traits --}}
                <div class="sheet-box mb-3">
                    <div
                        style="
                       display: flex;
                       justify-content: space-between;
                       align-items: center;
                       margin-bottom: 0.5rem;
                   ">
                        <span class="sheet-box-title" style="margin-bottom: 0;">
                            Traits
                        </span>
                        <a href="{{ route('personnages.traits', $personnage) }}" class="btn btn-secondary btn-small">
                            Tous voir
                        </a>
                    </div>
                </div>
                {{-- Capacités et dons obtenus lors des montées de niveau --}}
                @if (!empty($personnage->capacites) || !empty($personnage->feats))
                    <div
                        style="
                       display: grid;
                       grid-template-columns: repeat(2, 1fr);
                       gap: 1rem;
                       margin-bottom: 1.5rem;
                   ">
                        <!-- Capacités -->
                        <div class="sheet-box">
                            <div class="sheet-box-title">
                                Capacités acquises
                            </div>
                            @forelse($personnage->capacites ?? [] as $capacite)
                                <div class="sheet-row">
                                    <span>✦ {{ $capacite }}</span>
                                </div>
                            @empty
                                <div class="text-muted" style="font-size: 0.8rem;">
                                    Aucune
                                </div>
                            @endforelse
                        </div>
                        <!-- Dons -->
                        <div class="sheet-box">
                            <div class="sheet-box-title">
                                Dons
                            </div>
                            @forelse($personnage->feats ?? [] as $feat)
                                <div class="sheet-row">
                                    <span>✦ {{ $feat }}</span>
                                </div>
                            @empty
                                <div class="text-muted" style="font-size: 0.8rem;">
                                    Aucun
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
                {{-- Actions : sorts ou attaques selon la classe --}}
                <div class="sheet-box mb-3">
                    <div class="sheet-box-title">
                        Actions
                    </div>
                    @if ($personnage->sousClasse)
                        <div class="text-muted mb-2" style="font-size: 0.8rem;">
                            Sous-classe : {{ $personnage->sousClasse->nom }}
                        </div>
                    @endif
                    @if ($actions['type'] === 'sorts')
                        @php
                            $incantation = $actions['incantation'];
                        @endphp
                        <div class="text-muted mb-2" style="font-size: 0.8rem;">
                            {{ $personnage->classe->nom }}
                            incante avec
                            {{ $incantation['caracteristique'] }}.
                        </div>
                        <div class="sheet-row">
                            <span>Jet de sauvegarde de tes sorts</span>
                            <span class="valeur">
                                {{ $incantation['dd'] }}
                            </span>
                        </div>
                        <div class="sheet-row">
                            <span>Bonus d'attaque de sorts</span>
                            <span class="valeur">
                                +{{ $incantation['bonusAttaque'] }}
                            </span>
                        </div>
                    @else
                        @forelse($actions['attaques'] as $attaque)
                            @php
                                $bonusStr =
                                    $attaque['bonusAttaque'] >= 0
                                        ? '+' . $attaque['bonusAttaque']
                                        : $attaque['bonusAttaque'];
                            @endphp
                            <div class="sheet-row">
                                <span>
                                    {{ $attaque['nom'] }}
                                    <span class="text-muted">
                                        ({{ $attaque['type'] }})
                                    </span>
                                </span>
                                <span class="valeur">
                                    {{ $bonusStr }} pour toucher,
                                    {{ $attaque['degats'] }} dégâts
                                </span>
                            </div>
                        @empty
                            <div class="text-muted" style="font-size: 0.85rem;">
                                Aucune arme reconnue dans l'équipement porté.
                            </div>
                        @endforelse
                    @endif
                </div>
                {{-- Historique & Contexte --}}
                <div
                    style="
                   background: rgba(25, 10, 50, 0.9);
                   border: 1px solid #c49a3c;
                   border-radius: 12px;
                   padding: 1rem;
                   margin-bottom: 1rem;
               ">
                    <div
                        style="
                       font-size: 0.85rem;
                       font-weight: 600;
                       color: #e6b84d;
                       margin-bottom: 0.5rem;
                   ">
                        Historique &amp; Contexte
                    </div>
                    @if ($personnage->historique)
                        <div style="font-size: 0.8rem; color: #b8a9c9;">
                            <strong>Historique:</strong>
                            {{ $personnage->historique->nom }}
                        </div>
                    @endif
                    @if ($personnage->ideal)
                        <div style="font-size: 0.8rem; color: #b8a9c9;">
                            <strong>Idéal:</strong>
                            {{ $personnage->ideal->valeur }}
                        </div>
                    @endif
                    @if ($personnage->defaut)
                        <div style="font-size: 0.8rem; color: #b8a9c9;">
                            <strong>Défaut:</strong>
                            {{ $personnage->defaut->valeur }}
                        </div>
                    @endif
                    @if ($personnage->attachement)
                        <div style="font-size: 0.8rem; color: #b8a9c9;">
                            <strong>Attachement:</strong>
                            {{ $personnage->attachement->valeur }}
                        </div>
                    @endif
                    @if ($personnage->personnalite)
                        <div style="font-size: 0.8rem; color: #b8a9c9;">
                            <strong>Personnalité:</strong>
                            {{ $personnage->personnalite->valeur }}
                        </div>
                    @endif
                </div>
                {{-- Inventaire --}}
                <div class="sheet-box">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span class="sheet-box-title" style="margin-bottom: 0;">Inventaire</span>
                        <a href="{{ route('personnages.inventaire.index', $personnage) }}" class="btn btn-secondary btn-small">Tous voir</a>
                    </div>
                    @if($personnage->objets->count() > 0)
                        <div class="text-muted" style="font-size: 0.85rem;">
                            {{ $personnage->objets->map(fn($objet) => $objet->nom . ($objet->pivot->quantite > 1 ? ' x' . $objet->pivot->quantite : '') . ($objet->pivot->equipe ? ' (équipé)' : ''))->implode(', ') }}
                        </div>
                    @else
                        <div class="text-muted" style="font-size: 0.85rem;">Aucun équipement enregistré.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('partials.modal-suppression-personnage')
@endsection
