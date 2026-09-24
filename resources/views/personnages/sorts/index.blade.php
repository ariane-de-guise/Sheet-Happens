@extends('layouts.app')
@section('title', $personnage->nom . ' — Sorts connus')

@push('styles')
    <style>
        .sorts-page {
            max-width: 880px;
            margin: 0 auto;
            color: #e6b84d;
        }

        .maquette-header {
            background: rgba(25,10,50,0.95);
            border: 1px solid #6b2b8a;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        /* Use same 3-column grid as levels so columns align */
        .spell-slots {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: start;
        }

        .slot-box {
            flex: 1;
            border: 1px dashed rgba(107,43,138,0.6);
            padding: 0.75rem;
            text-align: center;
            border-radius: 6px;
            background: rgba(107,43,138,0.04);
            color: #f1d484;
        }

        .cast-bar {
            background: rgba(25,10,50,0.95);
            border: 1px solid rgba(107,43,138,0.6);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        /* cast-grid uses the same 3-column template to align with slots and levels */
        .cast-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            align-items: center;
        }

        .cast-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #f1d484;
            font-weight: 600;
        }

        .levels {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .level-card {
            background: rgba(25,10,50,0.95);
            border: 1px solid rgba(107,43,138,0.6);
            padding: 0.75rem;
            border-radius: 8px;
            min-height: 300px;
            color: #e6b84d;
        }

        .level-card .slots {
            margin-bottom: 0.75rem;
            color: #f1d484;
            font-weight: 700;
        }

        .spell-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .spell-list li {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .spell-list-header .col-label {
            display: inline-block;
            width: 14px;
            margin-right: 6px;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 800;
            color: #f1d484;
            line-height: 1;
        }

        /* Make spell name clearly interactive for hover */
        .spell-hover {
            cursor: pointer;
            text-decoration: underline dotted rgba(241,212,132,0.35);
        }
        .spell-hover:hover {
            color: #f1d484;
            text-decoration-color: #f1d484;
        }

        .spell-detail {
            display: none;
            margin-top: 0.5rem;
            background: rgba(25,10,50,0.9);
            border: 1px solid rgba(107,43,138,0.45);
            padding: 0.6rem;
            border-radius: 6px;
            color: #e6b84d;
            font-size: 0.9rem;
        }

        .spell-detail .meta { color: #f1d484; font-weight:700; margin-bottom:0.35rem }

        /* Checkbox styled as yellow square */
        .prepare-checkbox {
            position: relative;
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 6px;
            vertical-align: middle;
        }

        .prepare-checkbox input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 14px;
            height: 14px;
            margin: 0;
            cursor: pointer;
        }

        .prepare-checkbox .box {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            background: rgba(241,212,132,0.15);
            border: 1px solid rgba(241,212,132,0.35);
            display: inline-block;
        }

        .prepare-checkbox input[type="checkbox"]:checked + .box {
            background: #f1d484;
            border-color: #f1d484;
        }

        .prepare-checkbox input[type="checkbox"]:disabled + .box {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .bottom-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            gap: 1rem;
        }

        .btn-yellow {
            background: #f1d484;
            color: #1a0533;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
        }

        .btn-purple {
            background: #6b2b8a;
            color: #f1d484;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: none;
        }
    </style>
@endpush

@section('content')
    <div class="sorts-page">
        <h2 style="text-align:center; margin-bottom: 0.5rem; color: #e6b84d;">Sorts</h2>

        <div class="maquette-header">
            @php
                // Prefer ability provided by controller (could come from subclass limits)
                $abiliteMagieNom = $abiliteMagie ?? null;

                $incantationMap = [
                    'Clerc' => 'sagesse',
                    'Magicien' => 'intelligence',
                    'Druide' => 'sagesse',
                    'Barde' => 'charisme',
                    'Paladin' => 'charisme',
                    'Rôdeur' => 'sagesse',
                    'Ensorceleur' => 'charisme',
                    'Occultiste' => 'charisme',
                ];

                if (empty($abiliteMagieNom)) {
                    $nomClasse = $personnage->classe?->nom ?? null;
                    $abiliteMagieNom = $incantationMap[$nomClasse] ?? null;
                }

                $abiliteKey = $abiliteMagieNom ? strtolower($abiliteMagieNom) : null;
                $abiliteLabel = $abiliteMagieNom ? ucfirst($abiliteMagieNom) : null;
                $abiliteScore = $abiliteKey ? ($personnage->abilites?->{$abiliteKey} ?? null) : null;
                $modificateur = $abiliteScore !== null ? (int) floor(($abiliteScore - 10) / 2) : 0;
                $niveauPersonnage = (int) ($personnage->niveau ?? 0);
                $bonusMaitrise = 2 + intdiv(max(1, $niveauPersonnage) - 1, 4);
                $ddSauvegarde = 8 + $bonusMaitrise + $modificateur;

                // Modificateur sort = modificateur d'abilité + bonus de maîtrise
                $modificateurSort = $modificateur + $bonusMaitrise;

                // Limite sort : utiliser $limiteSort si présent, sinon niveau + modificateur d'abilité
                $limiteSortAffichee = isset($limiteSort) && $limiteSort !== null ? (int) $limiteSort : ($niveauPersonnage + $modificateur);
            @endphp
            @php $isMagicien = ($personnage->classe?->nom ?? '') === 'Magicien'; @endphp

                <div class="spell-slots">
                @php
                    $displayAbility = $abiliteMagie ?? $abiliteLabel ?? '—';
                @endphp
                <div class="slot-box">Abilité sort: <strong>{{ $displayAbility }}</strong>
                    @if($abiliteScore !== null) <span>({{ $abiliteScore }})</span>@endif
                </div>
                <div class="slot-box">Sauvegarde sort: <strong>{{ $ddSauvegarde }}</strong></div>
                <div class="slot-box">Modificateur sort: <strong>{{ $modificateurSort >= 0 ? '+' . $modificateurSort : $modificateurSort }}</strong></div>
            </div>
            <div style="font-weight:700; color:#f1d484;">
                Limite tour de magie : {{ $limiteTour ?? '—' }}
                <br>
                Limite sort : {{ $limiteSortAffichee }}
            </div>
            {{-- Tooltip placé ici entre la boite des bonus et le tour de magie --}}
            <div id="spell-tooltip" class="spell-tooltip" aria-hidden="true"></div>
        </div>

        <div class="cast-bar">
            <div style="text-align:center; font-weight:700; margin-bottom:0.6rem; color:#e6b84d">Tour magie</div>
            <div class="cast-grid">
                @php $cantrips = $level0 ?? collect(); @endphp
                @if($cantrips->isEmpty())
                    @for($i=0;$i<6;$i++)
                        <div class="cast-item">
                            <div class="checkbox-square"></div>
                            <div>—</div>
                        </div>
                    @endfor
                @else
                    @foreach($cantrips as $c)
                        <div class="cast-item">
                            <label class="prepare-checkbox">
                                    @php $p = (int) ($c->prepare ?? 0); $psId = $c->personnage_sort_id ?? null; @endphp
                                        <input type="checkbox"
                                            class="prepare-toggle"
                                            data-personnage-sort-id="{{ $psId }}"
                                            data-sort-level="{{ $c->niveau ?? 0 }}"
                                            value="1"
                                            @if($p === 1) checked @endif
                                            @if($p === 2) checked disabled data-fixed="1" @endif
                                            data-sort-id="{{ $c->sort->id ?? '' }}">
                                <span class="box"></span>
                            </label>
                            <div style="display:flex; flex-direction:column;">
                                  <span class="spell-hover"
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
                                  <div class="spell-detail" aria-hidden="true"></div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="levels">
            @foreach($levels as $niveau)
                @php $slotCount = (int) ($slots['Niveau' . $niveau] ?? 0); @endphp
                @if($slotCount > 0)
                <div class="level-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                        <div style="font-weight:800;">Niveau {{ $niveau }}</div>
                        <div class="slots">Emplacement: {{ $slotCount }}</div>
                    </div>

                    <ul class="spell-list">
                        <li class="spell-list-header" aria-hidden="true">
                            @if($isMagicien)
                                <span class="col-label" title="Grimoire">G</span>
                            @endif
                            <span class="col-label" title="Préparé">P</span>
                        </li>
                        @foreach(($available ?? collect())->filter(fn($s) => (int) ($s->niveau ?? 1) === (int) $niveau) as $item)
                            <li>
                                        @php $p = (int) ($item->prepare ?? 0); $psId = $item->personnage_sort_id ?? null; $inBook = in_array($p, [1, 2], true); @endphp
                                        @if($isMagicien)
                                            <label class="prepare-checkbox" title="Inscrit dans le grimoire">
                                                <input type="checkbox"
                                                    class="spellbook-toggle"
                                                    data-personnage-sort-id="{{ $psId }}"
                                                    data-sort-level="{{ $item->niveau ?? 1 }}"
                                                    data-sort-id="{{ $item->sort->id ?? '' }}"
                                                    value="2"
                                                    @if($inBook) checked @endif>
                                                <span class="box"></span>
                                            </label>
                                        @endif

                                        <label class="prepare-checkbox">
                                         <input type="checkbox"
                                             class="prepare-toggle"
                                             data-personnage-sort-id="{{ $psId }}"
                                             data-sort-level="{{ $item->niveau ?? 1 }}"
                                             value="1"
                                             @if($isMagicien)
                                                 @if($p === 1) checked @endif
                                                 @if(! $inBook) disabled @endif
                                             @else
                                                 @if($p === 1) checked @endif
                                                 @if($p === 2) checked disabled data-fixed="1" @endif
                                             @endif
                                             data-sort-id="{{ $item->sort->id ?? '' }}">
                                            <span class="box"></span>
                                        </label>

                                <div style="color:#e6b84d; display:flex; gap:0.5rem; align-items:center;">
                                    <span class="spell-hover"
                                          data-name="{{ $item->sort->Nom ?? 'Sort' }}"
                                          data-niveau="{{ $item->sort->Niveau ?? '' }}"
                                          data-ecole="{{ $item->sort->Ecole ?? '' }}"
                                          data-description="{{ e($item->sort->Description ?? '') }}"
                                          data-temps="{{ $item->sort->TempsActivation ?? '' }}"
                                          data-duree="{{ $item->sort->Duree ?? '' }}"
                                          data-distance="{{ $item->sort->Distance ?? '' }}"
                                          data-composants="{{ $item->sort->Composants ?? '' }}"
                                          data-att-sauv="{{ $item->sort->AttaqueSauvegarde ?? '' }}"
                                    >{{ $item->sort->Nom ?? 'Sort' }}</span>
                                    <div class="spell-detail" aria-hidden="true"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                    @endif
            @endforeach
        </div>

        <div class="bottom-actions">
            <a href="{{ route('personnages.show', $personnage) }}" class="btn-yellow">Retour</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function(){
            function savePrepare(personnageId, personnageSortId, sortId, value) {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                if (personnageSortId) {
                    const url = '/personnages/' + personnageId + '/sorts/' + personnageSortId;
                    return fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ prepare: value })
                    }).then(r => r.json().then(json => { if (!r.ok) throw json; return json; }));
                }

                // create new PersonnageSort
                const url = '/personnages/' + personnageId + '/sorts';
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ sort_id: sortId, prepare: value })
                }).then(r => r.json().then(json => { if (!r.ok) throw json; return json; }));
            }

                const personnageId = '{{ $personnage->id }}';
                const isMagicien = {{ $isMagicien ? 'true' : 'false' }};
                const limiteSort = {{ $limiteSort ?? 'null' }};
                const limiteTour = {{ $limiteTour ?? 'null' }};

            let saving = false;
            document.addEventListener('change', function(e) {
                const el = e.target;
                // Spellbook toggle (magician only)
                if (el.classList.contains('spellbook-toggle')) {
                    if (el.disabled || saving) return;
                    const psId = el.dataset.personnageSortId;
                    const sortId = el.dataset.sortId;
                    const level = parseInt(el.dataset.sortLevel || '0', 10);
                    // do not show spellbook for cantrips (level 0) — view ensures this
                    const value = el.checked ? 2 : 0;

                    saving = true;
                    el.disabled = true;
                    savePrepare(personnageId, psId, sortId, value)
                        .then((json) => {
                            if (json && json.personnage_sort_id) {
                                // attach id to all related toggles for this sort
                                document.querySelectorAll('[data-sort-id="' + sortId + '"]').forEach(function(i){ i.dataset.personnageSortId = json.personnage_sort_id; });
                            }

                            // grimoire coché => préparation possible ; décoché => préparation retirée
                            let container = el.closest('li') || el.parentElement;
                            const prepare = container && container.querySelector('.prepare-toggle');
                            if (prepare) {
                                prepare.checked = false;
                                prepare.disabled = value !== 2;
                            }
                            el.disabled = false;
                            saving = false;
                        })
                        .catch((err) => {
                            el.checked = !el.checked;
                            el.disabled = false;
                            saving = false;
                            const msg = err && err.message ? err.message : 'Erreur lors de la sauvegarde';
                            alert(msg);
                        });
                    return;
                }

                if (!el.classList.contains('prepare-toggle')) return;
                if (el.disabled || saving) return;

                const psId = el.dataset.personnageSortId;
                const sortId = el.dataset.sortId;
                const level = parseInt(el.dataset.sortLevel || '0', 10);
                const isChecked = el.checked ? 1 : 0;
                let saveValue = isChecked;

                // Magicien : le sort doit être dans le grimoire ; décocher la préparation le laisse dans le grimoire (2)
                let book = null;
                if (isMagicien && level > 0) {
                    const container = el.closest('li') || el.parentElement;
                    book = container && container.querySelector('.spellbook-toggle');
                    if (book && !book.checked) {
                        alert('Ce sort doit être inscrit dans le grimoire pour être préparé.');
                        el.checked = false;
                        return;
                    }
                    if (book && isChecked === 0) {
                        saveValue = 2;
                    }
                }

                // Client-side quick check: enforce limiteTour for level 0, and combined limiteSort for levels 1-3
                if (isChecked === 1) {
                    if (level === 0 && limiteTour !== null) {
                        const current = Array.from(document.querySelectorAll('.prepare-toggle'))
                            .filter(i => !i.disabled)
                            .filter(i => parseInt(i.dataset.sortLevel||'0',10) === 0)
                            .filter(i => i.checked)
                            .length;
                        if (current > limiteTour) {
                            alert('Limite de sorts cantrips préparés atteinte (client).');
                            el.checked = false;
                            return;
                        }
                    }

                    if ([1,2,3].includes(level) && limiteSort !== null) {
                        const current = Array.from(document.querySelectorAll('.prepare-toggle'))
                            .filter(i => !i.disabled)
                            .filter(i => [1,2,3].includes(parseInt(i.dataset.sortLevel||'0',10)))
                            .filter(i => i.checked)
                            .length;
                        if (current > limiteSort) {
                            alert('Limite de sorts préparés (niveaux 1-3) atteinte (client).');
                            el.checked = false;
                            return;
                        }
                    }
                }

                // Optimistic: disable while saving
                saving = true;
                el.disabled = true;
                if (book) book.disabled = true;

                savePrepare(personnageId, psId, sortId, saveValue)
                    .then((json) => {
                                // If a new record was created, store its id on the element
                                if (json && json.personnage_sort_id) {
                                    el.dataset.personnageSortId = json.personnage_sort_id;
                                    if (book) book.dataset.personnageSortId = json.personnage_sort_id;
                                }
                                if (book) book.disabled = false;
                        el.disabled = false;
                        saving = false;
                    })
                    .catch((err) => {
                        // revert on error
                        el.checked = !el.checked;
                        el.disabled = false;
                        if (book) book.disabled = false;
                        saving = false;
                        const msg = err && err.message ? err.message : 'Erreur lors de la sauvegarde';
                        alert(msg);
                    });
            });

            // Tooltip handlers
            const tooltip = document.getElementById('spell-tooltip');

            function esc(s) {
                return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            }

            function onSpellHover(e) {
                const t = e.currentTarget || e.target;
                const name = esc(t.dataset.name || '');
                const niveau = (esc(t.dataset.niveau || '') === "0") ? "Tour de magie" : esc(t.dataset.niveau || '');
                const ecole = esc(t.dataset.ecole || '');
                const temps = esc(t.dataset.temps || '');
                const duree = esc(t.dataset.duree || '');
                const distance = esc(t.dataset.distance || '');
                let desc = esc(t.dataset.description || '');
                desc = desc.replace(/\\n+/g, ' ').trim();


                tooltip.innerHTML = '<div class="title">' + name + '</div>' +
                    '<div class="meta">Niveau: ' + niveau + (ecole ? ' • ' + ecole : '') + '</div>' +
                    '<div class="meta">Temps: ' + (temps || '') + '</div>' +
                    '<div class="meta">Durée: ' + (duree || '') + '</div>' +
                    '<div class="meta">Distance: ' + (distance || '') + '</div>' +
                    '<div class="desc">' + (desc || '') + '</div>';

                // show tooltip and position it centered under the hovered name
                tooltip.classList.add('visible');
                setTimeout(() => {
                    const tipRect = tooltip.getBoundingClientRect();
                    const rect = t.getBoundingClientRect();
                    // center horizontally relative to hovered element
                    let left = rect.left + (rect.width - tipRect.width) / 2 + window.scrollX;
                    left = Math.max(8, Math.min(left, window.innerWidth - tipRect.width - 8));
                    // position just below the hovered element
                    let top = rect.bottom + 8 + window.scrollY;
                    // if not enough space below, place above
                    if (top + tipRect.height > window.innerHeight + window.scrollY - 8) {
                        top = rect.top - tipRect.height - 8 + window.scrollY;
                    }
                    tooltip.style.left = left + 'px';
                    tooltip.style.top = top + 'px';
                }, 0);
            }

            function onSpellLeave() {
                tooltip.classList.remove('visible');
                // clear after animation
                setTimeout(() => { tooltip.innerHTML = ''; }, 180);
            }

            document.querySelectorAll('.spell-hover').forEach(function(s){
                s.addEventListener('mouseenter', onSpellHover);
                s.addEventListener('mouseleave', onSpellLeave);
            });
        })();
    </script>
    <style>
        /* Tooltip styled like other page boxes (maquette-header / level-card) */
        .spell-tooltip {
            position: absolute;
            background: rgba(25,10,50,0.95);
            border: 1px solid rgba(107,43,138,0.6);
            color: #e6b84d;
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

        .spell-tooltip.visible {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .spell-tooltip .title {
            font-weight: 800;
            margin-bottom: 0.35rem;
            color: #e6b84d;
        }

        .spell-tooltip .meta {
            font-size: 0.85rem;
            color: #f1d484;
            margin-bottom: 0.5rem;
        }

        .spell-tooltip .desc {
            color: #e6b84d;
            max-height: none; /* remove scroll */
            overflow: visible;
            white-space: normal;
        }
    </style>

@endpush
