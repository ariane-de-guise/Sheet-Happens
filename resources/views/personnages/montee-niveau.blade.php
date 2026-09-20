@extends('layouts.app')

@section('title', 'Montée de niveau - ' . $personnage->nom)

@section('content')
<div style="max-width: 1050px; margin: 0 auto;">
    <div class="card" style="border: 1px solid #c49a3c; overflow: hidden;">
        <div style="text-align:center; padding: 1rem 0 1.5rem; border-bottom:1px solid rgba(196,154,60,.45);">
            <div style="color:#b8a9c9; text-transform:uppercase; letter-spacing:.25rem; font-size:.72rem;">Progression du personnage</div>
            <h1 style="color:#e6b84d; margin:.35rem 0; font-size:2rem;">Montée de niveau</h1>
            <div style="color:#b8a9c9;">{{ $personnage->nom }} · {{ $personnage->classe->nom }} · Niveau {{ $apercu['niveau_depart'] }} → {{ $apercu['niveau_arrivee'] }}</div>
        </div>

        @if($errors->any())
            <div style="margin:1rem 0; padding:1rem; border:1px solid #d06b6b; border-radius:10px; background:rgba(120,20,30,.18); color:#f2c4c4;">
                @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('personnages.montee-niveau.store', $personnage) }}" id="levelForm">
            @csrf
            <div style="display:grid; grid-template-columns: 1fr 1.45fr; gap:1.25rem; margin-top:1.5rem;">
                <div>
                    <div style="background:rgba(25,10,50,.9); border:1px solid #c49a3c; border-radius:12px; padding:1.1rem; margin-bottom:1rem;">
                        <div style="color:#e6b84d; font-weight:700; margin-bottom:.75rem;">Résumé</div>
                        <div style="display:grid; gap:.55rem; color:#b8a9c9; font-size:.9rem;">
                            <div style="display:flex;justify-content:space-between"><span>Niveau</span><strong style="color:#fff">{{ $apercu['niveau_depart'] }} → {{ $apercu['niveau_arrivee'] }}</strong></div>
                            <div style="display:flex;justify-content:space-between"><span>Bonus de maîtrise</span><strong style="color:#fff">+{{ $apercu['bonus_maitrise'] }}</strong></div>
                            <div style="display:flex;justify-content:space-between"><span>PV actuels</span><strong style="color:#fff">{{ $personnage->point_de_vie }}</strong></div>
                            <div style="display:flex;justify-content:space-between"><span>Dé de vie</span><strong style="color:#fff">d{{ $apercu['de_vie_faces'] }}</strong></div>
                            <div style="display:flex;justify-content:space-between"><span>Valeur fixe</span><strong style="color:#fff">{{ $apercu['pv_base'] }} + mod. CON ({{ $apercu['modificateur_constitution'] >= 0 ? '+' : '' }}{{ $apercu['modificateur_constitution'] }})</strong></div>
                        </div>
                    </div>

                    <div style="background:rgba(25,10,50,.9); border:1px solid #c49a3c; border-radius:12px; padding:1.1rem;">
                        <div style="color:#e6b84d; font-weight:700; margin-bottom:.75rem;">Nouveautés du niveau {{ $apercu['niveau_arrivee'] }}</div>
                        @forelse($apercu['capacites'] as $capacite)
                            <div style="padding:.65rem 0; border-bottom:1px solid rgba(196,154,60,.2); color:#e9e0f1;">✦ {{ $capacite }}</div>
                        @empty
                            <div style="color:#b8a9c9;">Aucune nouvelle capacité de classe à ce niveau.</div>
                        @endforelse
                    </div>
                </div>

                <div style="background:rgba(25,10,50,.9); border:1px solid #c49a3c; border-radius:12px; padding:1.25rem;">
                    <div style="color:#e6b84d; font-size:1.1rem; font-weight:700; margin-bottom:1rem;">Vos choix</div>

                    <div style="margin-bottom:1.5rem;">
                        <div style="color:#fff; font-weight:600; margin-bottom:.35rem;">Points de vie</div>
                        <div style="color:#b8a9c9; font-size:.85rem; margin-bottom:.75rem;">Choisissez la méthode D&amp;D 5e : prendre la valeur fixe ou lancer votre dé de vie. Le modificateur de Constitution est ajouté dans les deux cas.</div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:.75rem;">
                            <label style="padding:.85rem; border:1px solid #6e5689; border-radius:10px; cursor:pointer;">
                                <input type="radio" name="mode_pv" value="fixe" @checked(old('mode_pv', 'fixe') === 'fixe') required>
                                <strong style="color:#e6b84d; margin-left:.35rem;">Valeur fixe</strong>
                                <div style="color:#b8a9c9; font-size:.82rem; margin:.35rem 0 0 1.4rem;">{{ $apercu['pv_base'] }} + mod. CON</div>
                            </label>
                            <div style="padding:.85rem; border:1px solid #6e5689; border-radius:10px;">
                                <label style="cursor:pointer;">
                                    <input type="radio" name="mode_pv" value="de" @checked(old('mode_pv') === 'de') required>
                                    <strong style="color:#e6b84d; margin-left:.35rem;">Lancer le dé</strong>
                                </label>
                                <div style="color:#b8a9c9; font-size:.82rem; margin:.35rem 0 .65rem 1.4rem;">1d{{ $apercu['de_vie_faces'] }} + mod. CON</div>
                                <button type="button" id="btnRollHitDie" class="btn btn-secondary" style="width:100%;">Lancer le d{{ $apercu['de_vie_faces'] }}</button>
                                <div id="hitDieResult" style="{{ $jetDeVie !== null ? '' : 'display:none;' }} margin-top:.7rem; text-align:center; padding:.65rem; border-radius:8px; background:rgba(230,184,77,.12); color:#fff;">
                                    Résultat : <strong id="hitDieValue" style="color:#e6b84d; font-size:1.35rem;">{{ $jetDeVie }}</strong>
                                    <div id="hitDieTotal" style="color:#b8a9c9; font-size:.8rem; margin-top:.2rem;">
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
                            <div style="color:#fff; font-weight:600; margin-bottom:.7rem;">Choisissez votre sous-classe</div>
                            <div style="display:grid; gap:.7rem;">
                                @foreach($apercu['sous_classes'] as $sc)
                                    <label style="display:block; padding:.85rem; border:1px solid #6e5689; border-radius:10px; cursor:pointer;">
                                        <input type="radio" name="sous_classe_id" value="{{ $sc->id }}" @checked(old('sous_classe_id') == $sc->id) required>
                                        <strong style="color:#e6b84d; margin-left:.35rem;">{{ $sc->nom }}</strong>
                                        <div style="color:#b8a9c9; font-size:.82rem; margin:.35rem 0 0 1.4rem;">{{ $sc->description }}</div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($apercu['choix_asi'])
                        <div>
                            <div style="color:#fff; font-weight:600; margin-bottom:.7rem;">Amélioration de caractéristiques ou don</div>
                            <div style="display:flex; gap:.75rem; margin-bottom:1rem;">
                                <label style="flex:1; padding:.8rem; border:1px solid #6e5689; border-radius:10px; cursor:pointer;"><input type="radio" name="type_amelioration" value="asi" @checked(old('type_amelioration', 'asi') === 'asi')> <span style="color:#e6b84d">Caractéristiques</span></label>
                                <label style="flex:1; padding:.8rem; border:1px solid #6e5689; border-radius:10px; cursor:pointer;"><input type="radio" name="type_amelioration" value="feat" @checked(old('type_amelioration') === 'feat')> <span style="color:#e6b84d">Don (Feat)</span></label>
                            </div>

                            <div id="asiPanel" style="padding:1rem; background:rgba(49,25,75,.55); border-radius:10px;">
                                <div style="color:#b8a9c9; font-size:.85rem; margin-bottom:.8rem;">Répartissez exactement 2 points (+2 ou +1/+1). Une caractéristique ne peut pas dépasser 20.</div>
                                @php $stats = ['force'=>'Force','dexterite'=>'Dextérité','constitution'=>'Constitution','intelligence'=>'Intelligence','sagesse'=>'Sagesse','charisme'=>'Charisme']; @endphp
                                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem;">
                                    @foreach($stats as $key=>$label)
                                        <label style="color:#e9e0f1;font-size:.85rem;">{{ $label }} ({{ $personnage->abilites->$key }})
                                            <select name="asi[{{ $key }}]" style="width:100%;margin-top:.25rem;padding:.45rem;border-radius:7px;background:#1a0b2d;color:#fff;border:1px solid #6e5689;">
                                                @foreach([0,1,2] as $v)<option value="{{ $v }}" @selected((int)old("asi.$key",0)===$v)>+{{ $v }}</option>@endforeach
                                            </select>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div id="featPanel" style="display:none; padding:1rem; background:rgba(49,25,75,.55); border-radius:10px;">
                                <label style="color:#e9e0f1;">Don
                                    <select name="feat" id="featSelect" style="width:100%;margin-top:.35rem;padding:.55rem;border-radius:7px;background:#1a0b2d;color:#fff;border:1px solid #6e5689;">
                                        <option value="">— Choisir —</option>
                                        @foreach($apercu['feats'] as $nom=>$feat)
                                            <option value="{{ $nom }}" data-stats='@json($feat['choix_stat'] ?? [])' @selected(old('feat')===$nom)>{{ $nom }} — {{ $feat['resume'] }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <div id="featStatWrap" style="display:none;margin-top:.8rem;">
                                    <label style="color:#e9e0f1;">Caractéristique augmentée
                                        <select name="feat_stat" id="featStat" style="width:100%;margin-top:.35rem;padding:.55rem;border-radius:7px;background:#1a0b2d;color:#fff;border:1px solid #6e5689;"></select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.25rem;">
                <a href="{{ route('personnages.show', $personnage) }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn" style="background:#e6b84d;color:#1a0533;font-weight:800;padding:.8rem 1.5rem;">Confirmer le niveau {{ $apercu['niveau_arrivee'] }}</button>
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
