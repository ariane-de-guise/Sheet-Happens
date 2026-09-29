@extends('layouts.app')

@section('title', 'Montée de niveau - ' . $personnage->nom)

@section('content')
    <div style="max-width: 1050px; margin: 0 auto;">
        <div class="carte" style="border: 1px solid var(--or-bordure); overflow: hidden;">
            <div style="text-align:center; padding: 1rem 0 1.5rem; border-bottom:1px solid var(--or-ligne-forte);">
                <div style="color: var(--texte-secondaire); text-transform:uppercase; letter-spacing:.25rem; font-size:.72rem;">Progression du personnage</div>
                <h1 style="color: var(--or); margin:.35rem 0; font-size:2rem;">Montée de niveau</h1>
                <div style="color: var(--texte-secondaire);">{{ $personnage->nom }} · {{ $personnage->classe->nom }} · Niveau {{ $apercu['niveau_depart'] }} → {{ $apercu['niveau_arrivee'] }}</div>
            </div>

            @if($errors->any())
                <div style="margin:1rem 0; padding:1rem; border: 1px solid var(--alerte-erreur-bordure); border-radius:10px; background:var(--alerte-erreur-fond); color:var(--alerte-erreur-texte);">
                    @foreach($errors->all() as $erreur)<div>• {{ $erreur }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('personnages.montee-niveau.store', $personnage) }}" id="formulaireNiveau">
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
                                <div style="padding:.65rem 0; border-bottom:1px solid var(--or-ligne-faible); color: var(--texte);">
                                    @include('partials.info-bulle', [
                                        'libelle' => '✦ ' . $capacite->nom,
                                        'titre' => $capacite->nom,
                                        'description' => $capacite->description,
                                    ])
                                </div>
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
                                    <button type="button" id="boutonLancerDeVie" class="bouton bouton-secondaire" style="width:100%;">Lancer le d{{ $apercu['de_vie_faces'] }}</button>
                                    <div id="resultatDeVie" style="{{ $jetDeVie !== null ? '' : 'display:none;' }} margin-top:.7rem; text-align:center; padding:.65rem; border-radius:8px; background:var(--or-halo-faible); color:var(--texte);">
                                        Résultat : <strong id="valeurDeVie" style="color: var(--or); font-size:1.35rem;">{{ $jetDeVie }}</strong>
                                        <div id="totalDeVie" style="color: var(--texte-secondaire); font-size:.8rem; margin-top:.2rem;">
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

                        @if($apercu['choix_amelioration'])
                            <div>
                                <div style="color:var(--texte); font-weight:600; margin-bottom:.7rem;">Amélioration de caractéristiques ou don</div>
                                <div style="display:flex; gap:.75rem; margin-bottom:1rem;">
                                    <label style="flex:1; padding:.8rem; border:1px solid var(--bordure-option); border-radius:10px; cursor:pointer;"><input type="radio" name="type_amelioration" value="caracteristiques" @checked(old('type_amelioration', 'caracteristiques') === 'caracteristiques')> <span style="color: var(--or)">Caractéristiques</span></label>
                                    <label style="flex:1; padding:.8rem; border:1px solid var(--bordure-option); border-radius:10px; cursor:pointer;"><input type="radio" name="type_amelioration" value="don" @checked(old('type_amelioration') === 'don')> <span style="color: var(--or)">Don (Feat)</span></label>
                                </div>

                                <div id="panneauCaracteristiques" style="padding:1rem; background:var(--panneau-vif); border-radius:10px;">
                                    <div style="color: var(--texte-secondaire); font-size:.85rem; margin-bottom:.8rem;">Répartissez exactement 2 points (+2 ou +1/+1). Une caractéristique ne peut pas dépasser 20.</div>
                                    @php $stats = ['force'=>'Force','dexterite'=>'Dextérité','constitution'=>'Constitution','intelligence'=>'Intelligence','sagesse'=>'Sagesse','charisme'=>'Charisme']; @endphp
                                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem;">
                                        @foreach($stats as $cle=>$libelle)
                                            <label style="color: var(--texte);font-size:.85rem;">{{ $libelle }} ({{ $personnage->abilites->$cle }})
                                                <select name="caracteristiques[{{ $cle }}]" style="width:100%;margin-top:.25rem;padding:.45rem;border-radius:7px;background:var(--champ-fond);color:var(--texte);border:1px solid var(--bordure-option);">
                                                    @foreach([0,1,2] as $v)<option value="{{ $v }}" @selected((int)old("caracteristiques.$cle",0)===$v)>+{{ $v }}</option>@endforeach
                                                </select>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div id="panneauDon" style="display:none; padding:1rem; background:var(--panneau-vif); border-radius:10px;">
                                    <label style="color: var(--texte);">Don
                                        <select name="don" id="choixDon" style="width:100%;margin-top:.35rem;padding:.55rem;border-radius:7px;background:var(--champ-fond);color:var(--texte);border:1px solid var(--bordure-option);">
                                            <option value="">— Choisir —</option>
                                            @foreach($apercu['dons'] as $nom=>$don)
                                                <option value="{{ $nom }}" data-stats='@json($don->choix_stat ?? [])' data-description="{{ $don->description }}" @selected(old('don')===$nom)>{{ $nom }} — {{ $don->resume }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <div id="descriptionDon" style="display:none;margin-top:.8rem;padding:.7rem .8rem;border:1px solid var(--or-bordure);border-radius:8px;background:var(--panneau);color:var(--texte-secondaire);font-size:.85rem;line-height:1.4;"></div>
                                    <div id="conteneurCaracteristiqueDon" style="display:none;margin-top:.8rem;">
                                        <label style="color: var(--texte);">Caractéristique augmentée
                                            <select name="don_caracteristique" id="choixCaracteristiqueDon" style="width:100%;margin-top:.35rem;padding:.55rem;border-radius:7px;background:var(--champ-fond);color:var(--texte);border:1px solid var(--bordure-option);"></select>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.25rem;">
                    <a href="{{ route('personnages.show', $personnage) }}" class="bouton bouton-secondaire">Annuler</a>
                    <button type="submit" class="bouton" style="background:var(--or-vif);color:var(--texte-inverse);font-weight:800;padding:.8rem 1.5rem;">Confirmer le niveau {{ $apercu['niveau_arrivee'] }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const radiosPv = document.querySelectorAll('input[name="mode_pv"]');
            const boutonLancer = document.getElementById('boutonLancerDeVie');
            const boiteResultat = document.getElementById('resultatDeVie');
            const valeurResultat = document.getElementById('valeurDeVie');
            const totalResultat = document.getElementById('totalDeVie');
            const urlLancer = @json(route('personnages.montee-niveau.lancer-de-vie', $personnage));
            const conMod = {{ (int) $apercu['modificateur_constitution'] }};

            boutonLancer?.addEventListener('click', async () => {
                document.querySelector('input[name="mode_pv"][value="de"]').checked = true;
                boutonLancer.disabled = true;
                try {
                    const reponse = await fetch(urlLancer, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                    if (!reponse.ok) throw new Error('roll failed');
                    const donnees = await reponse.json();
                    valeurResultat.textContent = donnees.lancer;
                    totalResultat.textContent = `Gain de ce niveau : ${Math.max(1, donnees.lancer + conMod)} PV (${donnees.lancer} ${conMod >= 0 ? '+' : '-'} ${Math.abs(conMod)} CON)`;
                    boiteResultat.style.display = 'block';
                    boutonLancer.textContent = `Relancer le d${donnees.faces}`;
                } catch (error) {
                    boiteResultat.style.display = 'block';
                    valeurResultat.textContent = 'Erreur';
                    totalResultat.textContent = 'Impossible de lancer le dé. Réessayez.';
                } finally {
                    boutonLancer.disabled = false;
                }
            });

            radiosPv.forEach(radio => radio.addEventListener('change', () => {
                if (radio.value === 'fixe' && radio.checked && boiteResultat) boiteResultat.style.display = 'none';
            }));

            const radios = document.querySelectorAll('input[name="type_amelioration"]');
            const panneauCaracteristiques = document.getElementById('panneauCaracteristiques');
            const panneauDon = document.getElementById('panneauDon');
            const choixDon = document.getElementById('choixDon');
            const conteneurCaracteristique = document.getElementById('conteneurCaracteristiqueDon');
            const choixCaracteristique = document.getElementById('choixCaracteristiqueDon');
            const libelles = {force:'Force',dexterite:'Dextérité',constitution:'Constitution',intelligence:'Intelligence',sagesse:'Sagesse',charisme:'Charisme'};

            function basculer() {
                const valeur = document.querySelector('input[name="type_amelioration"]:checked')?.value;
                if (panneauCaracteristiques) panneauCaracteristiques.style.display = valeur === 'caracteristiques' ? 'block' : 'none';
                if (panneauDon) panneauDon.style.display = valeur === 'don' ? 'block' : 'none';
            }
            function majCaracteristiquesDon() {
                if (!choixDon || !choixCaracteristique || !conteneurCaracteristique) return;
                const opt = choixDon.options[choixDon.selectedIndex];
                const description = document.getElementById('descriptionDon');
                if (description) {
                    description.textContent = opt?.dataset.description || '';
                    description.style.display = description.textContent ? 'block' : 'none';
                }
                const stats = JSON.parse(opt?.dataset?.stats || '[]');
                choixCaracteristique.innerHTML = stats.map(s => `<option value="${s}">${libelles[s] || s}</option>`).join('');
                conteneurCaracteristique.style.display = stats.length ? 'block' : 'none';
            }
            radios.forEach(r => r.addEventListener('change', basculer));
            choixDon?.addEventListener('change', majCaracteristiquesDon);
            basculer(); majCaracteristiquesDon();
        })();
    </script>
@endpush
