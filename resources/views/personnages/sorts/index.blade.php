@extends('layouts.app')
@section('title', $personnage->nom . ' — Sorts connus')

@push('styles')
    <style>
        .page-sorts {
            max-width: 880px;
            margin: 0 auto;
            color: var(--or);
        }

        .maquette-entete {
            background: var(--panneau);
            border: 1px solid var(--or-bordure);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        /* Use same 3-column grid as levels so columns align */
        .emplacements-sorts {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: start;
        }

        .boite-emplacement {
            flex: 1;
            border: 1px dashed var(--or-bordure);
            padding: 0.75rem;
            text-align: center;
            border-radius: 6px;
            background: var(--or-halo-faible);
            color: var(--or-doux);
        }

        .barre-lancement {
            background: var(--panneau);
            border: 1px solid var(--or-bordure);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        /* cast-grid uses the same 3-column template to align with slots and levels */
        .grille-lancement {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            align-items: center;
        }

        .element-lancement {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--or-doux);
            font-weight: 600;
        }

        .niveaux {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .carte-niveau {
            background: var(--panneau);
            border: 1px solid var(--or-bordure);
            padding: 0.75rem;
            border-radius: 8px;
            min-height: 300px;
            color: var(--or);
        }

        .carte-niveau .emplacements {
            margin-bottom: 0.75rem;
            color: var(--or-doux);
            font-weight: 700;
        }

        .liste-sorts {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .liste-sorts li {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .liste-sorts-entete .etiquette-colonne {
            display: inline-block;
            width: 14px;
            margin-right: 6px;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--or-doux);
            line-height: 1;
        }

        /* Make spell name clearly interactive for hover */
        .sort-survol {
            cursor: pointer;
            text-decoration: underline dotted var(--or-ligne-forte);
        }
        .sort-survol:hover {
            color: var(--or-doux);
            text-decoration-color: var(--or-doux);
        }

        .sort-detail {
            display: none;
            margin-top: 0.5rem;
            background: var(--panneau);
            border: 1px solid var(--or-bordure);
            padding: 0.6rem;
            border-radius: 6px;
            color: var(--or);
            font-size: 0.9rem;
        }

        .sort-detail .meta { color: var(--or-doux); font-weight:700; margin-bottom:0.35rem }

        /* Checkbox styled as yellow square */
        .case-preparation {
            position: relative;
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 6px;
            vertical-align: middle;
        }

        .case-preparation input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 14px;
            height: 14px;
            margin: 0;
            cursor: pointer;
        }

        .case-preparation .boite {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            background: var(--or-halo-moyen);
            border: 1px solid var(--or-bordure);
            display: inline-block;
        }

        .case-preparation input[type="checkbox"]:checked + .boite {
            background: var(--or-vif);
            border-color: var(--or-vif);
        }

        .case-preparation input[type="checkbox"]:disabled + .boite {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .actions-bas {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            gap: 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-sorts">
        <h2 style="text-align:center; margin-bottom: 0.5rem; color: var(--or);">Sorts</h2>

        <div class="maquette-entete">
            @php
                // Caractéristique, modificateur et limite calculés par SortService.
                $abiliteScore = $scoreCaracteristique;
                $modificateur = $modificateurIncantation;
                $bonusMaitrise = 2 + intdiv(max(1, (int) $personnage->niveau) - 1, 4);
                $ddSauvegarde = 8 + $bonusMaitrise + $modificateur;
                $modificateurSort = $modificateur + $bonusMaitrise;
                $limiteSortAffichee = $limiteSortsPrepares;
            @endphp
            @php $estMagicien = ($personnage->classe?->nom ?? '') === 'Magicien'; @endphp

                <div class="emplacements-sorts">
                @php
                    $caracteristiqueAffichee = $abiliteMagie ?? ($caracteristique ? ucfirst($caracteristique) : '—');
                @endphp
                <div class="boite-emplacement">Abilité sort: <strong>{{ $caracteristiqueAffichee }}</strong>
                    @if($abiliteScore !== null) <span>({{ $abiliteScore }})</span>@endif
                </div>
                <div class="boite-emplacement">Sauvegarde sort: <strong>{{ $ddSauvegarde }}</strong></div>
                <div class="boite-emplacement">Modificateur sort: <strong>{{ $modificateurSort >= 0 ? '+' . $modificateurSort : $modificateurSort }}</strong></div>
            </div>
            <div style="font-weight:700; color:var(--or-doux);">
                Limite tour de magie : {{ $limiteTour ?? '—' }}
                <br>
                Limite sort : {{ $limiteSortAffichee }}
            </div>
            {{-- Tooltip placé ici entre la boite des bonus et le tour de magie --}}
            <div id="infobulle-sort" class="infobulle-sort" aria-hidden="true"></div>
        </div>

        <div class="barre-lancement">
            <div style="text-align:center; font-weight:700; margin-bottom:0.6rem; color:var(--or)">Tour magie</div>
            <div class="grille-lancement">
                @php $toursDeMagie = $niveau0 ?? collect(); @endphp
                @if($toursDeMagie->isEmpty())
                    @for($i=0;$i<6;$i++)
                        <div class="element-lancement">
                            <div class="case-carree"></div>
                            <div>—</div>
                        </div>
                    @endfor
                @else
                    @foreach($toursDeMagie as $c)
                        <div class="element-lancement">
                            <label class="case-preparation">
                                    @php $p = (int) ($c->prepare ?? 0); $idLienSort = $c->personnage_sort_id ?? null; @endphp
                                        <input type="checkbox"
                                            class="bascule-preparation"
                                            data-personnage-sort-id="{{ $idLienSort }}"
                                            data-sort-level="{{ $c->niveau ?? 0 }}"
                                            value="1"
                                            @if($p === 1) checked @endif
                                            @if($p === 2) checked disabled data-fixed="1" @endif
                                            data-sort-id="{{ $c->sort->id ?? '' }}">
                                <span class="boite"></span>
                            </label>
                            <div style="display:flex; flex-direction:column;">
                                  <span class="sort-survol"
                                      data-name="{{ $c->sort->Nom ?? 'Sort' }}"
                                      data-niveau="{{ $c->sort->Niveau ?? '' }}"
                                      data-ecole="{{ $c->sort->Ecole ?? '' }}"
                                      data-description="{{ e($c->sort->Description ?? '') }}"
                                      data-temps="{{ $c->sort->TempsActivation ?? '' }}"
                                      data-duree="{{ $c->sort->Duree ?? '' }}"
                                      data-distance="{{ $c->sort->Distance ?? '' }}"
                                      data-composants="{{ $c->sort->Composants ?? '' }}"
                                      data-att-sauv="{{ $c->sort->AttaqueSauvegarde ?? '' }}"
                                  >{{ $c->sort->Nom ?? 'Sort' }}</span>
                                  <div class="sort-detail" aria-hidden="true"></div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="niveaux">
            @foreach($niveaux as $niveau)
                @php $nombreEmplacements = (int) ($emplacements['Niveau' . $niveau] ?? 0); @endphp
                @if($nombreEmplacements > 0)
                <div class="carte-niveau">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <div style="font-weight:800;">Niveau {{ $niveau }}</div>
                        <div class="emplacements">Emplacement: {{ $nombreEmplacements }}</div>
                    </div>

                    <ul class="liste-sorts">
                        <li class="liste-sorts-entete" aria-hidden="true">
                            @if($estMagicien)
                                <span class="etiquette-colonne" title="Grimoire">G</span>
                            @endif
                            <span class="etiquette-colonne" title="Préparé">P</span>
                        </li>
                        @foreach(($disponibles ?? collect())->filter(fn($s) => (int) ($s->niveau ?? 1) === (int) $niveau) as $element)
                            <li>
                                        @php $p = (int) ($element->prepare ?? 0); $idLienSort = $element->personnage_sort_id ?? null; $dansGrimoire = in_array($p, [1, 2], true); @endphp
                                        @if($estMagicien)
                                            <label class="case-preparation" title="Inscrit dans le grimoire">
                                                <input type="checkbox"
                                                    class="bascule-grimoire"
                                                    data-personnage-sort-id="{{ $idLienSort }}"
                                                    data-sort-level="{{ $element->niveau ?? 1 }}"
                                                    data-sort-id="{{ $element->sort->id ?? '' }}"
                                                    value="2"
                                                    @if($dansGrimoire) checked @endif>
                                                <span class="boite"></span>
                                            </label>
                                        @endif

                                        <label class="case-preparation">
                                         <input type="checkbox"
                                             class="bascule-preparation"
                                             data-personnage-sort-id="{{ $idLienSort }}"
                                             data-sort-level="{{ $element->niveau ?? 1 }}"
                                             value="1"
                                             @if($estMagicien)
                                                 @if($p === 1) checked @endif
                                                 @if(! $dansGrimoire) disabled @endif
                                             @else
                                                 @if($p === 1) checked @endif
                                                 @if($p === 2) checked disabled data-fixed="1" @endif
                                             @endif
                                             data-sort-id="{{ $element->sort->id ?? '' }}">
                                            <span class="boite"></span>
                                        </label>

                                <div style="color:var(--or); display:flex; gap:0.5rem; align-items:center;">
                                    <span class="sort-survol"
                                          data-name="{{ $element->sort->Nom ?? 'Sort' }}"
                                          data-niveau="{{ $element->sort->Niveau ?? '' }}"
                                          data-ecole="{{ $element->sort->Ecole ?? '' }}"
                                          data-description="{{ e($element->sort->Description ?? '') }}"
                                          data-temps="{{ $element->sort->TempsActivation ?? '' }}"
                                          data-duree="{{ $element->sort->Duree ?? '' }}"
                                          data-distance="{{ $element->sort->Distance ?? '' }}"
                                          data-composants="{{ $element->sort->Composants ?? '' }}"
                                          data-att-sauv="{{ $element->sort->AttaqueSauvegarde ?? '' }}"
                                    >{{ $element->sort->Nom ?? 'Sort' }}</span>
                                    <div class="sort-detail" aria-hidden="true"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                    @endif
            @endforeach
        </div>

        <div class="actions-bas">
            <a href="{{ route('personnages.show', $personnage) }}" class="bouton bouton-secondaire bouton-petit">← Retour à la fiche</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function(){
            function enregistrerPreparation(personnageId, personnageSortId, sortId, valeur) {
                const jeton = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                if (personnageSortId) {
                    const url = '/personnages/' + personnageId + '/sorts/' + personnageSortId;
                    return fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': jeton
                        },
                        body: JSON.stringify({ prepare: valeur })
                    }).then(r => r.json().then(json => { if (!r.ok) throw json; return json; }));
                }

                // create new PersonnageSort
                const url = '/personnages/' + personnageId + '/sorts';
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': jeton
                    },
                    body: JSON.stringify({ sort_id: sortId, prepare: valeur })
                }).then(r => r.json().then(json => { if (!r.ok) throw json; return json; }));
            }

                const personnageId = '{{ $personnage->id }}';
                const estMagicien = {{ $estMagicien ? 'true' : 'false' }};
                const limiteSort = {{ $limiteSort ?? 'null' }};
                const limiteTour = {{ $limiteTour ?? 'null' }};

            let enregistrementEnCours = false;
            document.addEventListener('change', function(e) {
                const el = e.target;
                // Spellbook toggle (magician only)
                if (el.classList.contains('bascule-grimoire')) {
                    if (el.disabled || enregistrementEnCours) return;
                    const idLienSort = el.dataset.personnageSortId;
                    const sortId = el.dataset.sortId;
                    const niveauSort = parseInt(el.dataset.sortLevel || '0', 10);
                    // do not show spellbook for cantrips (level 0) — view ensures this
                    const valeur = el.checked ? 2 : 0;

                    enregistrementEnCours = true;
                    el.disabled = true;
                    enregistrerPreparation(personnageId, idLienSort, sortId, valeur)
                        .then((json) => {
                            if (json && json.personnage_sort_id) {
                                // attach id to all related toggles for this sort
                                document.querySelectorAll('[data-sort-id="' + sortId + '"]').forEach(function(i){ i.dataset.personnageSortId = json.personnage_sort_id; });
                            }

                            // grimoire coché => préparation possible ; décoché => préparation retirée
                            let conteneur = el.closest('li') || el.parentElement;
                            const prepare = conteneur && conteneur.querySelector('.bascule-preparation');
                            if (prepare) {
                                prepare.checked = false;
                                prepare.disabled = valeur !== 2;
                            }
                            el.disabled = false;
                            enregistrementEnCours = false;
                        })
                        .catch((erreur) => {
                            el.checked = !el.checked;
                            el.disabled = false;
                            enregistrementEnCours = false;
                            const msg = erreur && erreur.message ? erreur.message : 'Erreur lors de la sauvegarde';
                            alert(msg);
                        });
                    return;
                }

                if (!el.classList.contains('bascule-preparation')) return;
                if (el.disabled || enregistrementEnCours) return;

                const idLienSort = el.dataset.personnageSortId;
                const sortId = el.dataset.sortId;
                const niveauSort = parseInt(el.dataset.sortLevel || '0', 10);
                const estCoche = el.checked ? 1 : 0;
                let enregistrerValeur = estCoche;

                // Magicien : le sort doit être dans le grimoire ; décocher la préparation le laisse dans le grimoire (2)
                let grimoire = null;
                if (estMagicien && niveauSort > 0) {
                    const conteneur = el.closest('li') || el.parentElement;
                    grimoire = conteneur && conteneur.querySelector('.bascule-grimoire');
                    if (grimoire && !grimoire.checked) {
                        alert('Ce sort doit être inscrit dans le grimoire pour être préparé.');
                        el.checked = false;
                        return;
                    }
                    if (grimoire && estCoche === 0) {
                        enregistrerValeur = 2;
                    }
                }

                // Client-side quick check: enforce limiteTour for level 0, and combined limiteSort for levels 1-3
                if (estCoche === 1) {
                    if (niveauSort === 0 && limiteTour !== null) {
                        const courant = Array.from(document.querySelectorAll('.bascule-preparation'))
                            .filter(i => !i.disabled)
                            .filter(i => parseInt(i.dataset.sortLevel||'0',10) === 0)
                            .filter(i => i.checked)
                            .length;
                        if (courant > limiteTour) {
                            alert('Limite de sorts cantrips préparés atteinte (client).');
                            el.checked = false;
                            return;
                        }
                    }

                    if ([1,2,3].includes(niveauSort) && limiteSort !== null) {
                        const courant = Array.from(document.querySelectorAll('.bascule-preparation'))
                            .filter(i => !i.disabled)
                            .filter(i => [1,2,3].includes(parseInt(i.dataset.sortLevel||'0',10)))
                            .filter(i => i.checked)
                            .length;
                        if (courant > limiteSort) {
                            alert('Limite de sorts préparés (niveaux 1-3) atteinte (client).');
                            el.checked = false;
                            return;
                        }
                    }
                }

                // Optimistic: disable while saving
                enregistrementEnCours = true;
                el.disabled = true;
                if (grimoire) grimoire.disabled = true;

                enregistrerPreparation(personnageId, idLienSort, sortId, enregistrerValeur)
                    .then((json) => {
                                // If a new record was created, store its id on the element
                                if (json && json.personnage_sort_id) {
                                    el.dataset.personnageSortId = json.personnage_sort_id;
                                    if (grimoire) grimoire.dataset.personnageSortId = json.personnage_sort_id;
                                }
                                if (grimoire) grimoire.disabled = false;
                        el.disabled = false;
                        enregistrementEnCours = false;
                    })
                    .catch((erreur) => {
                        // revert on error
                        el.checked = !el.checked;
                        el.disabled = false;
                        if (grimoire) grimoire.disabled = false;
                        enregistrementEnCours = false;
                        const msg = erreur && erreur.message ? erreur.message : 'Erreur lors de la sauvegarde';
                        alert(msg);
                    });
            });

            // Tooltip handlers
            const infobulle = document.getElementById('infobulle-sort');

            function esc(s) {
                return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            }

            function survolerSort(e) {
                const t = e.currentTarget || e.target;
                const nomSort = esc(t.dataset.name || '');
                const niveau = (esc(t.dataset.niveau || '') === "0") ? "Tour de magie" : esc(t.dataset.niveau || '');
                const ecole = esc(t.dataset.ecole || '');
                const temps = esc(t.dataset.temps || '');
                const duree = esc(t.dataset.duree || '');
                const distance = esc(t.dataset.distance || '');
                let desc = esc(t.dataset.description || '');
                desc = desc.replace(/\\n+/g, ' ').trim();


                infobulle.innerHTML = '<div class="titre">' + nomSort + '</div>' +
                    '<div class="meta">Niveau: ' + niveau + (ecole ? ' • ' + ecole : '') + '</div>' +
                    '<div class="meta">Temps: ' + (temps || '') + '</div>' +
                    '<div class="meta">Durée: ' + (duree || '') + '</div>' +
                    '<div class="meta">Distance: ' + (distance || '') + '</div>' +
                    '<div class="desc">' + (desc || '') + '</div>';

                // show tooltip and position it centered under the hovered name
                infobulle.classList.add('visible');
                setTimeout(() => {
                    const rectInfobulle = infobulle.getBoundingClientRect();
                    const rect = t.getBoundingClientRect();
                    // center horizontally relative to hovered element
                    let gauche = rect.left + (rect.width - rectInfobulle.width) / 2 + window.scrollX;
                    gauche = Math.max(8, Math.min(gauche, window.innerWidth - rectInfobulle.width - 8));
                    // position just below the hovered element
                    let haut = rect.bottom + 8 + window.scrollY;
                    // if not enough space below, place above
                    if (haut + rectInfobulle.height > window.innerHeight + window.scrollY - 8) {
                        haut = rect.top - rectInfobulle.height - 8 + window.scrollY;
                    }
                    infobulle.style.left = gauche + 'px';
                    infobulle.style.top = haut + 'px';
                }, 0);
            }

            function quitterSort() {
                infobulle.classList.remove('visible');
                // clear after animation
                setTimeout(() => { infobulle.innerHTML = ''; }, 180);
            }

            document.querySelectorAll('.sort-survol').forEach(function(s){
                s.addEventListener('mouseenter', survolerSort);
                s.addEventListener('mouseleave', quitterSort);
            });
        })();
    </script>
    <style>
        /* Tooltip styled like other page boxes (maquette-header / level-card) */
        .infobulle-sort {
            position: absolute;
            background: var(--panneau-opaque);
            border: 1px solid var(--or-bordure);
            color: var(--or);
            padding: 0.75rem;
            border-radius: 8px;
            max-width: 360px;
            z-index: 9999;
            display: block; /* keep in DOM; visibility controlled via opacity */
            opacity: 0;
            transform: translateY(6px);
            transition: opacity 150ms ease, transform 150ms ease;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .infobulle-sort.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .infobulle-sort .titre {
            font-weight: 800;
            margin-bottom: 0.35rem;
            color: var(--or);
        }

        .infobulle-sort .meta {
            font-size: 0.85rem;
            color: var(--or-doux);
            margin-bottom: 0.5rem;
        }

        .infobulle-sort .desc {
            color: var(--or);
            max-height: none; /* remove scroll */
            overflow: visible;
            white-space: normal;
        }
    </style>

@endpush
