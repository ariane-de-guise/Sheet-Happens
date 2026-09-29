@extends('layouts.app')
@section('titre', $personnage->nom . ' - Sheet Happens')
@section('contenu')
    <div class="fiche-conteneur fiche-disposition">
        <!-- Left Sidebar -->
        <div class="carte carte-ajustee">
            <div class="texte-or marge-bas-3 fiche-marque">
                Sheet Happens
            </div>
            <div class="fiche-navigation">
                <a href="{{ route('personnages.index') }}" class="texte-attenue fiche-lien-retour">
                    ← Retour à la sélection de personnages
                </a>
            </div>
        </div>

        <div>
            <div class="carte carte-ajustee">

                <div class="fiche-entete-principal">
                    <!-- Avatar, CA et Niveau -->
                    <div class="fiche-avatar">
                        <form class="formulaire-sans-marge" action="{{ route('personnages.photo', $personnage) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <label for="champ-photo-personnage" class="avatar-etiquette" title="Changer la photo">
                                <img src="{{ $personnage->photo ? \Illuminate\Support\Facades\Storage::url($personnage->photo) : asset('images/logo_rmb.png') }}"
                                     alt="Portrait de {{ $personnage->nom }}"
                                     class="{{ $personnage->photo ? 'avatar-photo' : 'avatar-logo-defaut' }}">
                                <span class="avatar-voile">Changer<br>la photo</span>
                            </label>
                            <input class="champ-masque" type="file" id="champ-photo-personnage" name="photo" accept="image/*" onchange="this.form.submit()">
                        </form>

                        <div class="badge-ca">CA<br>{{ $classeArmure }}</div>

                        <div class="fiche-badge-niveau">
                            Niveau<br>{{ $personnage->niveau }}
                        </div>
                    </div>

                    <div class="fiche-identite">
                        <div class="fiche-identite-ligne">
                            <div>
                                <div class="fiche-nom">
                                    {{ $personnage->nom }}
                                </div>
                                <div class="fiche-sous-titre">
                                    {{ $personnage->classe->nom }} - {{ $personnage->race->nom }}
                                    @if ($personnage->sousRace)
                                        {{ $personnage->sousRace->nom }}
                                    @endif
                                </div>
                                @if ($personnage->alignement)
                                    <div class="fiche-alignement">
                                        {{ $personnage->alignement }}
                                    </div>
                                @endif
                            </div>
                            <!-- Actions -->
                            <div class="fiche-actions">
                                <!-- Niveau maximum atteint -->
                                @if ($personnage->niveau >= 5)
                                    <span class="bouton bouton-petit badge-niveau-max">
                                        Niveau maximum
                                    </span>
                                @endif
                                <!-- Modifier -->
                                <button type="button" class="bouton bouton-secondaire bouton-petit bouton-centre"
                                        onclick="window.location.href='{{ route('personnages.edit', $personnage) }}'">
                                    Modifier
                                </button>
                                <!-- Supprimer -->
                                <form class="formulaire-sans-marge" id="formulaire-supprimer-personnage"
                                      action="{{ route('personnages.destroy', $personnage) }}" method="POST">
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
                        <div class="fiche-xp-entete">
                            <span>Expériences</span>
                            <span>
                                @if ($seuilExperience !== null)
                                    {{ $personnage->experience }}/{{ $seuilExperience }}
                                @else
                                    {{ $personnage->experience }} (niveau maximum)
                                @endif
                            </span>
                        </div>
                        <div class="fiche-xp-barre">
                            <div class="fiche-xp-remplissage" style="width: {{ $seuilExperience ? min(100, (int) floor($personnage->experience / $seuilExperience * 100)) : 100 }}%;">
                            </div>
                        </div>
                        @if ($peutMonterDeNiveau)
                            <a class="fiche-xp-lien-montee" href="{{ route('personnages.montee-niveau', $personnage) }}">
                                ✦ Assez d'expérience pour passer au niveau {{ $personnage->niveau + 1 }} — monter de niveau
                            </a>
                        @endif
                        @if ($seuilExperience !== null)
                            <form class="fiche-xp-formulaire" action="{{ route('personnages.experience.ajouter', $personnage) }}" method="POST">
                                @csrf
                                <input class="fiche-xp-champ" type="number" name="experience" min="1" max="1000000" required
                                       placeholder="Points d'XP">
                                <button type="submit" class="bouton bouton-petit bouton-mise-en-avant">
                                    ✦ Ajouter de l'expérience
                                </button>
                            </form>
                            @error('experience')
                            <div class="message-erreur-champ">
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
                        <div class="fiche-ligne"><span>Points de vie</span><span class="valeur">{{ $personnage->point_de_vie_actuel ?? $personnage->point_de_vie }} / {{ $personnage->point_de_vie }}</span></div>
                        <form action="{{ route('personnages.points-de-vie', $personnage) }}" method="POST" class="pv-formulaire">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="montant" min="1" max="1000" required placeholder="PV" aria-label="Nombre de points de vie" class="formulaire-saisie-petit">
                            <button type="submit" name="type" value="degats" class="bouton bouton-petit bouton-danger">Dégâts</button>
                            <button type="submit" name="type" value="soins" class="bouton bouton-petit bouton-secondaire">Soins</button>
                        </form>
                        @error('montant')
                            <div class="message-erreur-champ">{{ $message }}</div>
                        @enderror
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
                    <div class="fiche-competences-colonnes">
                        @foreach ($competences as $competence)
                            @php
                                $bonusChaine =
                                    $competence['bonus'] >= 0 ? '+' . $competence['bonus'] : $competence['bonus'];
                            @endphp
                            <div class="fiche-ligne fiche-ligne-insecable">
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

                    <div class="fiche-langues">
                        <div class="fiche-langues-titre">
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
                    <div class="fiche-boite-entete">
                        <span class="fiche-boite-titre sans-marge-bas">
                            Traits
                        </span>
                        <div class="fiche-boite-actions">
                            <a href="{{ route('personnages.traits', $personnage) }}" class="bouton bouton-secondaire bouton-petit">Tous voir</a>
                        </div>
                    </div>
                </div>
                {{-- Capacités de classe et dons ; la description s'affiche au survol --}}
                @if ($personnage->capacites->isNotEmpty() || $personnage->dons->isNotEmpty())
                    <div class="fiche-grille-capacites">
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
                                <div class="texte-attenue texte-petit">
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
                                <div class="texte-attenue texte-petit">
                                    Aucun
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
                {{-- Actions : sorts ou attaques selon la classe --}}
                <div class="fiche-boite marge-bas-3">
                    <div class="fiche-boite-entete">
                        <span class="fiche-boite-titre sans-marge-bas">
                            Actions
                        </span>
                        @if ($aDesSorts)
                            <a href="{{ route('personnages.sorts.index', $personnage) }}" class="bouton bouton-secondaire bouton-petit">Sorts connus</a>
                        @endif
                    </div>
                    @if ($personnage->sousClasse)
                        <div class="texte-attenue marge-bas-2 texte-petit">
                            Sous-classe : {{ $personnage->sousClasse->nom }}
                        </div>
                    @endif
                    @if ($actions['type'] === 'sorts')
                        @php
                            $incantation = $actions['incantation'];
                        @endphp
                        <div class="texte-attenue marge-bas-2 texte-petit">
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
                            <div class="texte-attenue texte-moyen">
                                Aucune arme reconnue dans l'équipement porté.
                            </div>
                        @endforelse
                    @endif
                </div>
                {{-- Historique & Contexte --}}
                <div class="fiche-boite-historique">
                    <div class="fiche-boite-historique-titre">
                        Historique &amp; Contexte
                    </div>
                    @if ($personnage->historique)
                        <div class="fiche-detail">
                            <strong>Historique:</strong>
                            {{ $personnage->historique->nom }}
                        </div>
                    @endif
                    @if ($personnage->ideal)
                        <div class="fiche-detail">
                            <strong>Idéal:</strong>
                            {{ $personnage->ideal->valeur }}
                        </div>
                    @endif
                    @if ($personnage->defaut)
                        <div class="fiche-detail">
                            <strong>Défaut:</strong>
                            {{ $personnage->defaut->valeur }}
                        </div>
                    @endif
                    @if ($personnage->attachement)
                        <div class="fiche-detail">
                            <strong>Attachement:</strong>
                            {{ $personnage->attachement->valeur }}
                        </div>
                    @endif
                    @if ($personnage->personnalite)
                        <div class="fiche-detail">
                            <strong>Personnalité:</strong>
                            {{ $personnage->personnalite->valeur }}
                        </div>
                    @endif
                </div>
                {{-- Inventaire --}}
                <div class="fiche-boite">
                    <div class="fiche-boite-entete">
                        <span class="fiche-boite-titre sans-marge-bas">Inventaire</span>
                        <a href="{{ route('personnages.inventaire.index', $personnage) }}" class="bouton bouton-secondaire bouton-petit">Tous voir</a>
                    </div>
                    @if($personnage->objets->count() > 0)
                        <div class="texte-attenue texte-moyen">
                            {{ $personnage->objets->map(fn($objet) => $objet->nom . ($objet->pivot->quantite > 1 ? ' x' . $objet->pivot->quantite : '') . ($objet->pivot->quantite_equipee > 0 ? ' (équipé' . ($objet->pivot->quantite_equipee > 1 ? ' x' . $objet->pivot->quantite_equipee : '') . ')' : ''))->implode(', ') }}
                        </div>
                    @else
                        <div class="texte-attenue texte-moyen">Aucun équipement enregistré.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('partials.modal-suppression-personnage')
@endsection
