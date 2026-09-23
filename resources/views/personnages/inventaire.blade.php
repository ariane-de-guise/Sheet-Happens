@extends('layouts.app')

@section('title', 'Inventaire de ' . $personnage->nom)

@push('styles')
    <style>
        .inventaire-section {
            background: var(--panneau);
            border: 1px solid var(--or-bordure);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .inventaire-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--or);
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--or-ligne-moyenne);
        }

        .objet-ligne {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid var(--or-ligne-faible);
            flex-wrap: wrap;
        }

        .objet-ligne:last-child {
            border-bottom: none;
        }

        .objet-info {
            flex: 1;
            min-width: 200px;
        }

        .objet-nom {
            color: var(--or-doux);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .objet-nom .badge-equipe {
            color: var(--or);
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 0.4rem;
        }

        .objet-meta {
            color: var(--texte-secondaire);
            font-size: 0.75rem;
        }

        .objet-actions {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .quantite-form {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .quantite-form .valeur-quantite {
            color: var(--or-doux);
            font-weight: 600;
            min-width: 1.5rem;
            text-align: center;
            display: inline-block;
        }

        .btn-mini {
            padding: 0.15rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.4;
        }

        .objet-description {
            color: var(--texte-secondaire);
            font-size: 0.8rem;
            margin-top: 0.5rem;
            font-style: italic;
        }

        .badge {
            display: inline-block;
            padding: 0.1rem 0.5rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-magique {
            background: var(--primaire-halo);
            color: var(--texte);
            border: 1px solid var(--primaire);
        }

        .objet-bonus {
            color: var(--or);
            font-weight: 600;
        }

        .temps-restant {
            color: var(--texte-secondaire);
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
        }
    </style>
@endpush

@section('content')
    <div class="sheet-container" style="max-width: 900px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <div class="text-gold" style="font-size: 1.3rem; font-weight: 700;">Inventaire de {{ $personnage->nom }}</div>
                <div class="text-muted" style="font-size: 0.85rem;">{{ $personnage->classe->nom }} - {{ $personnage->race->nom }} · Classe d'Armure {{ $classeArmure }}</div>
            </div>
            <a href="{{ route('personnages.show', $personnage) }}" class="btn btn-secondary btn-small">← Retour à la fiche</a>
        </div>

        @if(session('success'))
            <div class="inventaire-section" style="border-color: var(--alerte-succes-bordure);">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="inventaire-section" style="border-color: var(--alerte-erreur-bordure);">
                @foreach($errors->all() as $erreur)
                    <div>{{ $erreur }}</div>
                @endforeach
            </div>
        @endif

        {{-- Effets actifs : bonus temporaires de potions utilisées. En minutes,
             expirent tout seuls (horodatage réel) ; en tours, décomptés à la
             main (pas de correspondance fiable avec le temps réel en combat). --}}
        @if($personnage->effetsActifs->count() > 0)
            <div class="inventaire-section">
                <div class="inventaire-section-title">Effets actifs</div>
                @foreach($personnage->effetsActifs as $effet)
                    @php
                        $texteBonus = collect($effet->bonus)->map(fn($v, $k) => ($v >= 0 ? '+' : '').$v.' '.str_replace('_', ' ', $k))->implode(', ');
                    @endphp
                    <div class="objet-ligne">
                        <div class="objet-info">
                            <div class="objet-nom">{{ $effet->nom }}</div>
                            <div class="objet-meta objet-bonus">{{ $texteBonus }}</div>
                        </div>
                        @if($effet->unite === 'tours')
                            <form action="{{ route('personnages.inventaire.effets.tours', [$personnage, $effet]) }}" method="POST" class="quantite-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="delta" value="-1">
                                <button type="submit" class="btn btn-secondary btn-mini">−</button>
                            </form>
                            <span class="valeur-quantite">{{ $effet->tours_restants }} tour(s)</span>
                            <form action="{{ route('personnages.inventaire.effets.tours', [$personnage, $effet]) }}" method="POST" class="quantite-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="delta" value="1">
                                <button type="submit" class="btn btn-secondary btn-mini">+</button>
                            </form>
                        @else
                            <div class="temps-restant" data-expire="{{ $effet->expire_a->toIso8601String() }}">…</div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Équipement porté : uniquement ce qui est réellement équipé --}}
        <div class="inventaire-section">
            <div class="inventaire-section-title">Équipement porté</div>
            @php $equipes = $personnage->objets->filter(fn($objet) => $objet->pivot->quantite_equipee > 0); @endphp
            @forelse($equipes as $objet)
                <div class="objet-ligne">
                    <div class="objet-info">
                        <div class="objet-nom">
                            {{ $objet->nom }}
                            @if($objet->pivot->quantite_equipee > 1)<span class="badge-equipe">×{{ $objet->pivot->quantite_equipee }}</span>@endif
                        </div>
                        @include('partials.objet-stats', ['objet' => $objet])
                    </div>
                    <form action="{{ route('personnages.inventaire.desequiper', [$personnage, $objet->pivot->id]) }}" method="POST" style="margin:0;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-secondary btn-mini">Déséquiper</button>
                    </form>
                </div>
            @empty
                <div class="text-muted" style="font-size: 0.9rem;">Aucun équipement porté pour le moment.</div>
            @endforelse
        </div>

        {{-- Inventaire réel --}}
        <div class="inventaire-section">
            <div class="inventaire-section-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Objets possédés</span>
                <a href="{{ route('personnages.inventaire.ajouter-page', $personnage) }}" class="btn btn-primary btn-small">+ Ajouter un objet</a>
            </div>
            @forelse($personnage->objets as $objet)
                <div class="objet-ligne">
                    <div class="objet-info">
                        <div class="objet-nom">
                            {{ $objet->nom }}
                            @if($objet->pivot->quantite_equipee > 0)<span class="badge-equipe">ÉQUIPÉ{{ $objet->pivot->quantite_equipee > 1 ? ' ×'.$objet->pivot->quantite_equipee : '' }}</span>@endif
                        </div>
                        @include('partials.objet-stats', ['objet' => $objet])
                    </div>
                    <div class="objet-actions">
                        <form action="{{ route('personnages.inventaire.quantite', [$personnage, $objet->pivot->id]) }}" method="POST" class="quantite-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="delta" value="-1">
                            <button type="submit" class="btn btn-secondary btn-mini" @disabled($objet->pivot->quantite <= 1)>−</button>
                        </form>
                        <span class="valeur-quantite">{{ $objet->pivot->quantite }}</span>
                        <form action="{{ route('personnages.inventaire.quantite', [$personnage, $objet->pivot->id]) }}" method="POST" class="quantite-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="delta" value="1">
                            <button type="submit" class="btn btn-secondary btn-mini">+</button>
                        </form>

                        @if(in_array($objet->type, ['arme', 'armure', 'bouclier']))
                            @if($objet->pivot->quantite_equipee > 0)
                                <form action="{{ route('personnages.inventaire.desequiper', [$personnage, $objet->pivot->id]) }}" method="POST" style="margin:0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary btn-mini">Déséquiper</button>
                                </form>
                            @endif
                            @if($objet->pivot->quantite_equipee < $objet->pivot->quantite)
                                <form action="{{ route('personnages.inventaire.equiper', [$personnage, $objet->pivot->id]) }}" method="POST" style="margin:0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-primary btn-mini">Équiper{{ $objet->pivot->quantite_equipee > 0 ? ' un de plus' : '' }}</button>
                                </form>
                            @endif
                        @endif

                        @if($objet->est_consommable)
                            <form action="{{ route('personnages.inventaire.utiliser', [$personnage, $objet->pivot->id]) }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-mini">Utiliser</button>
                            </form>
                        @endif

                        <form action="{{ route('personnages.inventaire.retirer', [$personnage, $objet->pivot->id]) }}" method="POST" style="margin:0;"
                              onsubmit="return confirm('Retirer {{ addslashes($objet->nom) }} de l\'inventaire ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-mini">Retirer</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-muted" style="font-size: 0.9rem;">Aucun objet dans l'inventaire.</div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function majComptesARebours() {
            const maintenant = Date.now();
            let unExpire = false;

            document.querySelectorAll('.temps-restant').forEach(el => {
                const restantMs = new Date(el.dataset.expire).getTime() - maintenant;

                if (restantMs <= 0) {
                    el.textContent = 'Expiré';
                    unExpire = true;
                    return;
                }

                const totalSecondes = Math.ceil(restantMs / 1000);
                const minutes = Math.floor(totalSecondes / 60);
                const secondes = totalSecondes % 60;
                el.textContent = minutes > 0
                    ? `${minutes} min ${String(secondes).padStart(2, '0')} s`
                    : `${secondes} s`;
            });

            // Un effet vient d'expirer : on recharge pour que le bonus disparaisse
            // réellement du calcul (jets de sauvegarde, CA, etc. sur la fiche) et
            // que la section "Effets actifs" se mette à jour.
            if (unExpire) {
                setTimeout(() => window.location.reload(), 1000);
            }
        }

        if (document.querySelector('.temps-restant')) {
            majComptesARebours();
            setInterval(majComptesARebours, 1000);
        }
    </script>
@endpush
