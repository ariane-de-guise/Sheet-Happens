@extends('layouts.app')

@section('title', 'Montée de niveau - ' . $personnage->nom)

@section('content')
    <div style="max-width: 1050px; margin: 0 auto;">
        <div class="card" style="border: 1px solid var(--or-bordure); overflow: hidden;">
            <div style="text-align:center; padding: 1rem 0 1.5rem; border-bottom:1px solid var(--or-ligne-forte);">
                <div style="color: var(--texte-secondaire); text-transform:uppercase; letter-spacing:.25rem; font-size:.72rem;">Progression du personnage</div>
                <h1 style="color: var(--or); margin:.35rem 0; font-size:2rem;">Montée de niveau</h1>
                <div style="color: var(--texte-secondaire);">{{ $personnage->nom }} · {{ $personnage->classe->nom }} · Niveau {{ $apercu['niveau_depart'] }} → {{ $apercu['niveau_arrivee'] }}</div>
            </div>

            @if($errors->any())
                <div style="margin:1rem 0; padding:1rem; border: 1px solid var(--alerte-erreur-bordure); border-radius:10px; background:var(--alerte-erreur-fond); color:var(--alerte-erreur-texte);">
                    @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('personnages.montee-niveau.store', $personnage) }}" id="levelForm">
                @csrf
                <div style="display:grid; grid-template-columns: 1fr 1.45fr; gap:1.25rem; margin-top:1.5rem;">
                    <div>
                        <div style="background:var(--panneau); border: 1px solid var(--or-bordure); border-radius:12px; padding:1.1rem; margin-bottom:1rem;">
                            <div style="color: var(--or); font-weight:700; margin-bottom:.75rem;">Résumé</div>
                            <div style="display:grid; gap:.55rem; color: var(--texte-secondaire); font-size:.9rem;">
                                <div style="display:flex;justify-content:space-between"><span>Niveau</span><strong style="color:var(--texte)">{{ $apercu['niveau_depart'] }} → {{ $apercu['niveau_arrivee'] }}</strong></div>
                                <div style="display:flex;justify-content:space-between"><span>Bonus de maîtrise</span><strong style="color:var(--texte)">+{{ $apercu['bonus_maitrise'] }}</strong></div>
                                <div style="display:flex;justify-content:space-between"><span>PV actuels</span><strong style="color:var(--texte)">{{ $personnage->point_de_vie }}</strong></div>
                                <div style="display:flex;justify-content:space-between"><span>Dé de vie</span><strong style="color:var(--texte)">d{{ $apercu['de_vie_faces'] }}</strong></div>
                                <div style="display:flex;justify-content:space-between"><span>Valeur fixe</span><strong style="color:var(--texte)">{{ $apercu['pv_base'] }} + mod. CON ({{ $apercu['modificateur_constitution'] >= 0 ? '+' : '' }}{{ $apercu['modificateur_constitution'] }})</strong></div>
                            </div>
                        </div>

                        <div style="background:var(--panneau); border: 1px solid var(--or-bordure); border-radius:12px; padding:1.1rem;">
                            <div style="color: var(--or); font-weight:700; margin-bottom:.75rem;">Nouveautés du niveau {{ $apercu['niveau_arrivee'] }}</div>
                            @forelse($apercu['capacites'] as $capacite)
                                <div style="padding:.65rem 0; border-bottom:1px solid var(--or-ligne-faible); color: var(--texte);">✦ {{ $capacite }}</div>
                            @empty
                                <div style="color: var(--texte-secondaire);">Aucune nouvelle capacité de classe à ce niveau.</div>
                            @endforelse
                        </div>
                    </div>

                    <div style="background:var(--panneau); border: 1px solid var(--or-bordure); border-radius:12px; padding:1.25rem;">
                        <div style="color: var(--or); font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Vos choix</div>

                        <div style="margin-bottom:1.5rem;">
                            <div style="color:var(--texte); font-weight:600; margin-bottom:.35rem;">Points de vie</div>
                            <div style="color: var(--texte-secondaire); font-size:.85rem; margin-bottom:.75rem;">Choisissez la méthode D&amp;D 5e : prendre la valeur fixe ou lancer votre dé de vie. Le modificateur de Constitution est ajouté dans les deux cas.</div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">
                                <label style="padding:.85rem; border:1px solid var(--bordure-option); border-radius:10px; cursor:pointer;">
                                    <input type="radio" name="mode_pv" value="fixe" @checked(old('mode_pv', 'fixe') === 'fixe') required>
                                    <strong style="color: var(--or); margin-left:.35rem;">Valeur fixe</strong>
                                    <div style="color: var(--texte-secondaire); font-size:.82rem; margin:.35rem 0 0 1.4rem;">{{ $apercu['pv_base'] }} + mod. CON</div>
                                </label>
                                <div style="padding:.85rem; border:1px solid var(--bordure-option); border-radius:10px;">
                                    <label style="cursor:pointer;">
                                        <input type="radio" name="mode_pv" value="de" @checked(old('mode_pv') === 'de') required>
                                        <strong style="color: var(--or); margin-left:.35rem;">Lancer le dé</strong>
                                    </label>
                                    <div style="color: var(--texte-secondaire); font-size:.82rem; margin:.35rem 0 .65rem 1.4rem;">1d{{ $apercu['de_vie_faces'] }} + mod. CON</div>
                                    <button type="button" id="btnRollHitDie" class="btn btn-secondary" style="width:100%;">Lancer le d{{ $apercu['de_vie_faces'] }}</button>
                                    <div id="hitDieResult" style="{{ $jetDeVie !== null ? '' : 'display:none;' }} margin-top:.7rem; text-align:center; padding:.65rem; border-radius:8px; background:var(--or-halo-faible); color:var(--texte);">
                                        Résultat : <strong id="hitDieValue" style="color: var(--or); font-size:1.35rem;">{{ $jetDeVie }}</strong>
                                        <div id="hitDieTotal" style="color: var(--texte-secondaire); font-size:.8rem; margin-top:.2rem;">
                                            @if($jetDeVie !== null)
                                                Gain de ce niveau : {{ max(1, $jetDeVie + $apercu['modificateur_constitution']) }} PV ({{ $jetDeVie }} {{ $apercu['modificateur_constitution'] >= 0 ? '+' : '-' }} {{ abs($apercu['modificateur_constitution']) }} CON)
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($apercu['choix_sous_classe'])
                            <div style="margin-bottom:1.5rem;">
                                <div style="color:var(--texte); font-weight:600; margin-bottom:.7rem;">Choisissez votre sous-classe</div>
                                <div style="display:grid; gap:.7rem;">
                                    @foreach($apercu['sous_classes'] as $sc)
                                        <label style="display:block; padding:.85rem; border:1px solid var(--bordure-option); border-radius:10px; cursor:pointer;">
                                            <input type="radio" name="sous_classe_id" value="{{ $sc->id }}" @checked(old('sous_classe_id') == $sc->id) required>
                                            <strong style="color: var(--or); margin-left:.35rem;">{{ $sc->nom }}</strong>
                                            <div style="color: var(--texte-secondaire); font-size:.82rem; margin:.35rem 0 0 1.4rem;">{{ $sc->description }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($apercu['choix_asi'])
                            <div>
                                <div style="color:var(--texte); font-weight:600; margin-bottom:.7rem;">Amélioration de caractéristiques ou don</div>
                                <div style="display:flex; gap:.75rem; margin-bottom:1rem;">
                                    <label style="flex:1; padding:.8rem; border:1px solid var(--bordure-option); border-radius:10px; cursor:pointer;"><input type="radio" name="type_amelioration" value="asi" @checked(old('type_amelioration', 'asi') === 'asi')> <span style="color: var(--or)">Caractéristiques</span></label>
                                    <label style="flex:1; padding:.8rem; border:1px solid var(--bordure-option); border-radius:10px; cursor:pointer;"><input type="radio" name="type_amelioration" value="feat" @checked(old('type_amelioration') === 'feat')> <span style="color: var(--or)">Don (Feat)</span></label>
                                </div>

                                <div id="asiPanel" style="padding:1rem; background:var(--panneau-vif); border-radius:10px;">
                                    <div style="color: var(--texte-secondaire); font-size:.85rem; margin-bottom:.8rem;">Répartissez exactement 2 points (+2 ou +1/+1). Une caractéristique ne peut pas dépasser 20.</div>
                                    @php $stats = ['force'=>'Force','dexterite'=>'Dextérité','constitution'=>'Constitution','intelligence'=>'Intelligence','sagesse'=>'Sagesse','charisme'=>'Charisme']; @endphp
                                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem;">
                                        @foreach($stats as $key=>$label)
                                            <label style="color: var(--texte);font-size:.85rem;">{{ $label }} ({{ $personnage->abilites->$key }})
                                                <select name="asi[{{ $key }}]" style="width:100%;margin-top:.25rem;padding:.45rem;border-radius:7px;background:var(--champ-fond);color:var(--texte);border:1px solid var(--bordure-option);">
                                                    @foreach([0,1,2] as $v)<option value="{{ $v }}" @selected((int)old("asi.$key",0)===$v)>+{{ $v }}</option>@endforeach
                                                </select>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div id="featPanel" style="display:none; padding:1rem; background:var(--panneau-vif); border-radius:10px;">
                                    <label style="color: var(--texte);">Don
                                        <select name="feat" id="featSelect" style="width:100%;margin-top:.35rem;padding:.55rem;border-radius:7px;background:var(--champ-fond);color:var(--texte);border:1px solid var(--bordure-option);">
                                            <option value="">— Choisir —</option>
                                            @foreach($apercu['feats'] as $nom=>$feat)
                                                <option value="{{ $nom }}" data-stats='@json($feat['choix_stat'] ?? [])' @selected(old('feat')===$nom)>{{ $nom }} — {{ $feat['resume'] }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <div id="featStatWrap" style="display:none;margin-top:.8rem;">
                                        <label style="color: var(--texte);">Caractéristique augmentée
                                            <select name="feat_stat" id="featStat" style="width:100%;margin-top:.35rem;padding:.55rem;border-radius:7px;background:var(--champ-fond);color:var(--texte);border:1px solid var(--bordure-option);"></select>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.25rem;">
                    <a href="{{ route('personnages.show', $personnage) }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn" style="background:var(--or-vif);color:var(--texte-inverse);font-weight:800;padding:.8rem 1.5rem;">Confirmer le niveau {{ $apercu['niveau_arrivee'] }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const hpRadios = document.querySelectorAll('input[name="mode_pv"]');
            const rollButton = document.getElementById('btnRollHitDie');
            const resultBox = document.getElementById('hitDieResult');
            const resultValue = document.getElementById('hitDieValue');
            const resultTotal = document.getElementById('hitDieTotal');
            const rollUrl = @json(route('personnages.montee-niveau.roll-hit-die', $personnage));
            const conMod = {{ (int) $apercu['modificateur_constitution'] }};

            rollButton?.addEventListener('click', async () => {
                document.querySelector('input[name="mode_pv"][value="de"]').checked = true;
                rollButton.disabled = true;
                try {
                    const response = await fetch(rollUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                    if (!response.ok) throw new Error('roll failed');
                    const data = await response.json();
                    resultValue.textContent = data.roll;
                    resultTotal.textContent = `Gain de ce niveau : ${Math.max(1, data.roll + conMod)} PV (${data.roll} ${conMod >= 0 ? '+' : '-'} ${Math.abs(conMod)} CON)`;
                    resultBox.style.display = 'block';
                    rollButton.textContent = `Relancer le d${data.faces}`;
                } catch (error) {
                    resultBox.style.display = 'block';
                    resultValue.textContent = 'Erreur';
                    resultTotal.textContent = 'Impossible de lancer le dé. Réessayez.';
                } finally {
                    rollButton.disabled = false;
                }
            });

            hpRadios.forEach(radio => radio.addEventListener('change', () => {
                if (radio.value === 'fixe' && radio.checked && resultBox) resultBox.style.display = 'none';
            }));

            const radios = document.querySelectorAll('input[name="type_amelioration"]');
            const asi = document.getElementById('asiPanel');
            const feat = document.getElementById('featPanel');
            const featSelect = document.getElementById('featSelect');
            const wrap = document.getElementById('featStatWrap');
            const statSelect = document.getElementById('featStat');
            const labels = {force:'Force',dexterite:'Dextérité',constitution:'Constitution',intelligence:'Intelligence',sagesse:'Sagesse',charisme:'Charisme'};

            function toggle() {
                const value = document.querySelector('input[name="type_amelioration"]:checked')?.value;
                if (asi) asi.style.display = value === 'asi' ? 'block' : 'none';
                if (feat) feat.style.display = value === 'feat' ? 'block' : 'none';
            }
            function featStats() {
                if (!featSelect || !statSelect || !wrap) return;
                const opt = featSelect.options[featSelect.selectedIndex];
                const stats = JSON.parse(opt?.dataset?.stats || '[]');
                statSelect.innerHTML = stats.map(s => `<option value="${s}">${labels[s] || s}</option>`).join('');
                wrap.style.display = stats.length ? 'block' : 'none';
            }
            radios.forEach(r => r.addEventListener('change', toggle));
            featSelect?.addEventListener('change', featStats);
            toggle(); featStats();
        })();
    </script>
@endpush
