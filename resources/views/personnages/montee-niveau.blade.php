@extends('layouts.app')

@section('titre', 'Montée de niveau - ' . $personnage->nom)

@section('contenu')
    <div class="montee-page">
        <div class="carte montee-carte">
            <div class="montee-entete">
                <div class="montee-surtitre">Progression du personnage</div>
                <h1 class="montee-titre">Montée de niveau</h1>
                <div class="texte-secondaire">{{ $personnage->nom }} · {{ $personnage->classe->nom }} · Niveau {{ $apercu['niveau_depart'] }} → {{ $apercu['niveau_arrivee'] }}</div>
            </div>

            @if($errors->any())
                <div class="montee-erreurs">
                    @foreach($errors->all() as $erreur)<div>• {{ $erreur }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('personnages.montee-niveau.store', $personnage) }}" id="formulaireNiveau">
                @csrf
                <div class="montee-grille">
                    <div>
                        <div class="montee-boite-resume">
                            <div class="montee-boite-titre">Résumé</div>
                            <div class="montee-resume">
                                <div class="montee-resume-ligne"><span>Niveau</span><strong class="texte-principal">{{ $apercu['niveau_depart'] }} → {{ $apercu['niveau_arrivee'] }}</strong></div>
                                <div class="montee-resume-ligne"><span>Bonus de maîtrise</span><strong class="texte-principal">+{{ $apercu['bonus_maitrise'] }}</strong></div>
                                <div class="montee-resume-ligne"><span>PV maximum</span><strong class="texte-principal">{{ $personnage->point_de_vie }}</strong></div>
                                <div class="montee-resume-ligne"><span>Dé de vie</span><strong class="texte-principal">d{{ $apercu['de_vie_faces'] }}</strong></div>
                                <div class="montee-resume-ligne"><span>Valeur fixe</span><strong class="texte-principal">{{ $apercu['pv_base'] }} + mod. CON ({{ $apercu['modificateur_constitution'] >= 0 ? '+' : '' }}{{ $apercu['modificateur_constitution'] }})</strong></div>
                            </div>
                        </div>

                        <div class="montee-boite">
                            <div class="montee-boite-titre">Nouveautés du niveau {{ $apercu['niveau_arrivee'] }}</div>
                            @forelse($apercu['capacites'] as $capacite)
                                <div class="montee-nouveaute">
                                    @include('partials.info-bulle', [
                                        'libelle' => '✦ ' . $capacite->nom,
                                        'titre' => $capacite->nom,
                                        'description' => $capacite->description,
                                    ])
                                </div>
                            @empty
                                <div class="texte-secondaire">Aucune nouvelle capacité de classe à ce niveau.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="montee-boite-choix">
                        <div class="montee-choix-titre">Vos choix</div>

                        <div class="montee-bloc">
                            <div class="montee-libelle">Points de vie</div>
                            <div class="montee-aide">Choisissez la méthode D&amp;D 5e : prendre la valeur fixe ou lancer votre dé de vie. Le modificateur de Constitution est ajouté dans les deux cas.</div>
                            <div class="montee-grille-pv">
                                <label class="montee-option-pv">
                                    <input type="radio" name="mode_pv" value="fixe" @checked(old('mode_pv', 'fixe') === 'fixe') required>
                                    <strong class="montee-option-nom">Valeur fixe</strong>
                                    <div class="montee-option-detail">{{ $apercu['pv_base'] }} + mod. CON</div>
                                </label>
                                <div class="montee-option-de">
                                    <label class="curseur-main">
                                        <input type="radio" name="mode_pv" value="de" @checked(old('mode_pv') === 'de') required>
                                        <strong class="montee-option-nom">Lancer le dé</strong>
                                    </label>
                                    <div class="montee-option-detail-de">1d{{ $apercu['de_vie_faces'] }} + mod. CON</div>
                                    <button type="button" id="boutonLancerDeVie" class="bouton bouton-secondaire bouton-pleine-largeur">Lancer le d{{ $apercu['de_vie_faces'] }}</button>
                                    <div id="resultatDeVie" class="montee-jet-resultat"@if($jetDeVie === null) style="display:none;"@endif>
                                        Résultat : <strong class="montee-jet-valeur" id="valeurDeVie">{{ $jetDeVie }}</strong>
                                        <div class="montee-jet-total" id="totalDeVie">
                                            @if($jetDeVie !== null)
                                                Gain de ce niveau : {{ max(1, $jetDeVie + $apercu['modificateur_constitution']) }} PV ({{ $jetDeVie }} {{ $apercu['modificateur_constitution'] >= 0 ? '+' : '-' }} {{ abs($apercu['modificateur_constitution']) }} CON)
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($apercu['choix_sous_classe'])
                            <div class="montee-bloc">
                                <div class="montee-libelle-choix">Choisissez votre sous-classe</div>
                                <div class="montee-liste-sous-classes">
                                    @foreach($apercu['sous_classes'] as $sc)
                                        <label class="montee-option-sous-classe">
                                            <input type="radio" name="sous_classe_id" value="{{ $sc->id }}" @checked(old('sous_classe_id') == $sc->id) required>
                                            <strong class="montee-option-nom">{{ $sc->nom }}</strong>
                                            <div class="montee-option-detail">{{ $sc->description }}</div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($apercu['choix_amelioration'])
                            <div>
                                <div class="montee-libelle-choix">Amélioration de caractéristiques ou don</div>
                                <div class="montee-types-amelioration">
                                    <label class="montee-option-amelioration"><input type="radio" name="type_amelioration" value="caracteristiques" @checked(old('type_amelioration', 'caracteristiques') === 'caracteristiques')> <span class="montee-option-libelle">Caractéristiques</span></label>
                                    <label class="montee-option-amelioration"><input type="radio" name="type_amelioration" value="don" @checked(old('type_amelioration') === 'don')> <span class="montee-option-libelle">Don (Feat)</span></label>
                                </div>

                                <div class="montee-panneau" id="panneauCaracteristiques">
                                    <div class="montee-aide-repartition">Répartissez exactement 2 points (+2 ou +1/+1). Une caractéristique ne peut pas dépasser 20.</div>
                                    @php $stats = ['force'=>'Force','dexterite'=>'Dextérité','constitution'=>'Constitution','intelligence'=>'Intelligence','sagesse'=>'Sagesse','charisme'=>'Charisme']; @endphp
                                    <div class="montee-grille-caracteristiques">
                                        @foreach($stats as $cle=>$libelle)
                                            <label class="montee-libelle-caracteristique">{{ $libelle }} ({{ $personnage->abilites->$cle }})
                                                <select class="montee-liste-petite" name="caracteristiques[{{ $cle }}]">
                                                    @foreach([0,1,2] as $v)<option value="{{ $v }}" @selected((int)old("caracteristiques.$cle",0)===$v)>+{{ $v }}</option>@endforeach
                                                </select>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="montee-panneau-masque" id="panneauDon">
                                    <label class="montee-libelle-don">Don
                                        <select class="montee-liste" name="don" id="choixDon">
                                            <option value="">— Choisir —</option>
                                            @foreach($apercu['dons'] as $nom=>$don)
                                                <option value="{{ $nom }}" data-stats='@json($don->choix_stat ?? [])' data-description="{{ $don->description }}" @selected(old('don')===$nom)>{{ $nom }} — {{ $don->resume }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <div class="montee-description-don" id="descriptionDon"></div>
                                    <div class="montee-conteneur-masque" id="conteneurCaracteristiqueDon">
                                        <label class="montee-libelle-don">Caractéristique augmentée
                                            <select class="montee-liste" name="don_caracteristique" id="choixCaracteristiqueDon"></select>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="montee-pied">
                    <a href="{{ route('personnages.show', $personnage) }}" class="bouton bouton-secondaire">Annuler</a>
                    <button type="submit" class="bouton bouton-confirmer">Confirmer le niveau {{ $apercu['niveau_arrivee'] }}</button>
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
