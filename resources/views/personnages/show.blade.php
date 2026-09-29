@extends('layouts.app')
@section('title', $personnage->nom . ' - Sheet Happens')
@push('styles')
    <style>
        .fiche-conteneur {
            max-width: 1100px;
        }

        .fiche-grille {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .fiche-grille {
                grid-template-columns: 1fr;
            }
        }

        .fiche-boite {
            background: var(--panneau);
            border: 1px solid var(--or-bordure);
            border-radius: 12px;
            padding: 1rem;
        }

        .fiche-boite-titre {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--or);
            margin-bottom: 0.5rem;
        }

        .fiche-ligne {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.8rem;
            color: var(--texte-secondaire);
            padding: 0.2rem 0;
        }

        .fiche-ligne > span:first-child {
            flex-shrink: 0;
        }

        /* Lignes à colonne unique (capacités, dons...) : pas de valeur à
           droite à préserver, donc le texte doit pouvoir rétrécir et
           passer à la ligne plutôt que déborder de la carte. */
        .fiche-ligne > span:first-child:only-child {
            flex-shrink: 1;
            min-width: 0;
            overflow-wrap: break-word;
        }

        .fiche-ligne .maitrise-point {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 0.4rem;
            background: var(--point-inactif);
            vertical-align: middle;
        }

        .fiche-ligne .maitrise-point.actif {
            background: var(--or-vif);
        }

        .fiche-ligne .valeur {
            color: var(--or-doux);
            font-weight: 600;
            flex: 1;
            min-width: 0;
            text-align: right;
        }

        .avatar-etiquette {
            cursor: pointer;
            display: block;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            background: var(--panneau-carte);
            border: 2px solid var(--or-bordure);
            position: relative;
        }

        .avatar-etiquette img {
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
        .avatar-etiquette img.avatar-photo {
            object-fit: cover;
        }

        .avatar-etiquette img.avatar-logo-defaut {
            object-fit: contain;
        }

        .avatar-voile {
            position: absolute;
            inset: 0;
            background: var(--avatar-overlay-fond);
            color: var(--or);
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .avatar-etiquette:hover .avatar-voile {
            opacity: 1;
        }

        .badge-ca {
            position: absolute;
            top: -5px;
            left: -5px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--texte-inverse);
            border: 2px solid var(--or);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--or);
            font-size: 0.65rem;
            font-weight: 700;
            line-height: 1.1;
        }
    </style>
@endpush
@section('content')
    <div class="fiche-conteneur" style="display: grid; grid-template-columns: 250px 1fr; gap: 1.5rem;">
        <!-- Left Sidebar -->
        <div class="carte" style="height: fit-content;">
            <div class="texte-or marge-bas-3" style="font-size: 1.1rem;">
                Sheet Happens
            </div>
            <div style="border-top: 1px solid var(--or-bordure); padding-top: 1rem;">
                <a href="{{ route('personnages.index') }}" class="texte-attenue"
                   style="text-decoration: none; font-size: 0.85rem;">
                    ← Retour à la sélection de personnages
                </a>
            </div>
        </div>

        <div>
            <div class="carte" style="height: fit-content;">

                <div style="display: flex; gap: 2rem; align-items: flex-start; margin-bottom: 2rem;">
                    <!-- Avatar, CA et Niveau -->
                    <div style="position: relative;">
                        <form action="{{ route('personnages.photo', $personnage) }}" method="POST" enctype="multipart/form-data" style="margin: 0;">
                            @csrf
                            @method('PATCH')
                            <label for="champ-photo-personnage" class="avatar-etiquette" title="Changer la photo">
                                <img src="{{ $personnage->photo ? \Illuminate\Support\Facades\Storage::url($personnage->photo) : asset('images/logo_rmb.png') }}"
                                     alt="Portrait de {{ $personnage->nom }}"
                                     class="{{ $personnage->photo ? 'avatar-photo' : 'avatar-logo-defaut' }}">
                                <span class="avatar-voile">Changer<br>la photo</span>
                            </label>
                            <input type="file" id="champ-photo-personnage" name="photo" accept="image/*" style="display: none;" onchange="this.form.submit()">
                        </form>

                        <div class="badge-ca">CA<br>{{ $classeArmure }}</div>

                        <div
                            style="
                           position: absolute;
                           bottom: -5px;
                           right: -5px;
                           width: 45px;
                           height: 45px;
                           border-radius: 50%;
                           background: var(--or-vif);
                           display: flex;
                           align-items: center;
                           justify-content: center;
                           text-align: center;
                           color: var(--texte-inverse);
                           font-size: 0.7rem;
                           font-weight: 600;
                       ">
                            Niveau<br>{{ $personnage->niveau }}
                        </div>
                    </div>

                    <div style="flex: 1; background: var(--panneau); border: 1px solid var(--or-bordure); border-radius: 12px; padding: 1.25rem;">
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
                                   color: var(--or);
                                   margin-bottom: 0.5rem;
                               ">
                                    {{ $personnage->nom }}
                                </div>
                                <div style="font-size: 0.9rem; color: var(--texte-secondaire); margin-bottom: 0.25rem;">
                                    {{ $personnage->classe->nom }} - {{ $personnage->race->nom }}
                                    @if ($personnage->sousRace)
                                        {{ $personnage->sousRace->nom }}
                                    @endif
                                </div>
                                @if ($personnage->alignement)
                                    <div style="font-size: 0.9rem; color: var(--texte-secondaire); margin-bottom: 0.75rem;">
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
                                <!-- Niveau maximum atteint -->
                                @if ($personnage->niveau >= 5)
                                    <span class="bouton bouton-petit"
                                          style="
                                           background: var(--niveau-max-fond);
                                           color: var(--niveau-max-texte);
                                           cursor: default;
                                       ">
                                        Niveau maximum
                                    </span>
                                @endif
                                <!-- Modifier -->
                                <button type="button" class="bouton bouton-secondaire bouton-petit" style="text-align: center;"
                                        onclick="window.location.href='{{ route('personnages.edit', $personnage) }}'">
                                    Modifier
                                </button>
                                <!-- Supprimer -->
                                <form id="formulaire-supprimer-personnage"
                                      action="{{ route('personnages.destroy', $personnage) }}" method="POST"
                                      style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="bouton bouton-danger bouton-petit"
                                            onclick="ouvrirModalSuppression(
                                           'formulaire-supprimer-personnage',
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
                           color: var(--texte-secondaire);
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
                           background: var(--panneau);
                           border-radius: 3px;
                           margin-top: 0.25rem;
                           overflow: hidden;
                       ">
                            <div
                                style="
                               width: {{ $seuilExperience ? min(100, (int) floor($personnage->experience / $seuilExperience * 100)) : 100 }}%;
                               height: 100%;
                               background: var(--or-vif);
                               border-radius: 3px;
                           ">
                            </div>
                        </div>
                        @if ($peutMonterDeNiveau)
                            <a href="{{ route('personnages.montee-niveau', $personnage) }}"
                               style="display: block; margin-top: 0.4rem; font-size: 0.8rem; font-weight: 700; color: var(--or); text-decoration: none;">
                                ✦ Assez d'expérience pour passer au niveau {{ $personnage->niveau + 1 }} — monter de niveau
                            </a>
                        @endif
                        @if ($seuilExperience !== null)
                            <form action="{{ route('personnages.experience.ajouter', $personnage) }}" method="POST"
                                  style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.5rem;">
                                @csrf
                                <input type="number" name="experience" min="1" max="1000000" required
                                       placeholder="Points d'XP"
                                       style="
                                       flex: 1;
                                       min-width: 0;
                                       background: var(--panneau-carte);
                                       border: 1px solid var(--or-bordure);
                                       border-radius: 6px;
                                       color: var(--or-doux);
                                       padding: 0.3rem 0.5rem;
                                       font-size: 0.8rem;
                                   ">
                                <button type="submit" class="bouton bouton-petit"
                                        style="
                                       background: var(--or-vif);
                                       color: var(--texte-inverse);
                                       font-weight: 800;
                                       text-align: center;
                                       white-space: nowrap;
                                   ">
                                    ✦ Ajouter de l'expérience
                                </button>
                            </form>
                            @error('experience')
                            <div style="color: var(--texte-negatif); font-size: 0.75rem; margin-top: 0.25rem;">
                                {{ $message }}
                            </div>
                            @enderror
                        @endif
                    </div>
                </div>
                {{-- Caractéristiques, jets de sauvegarde, PV/vitesse/bonus --}}
                <div class="fiche-grille">
                    <!-- Caractéristiques -->
                    <div class="fiche-boite">
                        <div class="fiche-boite-titre">
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
                        @foreach ($stats as $cle => $libelle)
                            @php
                                $mod = floor(($personnage->abilites->$cle - 10) / 2);
                                $modChaine = $mod >= 0 ? '+' . $mod : $mod;
                            @endphp
                            <div class="fiche-ligne">
                                <span>{{ $libelle }}</span>
                                <span class="valeur">
                                    {{ $personnage->abilites->$cle }} ({{ $modChaine }})
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <!-- Jets de sauvegarde -->
                    <div class="fiche-boite">
                        <div class="fiche-boite-titre">
                            Jets de sauvegarde
                        </div>
                        @foreach ($jetsDeSauvegarde as $jet)
                            @php
                                $bonusChaine = $jet['bonus'] >= 0 ? '+' . $jet['bonus'] : $jet['bonus'];
                            @endphp
                            <div class="fiche-ligne">
                                <span>
                                    <span class="maitrise-point {{ $jet['maitrise'] ? 'actif' : '' }}"></span>
                                    {{ $jet['libelle'] }}
                                </span>
                                <span class="valeur">
                                    {{ $bonusChaine }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <!-- PV, vitesse et bonus -->
                    <div class="fiche-boite">
                        <div class="fiche-boite-titre">PV, vitesse &amp; bonus</div>
                        <div class="fiche-ligne"><span>Points de vie</span><span class="valeur">{{ $personnage->point_de_vie }}</span></div>
                        <div class="fiche-ligne"><span>Classe d'Armure</span><span class="valeur">{{ $classeArmure }}</span></div>
                        <div class="fiche-ligne"><span>Vitesse</span><span class="valeur">{{ $personnage->race->vitesse ? $personnage->race->vitesse . ' pi' : '—' }}</span></div>
                        <div class="fiche-ligne"><span>Bonus de maîtrise</span><span class="valeur">+{{ $bonusMaitrise }}</span></div>
                        <div class="fiche-ligne"><span>Perception passive</span><span class="valeur">{{ $perceptionPassive }}</span></div>
                    </div>
                </div>

                {{-- Compétences --}}
                <div class="fiche-boite marge-bas-3">
                    <div class="fiche-boite-titre">
                        Compétences
                    </div>
                    <div style="columns: 2; column-gap: 1.5rem;">
                        @foreach ($competences as $competence)
                            @php
                                $bonusChaine =
                                    $competence['bonus'] >= 0 ? '+' . $competence['bonus'] : $competence['bonus'];
                            @endphp
                            <div class="fiche-ligne" style="break-inside: avoid;">
                                <span>
                                    <span class="maitrise-point {{ $competence['maitrise'] ? 'actif' : '' }}"></span>
                                    {{ $competence['nom'] }}
                                    <span class="texte-attenue">
                                        ({{ $competence['aptitude'] }})
                                    </span>
                                </span>
                                <span class="valeur">
                                    {{ $bonusChaine }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div
                        style="
                       border-top: 1px solid var(--or-ligne-forte);
                       margin-top: 0.75rem;
                       padding-top: 0.75rem;
                   ">
                        <div
                            style="
                           font-size: 0.8rem;
                           font-weight: 600;
                           color: var(--or);
                           margin-bottom: 0.35rem;
                       ">
                            Langues
                        </div>
                        <div class="fiche-ligne">
                            <span>Langues</span>
                            <span class="valeur">
                                {{ $personnage->langues && count($personnage->langues) > 0 ? implode(', ', $personnage->langues) : 'Aucune' }}
                            </span>
                        </div>
                    </div>
                </div>
                {{-- Traits --}}
                <div class="fiche-boite marge-bas-3">
                    <div
                        style="
                       display: flex;
                       justify-content: space-between;
                       align-items: center;
                       margin-bottom: 0.5rem;
                   ">
                        <span class="fiche-boite-titre" style="margin-bottom: 0;">
                            Traits
                        </span>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('personnages.traits', $personnage) }}" class="bouton bouton-secondaire bouton-petit">Tous voir</a>
                        </div>
                    </div>
                </div>
                {{-- Capacités de classe et dons ; la description s'affiche au survol --}}
                @if ($personnage->capacites->isNotEmpty() || $personnage->dons->isNotEmpty())
                    <div
                        style="
                       display: grid;
                       grid-template-columns: repeat(2, 1fr);
                       gap: 1rem;
                       margin-bottom: 1.5rem;
                   ">
                        <!-- Capacités -->
                        <div class="fiche-boite">
                            <div class="fiche-boite-titre">
                                Capacités acquises
                            </div>
                            @forelse($personnage->capacites as $capacite)
                                <div class="fiche-ligne">
                                    @include('partials.info-bulle', [
                                        'libelle' => '✦ ' . $capacite->nom,
                                        'titre' => $capacite->nom,
                                        'meta' => 'Niveau ' . $capacite->niveau . ' · ' . ($capacite->sousClasse?->nom ?? $personnage->classe->nom),
                                        'description' => $capacite->description,
                                    ])
                                </div>
                            @empty
                                <div class="texte-attenue" style="font-size: 0.8rem;">
                                    Aucune
                                </div>
                            @endforelse
                        </div>
                        <!-- Dons -->
                        <div class="fiche-boite">
                            <div class="fiche-boite-titre">
                                Dons
                            </div>
                            @forelse($personnage->dons as $don)
                                <div class="fiche-ligne">
                                    @include('partials.info-bulle', [
                                        'libelle' => '✦ ' . $don->nom,
                                        'titre' => $don->nom,
                                        'meta' => $don->resume,
                                        'description' => $don->description,
                                    ])
                                </div>
                            @empty
                                <div class="texte-attenue" style="font-size: 0.8rem;">
                                    Aucun
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
                {{-- Actions : sorts ou attaques selon la classe --}}
                <div class="fiche-boite marge-bas-3">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span class="fiche-boite-titre" style="margin-bottom: 0;">
                            Actions
                        </span>
                        @if ($aDesSorts)
                            <a href="{{ route('personnages.sorts.index', $personnage) }}" class="bouton bouton-secondaire bouton-petit">Sorts connus</a>
                        @endif
                    </div>
                    @if ($personnage->sousClasse)
                        <div class="texte-attenue marge-bas-2" style="font-size: 0.8rem;">
                            Sous-classe : {{ $personnage->sousClasse->nom }}
                        </div>
                    @endif
                    @if ($actions['type'] === 'sorts')
                        @php
                            $incantation = $actions['incantation'];
                        @endphp
                        <div class="texte-attenue marge-bas-2" style="font-size: 0.8rem;">
                            {{ $personnage->classe->nom }}
                            incante avec
                            {{ $incantation['caracteristique'] }}.
                        </div>
                        <div class="fiche-ligne">
                            <span>Jet de sauvegarde de tes sorts</span>
                            <span class="valeur">
                                {{ $incantation['dd'] }}
                            </span>
                        </div>
                        <div class="fiche-ligne">
                            <span>Bonus d'attaque de sorts</span>
                            <span class="valeur">
                                +{{ $incantation['bonusAttaque'] }}
                            </span>
                        </div>
                    @else
                        @forelse($actions['attaques'] as $attaque)
                            @php
                                $bonusChaine =
                                    $attaque['bonusAttaque'] >= 0
                                        ? '+' . $attaque['bonusAttaque']
                                        : $attaque['bonusAttaque'];
                            @endphp
                            <div class="fiche-ligne">
                                <span>
                                    {{ $attaque['nom'] }}
                                    <span class="texte-attenue">
                                        ({{ $attaque['type'] }})
                                    </span>
                                </span>
                                <span class="valeur">
                                    {{ $bonusChaine }} pour toucher,
                                    {{ $attaque['degats'] }} dégâts
                                </span>
                            </div>
                        @empty
                            <div class="texte-attenue" style="font-size: 0.85rem;">
                                Aucune arme reconnue dans l'équipement porté.
                            </div>
                        @endforelse
                    @endif
                </div>
                {{-- Historique & Contexte --}}
                <div
                    style="
                   background: var(--panneau);
                   border: 1px solid var(--or-bordure);
                   border-radius: 12px;
                   padding: 1rem;
                   margin-bottom: 1rem;
               ">
                    <div
                        style="
                       font-size: 0.85rem;
                       font-weight: 600;
                       color: var(--or);
                       margin-bottom: 0.5rem;
                   ">
                        Historique &amp; Contexte
                    </div>
                    @if ($personnage->historique)
                        <div style="font-size: 0.8rem; color: var(--texte-secondaire);">
                            <strong>Historique:</strong>
                            {{ $personnage->historique->nom }}
                        </div>
                    @endif
                    @if ($personnage->ideal)
                        <div style="font-size: 0.8rem; color: var(--texte-secondaire);">
                            <strong>Idéal:</strong>
                            {{ $personnage->ideal->valeur }}
                        </div>
                    @endif
                    @if ($personnage->defaut)
                        <div style="font-size: 0.8rem; color: var(--texte-secondaire);">
                            <strong>Défaut:</strong>
                            {{ $personnage->defaut->valeur }}
                        </div>
                    @endif
                    @if ($personnage->attachement)
                        <div style="font-size: 0.8rem; color: var(--texte-secondaire);">
                            <strong>Attachement:</strong>
                            {{ $personnage->attachement->valeur }}
                        </div>
                    @endif
                    @if ($personnage->personnalite)
                        <div style="font-size: 0.8rem; color: var(--texte-secondaire);">
                            <strong>Personnalité:</strong>
                            {{ $personnage->personnalite->valeur }}
                        </div>
                    @endif
                </div>
                {{-- Inventaire --}}
                <div class="fiche-boite">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span class="fiche-boite-titre" style="margin-bottom: 0;">Inventaire</span>
                        <a href="{{ route('personnages.inventaire.index', $personnage) }}" class="bouton bouton-secondaire bouton-petit">Tous voir</a>
                    </div>
                    @if($personnage->objets->count() > 0)
                        <div class="texte-attenue" style="font-size: 0.85rem;">
                            {{ $personnage->objets->map(fn($objet) => $objet->nom . ($objet->pivot->quantite > 1 ? ' x' . $objet->pivot->quantite : '') . ($objet->pivot->quantite_equipee > 0 ? ' (équipé' . ($objet->pivot->quantite_equipee > 1 ? ' x' . $objet->pivot->quantite_equipee : '') . ')' : ''))->implode(', ') }}
                        </div>
                    @else
                        <div class="texte-attenue" style="font-size: 0.85rem;">Aucun équipement enregistré.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('partials.modal-suppression-personnage')
@endsection
