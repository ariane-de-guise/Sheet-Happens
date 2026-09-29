@extends('layouts.app')

@section('titre', 'Modifier ' . $personnage->nom)

@push('styles')
<style>
.fiche-conteneur {
    max-width: 1100px;
}

.fiche-entete {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.fiche-entete-gauche {
    flex: 1;
    min-width: 250px;
}

.fiche-entete-droite {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    flex-wrap: wrap;
}

.stat-bloc {
    background: var(--panneau);
    border: 2px solid var(--or-bordure);
    border-radius: 12px;
    padding: 0.75rem;
    text-align: center;
    min-width: 80px;
}

.stat-bloc-etiquette {
    font-size: 0.7rem;
    color: var(--texte-secondaire);
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.stat-bloc input {
    width: 50px;
    text-align: center;
    background: var(--panneau-vif);
    border: 1px solid var(--or-bordure);
    border-radius: 6px;
    color: var(--or);
    font-size: 1.3rem;
    font-weight: 700;
    padding: 0.2rem;
}

.fiche-section {
    background: var(--panneau);
    border: 1px solid var(--or-bordure);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}

.fiche-section-titre {
    font-size: 1rem;
    font-weight: 600;
    color: var(--or);
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--or-ligne-moyenne);
}

.fiche-grille-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.fiche-grille-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.fiche-grille-6 {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 0.75rem;
}

.boite-aptitude {
    background: var(--panneau-carte);
    border: 2px solid var(--or-bordure);
    border-radius: 10px;
    padding: 0.75rem 0.5rem;
    text-align: center;
}

.nom-aptitude {
    font-size: 0.75rem;
    color: var(--texte-secondaire);
    text-transform: uppercase;
    margin-bottom: 0.4rem;
}

.champ-aptitude {
    width: 50px;
    text-align: center;
    background: var(--panneau-vif);
    border: 1px solid var(--or-bordure);
    border-radius: 6px;
    color: var(--or);
    font-size: 1.4rem;
    font-weight: 700;
    padding: 0.3rem;
    margin-bottom: 0.3rem;
}

.stat-bloc-valeur-fixe {
    font-weight: 700;
    color: var(--or);
    text-align: center;
}

.note-montee-niveau {
    font-size: 0.75rem;
    color: var(--texte-secondaire);
    margin: 0 0 0.75rem;
}

.mod-aptitude {
    font-size: 0.9rem;
    color: var(--or);
    font-weight: 600;
}

.formulaire-saisie-petit {
    width: 100%;
    padding: 0.4rem 0.6rem;
    background: var(--panneau);
    border: 1px solid var(--or-bordure);
    border-radius: 6px;
    color: var(--or-doux);
    font-size: 0.85rem;
    outline: none;
}

.formulaire-saisie-petit:focus {
    border-color: var(--or);
}

.formulaire-liste-petit {
    width: 100%;
    padding: 0.4rem 0.6rem;
    background: var(--panneau);
    border: 1px solid var(--or-bordure);
    border-radius: 6px;
    color: var(--or-doux);
    font-size: 0.85rem;
    outline: none;
    cursor: pointer;
}

.formulaire-liste-petit:focus {
    border-color: var(--or);
}

.grille-cases {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 0.5rem;
}

.element-case {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.5rem;
    background: var(--panneau-carte);
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.8rem;
    color: var(--or-doux);
}

.element-case:hover {
    background: var(--panneau-survol);
}

.element-case input[type="checkbox"] {
    accent-color: var(--or);
}

.zone-texte-petite {
    width: 100%;
    padding: 0.5rem 0.7rem;
    background: var(--panneau);
    border: 1px solid var(--or-bordure);
    border-radius: 8px;
    color: var(--or-doux);
    font-size: 0.85rem;
    outline: none;
    resize: vertical;
    min-height: 60px;
}

.zone-texte-petite:focus {
    border-color: var(--or);
}

@media (max-width: 900px) {
    .fiche-grille-6 {
        grid-template-columns: repeat(3, 1fr);
    }
    .fiche-grille-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .fiche-grille-6 {
        grid-template-columns: repeat(2, 1fr);
    }
    .fiche-grille-2 {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('contenu')
<div class="fiche-conteneur">
    @if ($errors->any())
        <div class="alerte alerte-danger" role="alert">
            <strong>La sauvegarde a échoué.</strong>
            <ul>
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('personnages.update', $personnage) }}" method="POST" id="formulaireEdition">
        @csrf
        @method('PATCH')

    <div class="fiche-entete">
        <div class="fiche-entete-gauche">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <a href="{{ route('personnages.show', $personnage) }}" class="texte-attenue" style="text-decoration: none; font-size: 0.85rem;">
                    ← Retour à la fiche
                </a>
            </div>
            <h2 class="titre-principal" style="margin-bottom: 0;">Modifier le personnage</h2>
        </div>
        <div class="fiche-entete-droite">
            <div class="stat-bloc">
                <div class="stat-bloc-etiquette">Niveau</div>
                <div class="stat-bloc-valeur-fixe" title="Le niveau évolue par la montée de niveau">{{ $personnage->niveau }}</div>
            </div>
            <div class="stat-bloc">
                <div class="stat-bloc-etiquette">PV</div>
                <input type="number" name="point_de_vie" value="{{ old('point_de_vie', $personnage->point_de_vie) }}" min="1"
                    style="width:55px;">
            </div>
        </div>
    </div>

        <div class="fiche-section">
            <div class="fiche-section-titre">Identité</div>
            <div class="fiche-grille-4">
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Nom</label>
                    <input type="text" name="nom" value="{{ old('nom', $personnage->nom) }}" class="formulaire-saisie-petit" required>
                </div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Race</label>
                    <select name="race_id" id="raceListe" class="formulaire-liste-petit" required onchange="chargerSousRaces()">
                        <option value="">-- Choisir --</option>
                        @foreach($races as $race)
                            <option value="{{ $race->id }}" {{ old('race_id', $personnage->race_id) == $race->id ? 'selected' : '' }}>
                                {{ $race->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Sous-race</label>
                    @php
                        $raceActuelle = $races->firstWhere('id', old('race_id', $personnage->race_id));
                        $sousRaceRequise = $raceActuelle && $raceActuelle->sousRaces->isNotEmpty();
                    @endphp
                    <select name="sous_race_id" id="sousRaceListe" class="formulaire-liste-petit" {{ $sousRaceRequise ? 'required' : '' }}>
                        <option value="">-- Aucune --</option>
                        @if($raceActuelle)
                            @foreach($raceActuelle->sousRaces as $sousRace)
                                <option value="{{ $sousRace->id }}" {{ old('sous_race_id', $personnage->sous_race_id) == $sousRace->id ? 'selected' : '' }}>
                                    {{ $sousRace->nom }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Alignement</label>
                    <select name="alignement" class="formulaire-liste-petit">
                        <option value="">-- Choisir --</option>
                        @foreach([
                            'Loyal bon', 'Neutre bon', 'Chaotique bon',
                            'Loyal neutre', 'Neutre', 'Chaotique neutre',
                            'Loyal méchant', 'Neutre méchant', 'Chaotique méchant',
                        ] as $optionAlignement)
                            <option value="{{ $optionAlignement }}" {{ old('alignement', $personnage->alignement ?? '') == $optionAlignement ? 'selected' : '' }}>
                                {{ $optionAlignement }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="fiche-grille-2 marge-haut-2">
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Classe</label>
                    <select name="classe_id" id="classeListe" class="formulaire-liste-petit" required onchange="chargerSousClasses(); avertirChangementClasse()">
                        <option value="">-- Choisir --</option>
                        @foreach($classes as $classe)
                            <option value="{{ $classe->id }}" data-niveau-sous-classe="{{ $classe->niveau_sous_classe }}" data-a-des-sous-classes="{{ $classe->sousClasses->isNotEmpty() ? '1' : '0' }}" {{ old('classe_id', $personnage->classe_id) == $classe->id ? 'selected' : '' }}>
                                {{ $classe->nom }}
                            </option>
                        @endforeach
                    </select>
                    <div id="avertissementClasse" class="texte-attenue" style="display: none; font-size: 0.75rem; margin-top: 0.35rem; color: var(--or-doux);">
                        Changer de classe réinitialisera les sorts du personnage.
                    </div>
                </div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Sous-classe</label>
                    @php
                        $classeActuelle = $classes->firstWhere('id', old('classe_id', $personnage->classe_id));
                        $sousClasseRequise = $classeActuelle
                            && $classeActuelle->sousClasses->isNotEmpty()
                            && $personnage->niveau >= $classeActuelle->niveau_sous_classe;
                        $sousClasseBloquee = $classeActuelle && $classeActuelle->sousClasses->isNotEmpty() && $personnage->niveau < $classeActuelle->niveau_sous_classe;
                    @endphp
                    <select name="sous_classe_id" id="sousClasseListe" class="formulaire-liste-petit" {{ $sousClasseBloquee ? 'disabled' : '' }} {{ $sousClasseRequise ? 'required' : '' }}>
                        <option value="">-- Aucune --</option>
                        @if($classeActuelle && ! $sousClasseBloquee)
                            @foreach($classeActuelle->sousClasses as $sousClasse)
                                <option value="{{ $sousClasse->id }}" {{ old('sous_classe_id', $personnage->sous_classe_id) == $sousClasse->id ? 'selected' : '' }}>
                                    {{ $sousClasse->nom }} (Niv. {{ $classeActuelle->niveau_sous_classe }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <div id="sousClasseMessage" class="texte-attenue" style="font-size: 0.75rem; margin-top: 0.3rem;">
                        @if($sousClasseBloquee)
                            Sous-classe disponible au niveau {{ $classeActuelle->niveau_sous_classe }} (personnage actuellement niveau {{ $personnage->niveau }}).
                        @endif
                    </div>
                </div>
            </div>
            <div class="formulaire-groupe marge-haut-2">
                <label class="formulaire-etiquette">Description / Backstory</label>
                <textarea name="description" class="zone-texte-petite" rows="3">{{ old('description', $personnage->description ?? '') }}</textarea>
            </div>
        </div>

        <div class="fiche-section">
            <div class="fiche-section-titre">Caractéristiques</div>
            <p class="note-montee-niveau">Le niveau et les caractéristiques évoluent uniquement par la montée de niveau (amélioration de caractéristiques ou don).</p>
            <div class="fiche-grille-6">
                @php
                    $stats = [
                        'force' => 'Force',
                        'dexterite' => 'Dex',
                        'constitution' => 'Con',
                        'intelligence' => 'Int',
                        'sagesse' => 'Sag',
                        'charisme' => 'Cha',
                    ];
                @endphp
                @foreach($stats as $cle => $libelle)
                    @php
                        $valeur = $personnage->abilites->$cle ?? 10;
                        $mod = floor(($valeur - 10) / 2);
                        $modChaine = $mod >= 0 ? '+' . $mod : $mod;
                    @endphp
                    <div class="boite-aptitude">
                        <div class="nom-aptitude">{{ $libelle }}</div>
                        <div class="champ-aptitude">{{ $valeur }}</div>
                        <div class="mod-aptitude">{{ $modChaine }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="fiche-grille-2">
            <div class="fiche-section">
                <div class="fiche-section-titre">Compétences de classe (max {{ $personnage->classe->nombre_competences ?? 2 }})</div>
                @if($personnage->historique?->competences)
                    <div class="texte-attenue" style="font-size: 0.8rem; margin-bottom: 0.75rem;">
                        Compétences d’historique incluses et non modifiables : {{ implode(', ', $personnage->historique->competences) }}
                    </div>
                @endif
                <div class="grille-cases">
                    @php
                        $competencesActuelles = old('competences', $personnage->listeCompetence->competences ?? []);
                        $competencesHistorique = $personnage->historique?->competences ?? [];
                    @endphp
                    @foreach($toutesCompetences as $comp)
                        @php $estAccordeeParHistorique = in_array($comp, $competencesHistorique, true); @endphp
                        <label class="element-case">
                            <input type="checkbox" name="competences[]" value="{{ $comp }}"
                                {{ in_array($comp, $competencesActuelles, true) ? 'checked' : '' }}
                                {{ $estAccordeeParHistorique ? 'disabled' : '' }}>
                            @if($estAccordeeParHistorique)
                                <input type="hidden" name="competences[]" value="{{ $comp }}">
                            @endif
                            {{ $comp }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="fiche-section">
                <div class="fiche-section-titre">Historique & Contexte</div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Historique *</label>
                    <select name="historique_id" id="edition_historique_liste" class="formulaire-liste-petit" required onchange="changerHistoriqueEdition(this.value)">
                        <option value="">-- Choisir un historique --</option>
                        @foreach($historiques as $h)
                            <option value="{{ $h->id }}" {{ old('historique_id', $personnage->historique_id) == $h->id ? 'selected' : '' }}>
                                {{ $h->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Idéal *</label>
                    <select name="ideal_id" id="edition_ideal_liste" class="formulaire-liste-petit" required>
                        <option value="">-- Choisir un idéal --</option>
                        @foreach($historiques as $h)
                            @foreach($h->ideaux as $element)
                                <option value="{{ $element->id }}" data-historique="{{ $h->id }}" {{ old('ideal_id', $personnage->ideal_id) == $element->id ? 'selected' : '' }}>
                                    {{ $element->valeur }}{{ $element->alignement ? ' (' . $element->alignement . ')' : '' }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Défaut *</label>
                    <select name="defaut_id" id="edition_defaut_liste" class="formulaire-liste-petit" required>
                        <option value="">-- Choisir un défaut --</option>
                        @foreach($historiques as $h)
                            @foreach($h->defauts as $element)
                                <option value="{{ $element->id }}" data-historique="{{ $h->id }}" {{ old('defaut_id', $personnage->defaut_id) == $element->id ? 'selected' : '' }}>
                                    {{ $element->valeur }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Attachement *</label>
                    <select name="attachement_id" id="edition_attachement_liste" class="formulaire-liste-petit" required>
                        <option value="">-- Choisir un attachement --</option>
                        @foreach($historiques as $h)
                            @foreach($h->attachements as $element)
                                <option value="{{ $element->id }}" data-historique="{{ $h->id }}" {{ old('attachement_id', $personnage->attachement_id) == $element->id ? 'selected' : '' }}>
                                    {{ $element->valeur }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Trait de personnalité *</label>
                    <select name="personnalite_id" id="edition_personnalite_liste" class="formulaire-liste-petit" required>
                        <option value="">-- Choisir une personnalité --</option>
                        @foreach($historiques as $h)
                            @foreach($h->personnalites as $element)
                                <option value="{{ $element->id }}" data-historique="{{ $h->id }}" {{ old('personnalite_id', $personnage->personnalite_id) == $element->id ? 'selected' : '' }}>
                                    {{ $element->valeur }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="formulaire-groupe">
                    <label class="formulaire-etiquette">Langues (max {{ 2 + (int)floor(($personnage->niveau - 1) / 3) }})</label>
                    <div class="grille-cases">
                        @php
                            $languesActuelles = old('langues', $personnage->langues ?? []);
                        @endphp
                        @foreach($langues as $langue)
                            <label class="element-case">
                                <input type="checkbox" name="langues[]" value="{{ $langue }}"
                                    {{ in_array($langue, $languesActuelles) ? 'checked' : '' }}>
                                {{ $langue }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="bouton-groupe-ligne">
            <a href="{{ route('personnages.show', $personnage) }}" class="bouton bouton-secondaire">Annuler</a>
            <button type="submit" class="bouton bouton-principal">Sauvegarder</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Les <option hidden> ne sont pas fiables dans une <select> native (surtout
// Firefox, qui les affiche quand même dans la liste déroulante) : on garde
// donc la liste complète de chaque select en mémoire et on reconstruit le
// contenu du select à partir de là à chaque changement d'historique, au
// lieu de simplement masquer les options qui ne correspondent pas.
const reserveOptionsHistorique = {};

function initialiserReserveOptionsHistorique(idListe) {
    const liste = document.getElementById(idListe);
    if (!liste) return;

    reserveOptionsHistorique[idListe] = {
        placeholder: liste.options[0].outerHTML,
        courant: liste.value,
        options: Array.from(liste.options).slice(1).map(opt => ({
            valeur: opt.value,
            historique: opt.dataset.historique,
            html: opt.innerHTML,
        })),
    };
}

function filtrerOptionsEdition(idListe, historiqueId) {
    const liste = document.getElementById(idListe);
    const reserve = reserveOptionsHistorique[idListe];
    if (!liste || !reserve) return;

    // Tant qu'aucun historique n'est choisi, ces champs sont entièrement
    // bloqués (comme "Outil" à l'étape Contexte de la création) : ils ne
    // dépendent que de l'historique, donc rien n'est sélectionnable avant.
    const bloque = historiqueId === '' || historiqueId === null || historiqueId === undefined;
    liste.disabled = bloque;

    const optionsCorrespondantes = bloque
        ? []
        : reserve.options.filter(opt => opt.historique == historiqueId);

    liste.innerHTML = reserve.placeholder + optionsCorrespondantes.map(opt =>
        '<option value="' + opt.valeur + '"' + (opt.valeur === reserve.courant ? ' selected' : '') + '>' + opt.html + '</option>'
    ).join('');
}

function changerHistoriqueEdition(historiqueId) {
    filtrerOptionsEdition('edition_ideal_liste', historiqueId);
    filtrerOptionsEdition('edition_defaut_liste', historiqueId);
    filtrerOptionsEdition('edition_attachement_liste', historiqueId);
    filtrerOptionsEdition('edition_personnalite_liste', historiqueId);
}

document.addEventListener('DOMContentLoaded', function () {
    initialiserReserveOptionsHistorique('edition_ideal_liste');
    initialiserReserveOptionsHistorique('edition_defaut_liste');
    initialiserReserveOptionsHistorique('edition_attachement_liste');
    initialiserReserveOptionsHistorique('edition_personnalite_liste');

    const formulaire = document.getElementById('formulaireEdition');
    formulaire.addEventListener('invalid', function (evenement) {
        if (evenement.target.validity.valueMissing) {
            evenement.target.setCustomValidity(
                evenement.target.tagName === 'SELECT'
                    ? 'Veuillez sélectionner un élément dans la liste.'
                    : 'Veuillez remplir ce champ.'
            );
        }
    }, true);
    formulaire.addEventListener('input', evenement => evenement.target.setCustomValidity(''));
    formulaire.addEventListener('change', evenement => evenement.target.setCustomValidity(''));

    const choixHist = document.getElementById('edition_historique_liste');
    if (choixHist && choixHist.value) {
        changerHistoriqueEdition(choixHist.value);
    } else {
        filtrerOptionsEdition('edition_ideal_liste', '');
        filtrerOptionsEdition('edition_defaut_liste', '');
        filtrerOptionsEdition('edition_attachement_liste', '');
        filtrerOptionsEdition('edition_personnalite_liste', '');
    }
});

function chargerSousRaces() {
    const raceId = document.getElementById('raceListe').value;
    const liste = document.getElementById('sousRaceListe');
    const sousRacesParRace = @json($races->mapWithKeys(fn ($race) => [$race->id => $race->sousRaces->map(fn ($sousRace) => ['id' => $sousRace->id, 'nom' => $sousRace->nom])]));
    const sousRaces = sousRacesParRace[raceId] || [];
    const conserverSelection = raceId === @json((string) old('race_id', $personnage->race_id));
    const courant = conserverSelection ? @json(old('sous_race_id', $personnage->sous_race_id ?? '')) : '';

    liste.innerHTML = '<option value="">-- Aucune --</option>';
    liste.required = sousRaces.length > 0;

    sousRaces.forEach(sr => {
        const opt = document.createElement('option');
        opt.value = sr.id;
        opt.textContent = sr.nom;
        if (sr.id == courant) opt.selected = true;
        liste.appendChild(opt);
    });
}

function avertirChangementClasse() {
    const changee = document.getElementById('classeListe').value !== @json((string) $personnage->classe_id);
    document.getElementById('avertissementClasse').style.display = changee ? 'block' : 'none';
}

function chargerSousClasses() {
    const classeId = document.getElementById('classeListe').value;
    const liste = document.getElementById('sousClasseListe');
    const message = document.getElementById('sousClasseMessage');
    const sousClassesParClasse = @json($classes->mapWithKeys(fn ($classe) => [$classe->id => $classe->sousClasses->map(fn ($sousClasse) => ['id' => $sousClasse->id, 'nom' => $sousClasse->nom])]));
    const sousClasses = sousClassesParClasse[classeId] || [];
    const optionClasse = document.querySelector('#classeListe option[value="' + classeId + '"]');
    const niveauRequis = optionClasse ? parseInt(optionClasse.dataset.niveauSousClasse, 10) : 0;
    const niveauPersonnage = {{ $personnage->niveau }};
    const courant = @json(old('sous_classe_id', $personnage->sous_classe_id ?? ''));

    liste.innerHTML = '<option value="">-- Aucune --</option>';
    liste.disabled = false;
    liste.required = sousClasses.length > 0;
    if (message) message.textContent = '';

    if (!classeId) return;

    const aDesSousClasses = optionClasse && optionClasse.dataset.aDesSousClasses === '1';

    if (aDesSousClasses && niveauPersonnage < niveauRequis) {
        liste.disabled = true;
        liste.required = false;
        if (message) {
            message.textContent = 'Sous-classe disponible au niveau ' + niveauRequis + ' (personnage actuellement niveau ' + niveauPersonnage + ').';
        }
        return;
    }

    sousClasses.forEach(sc => {
        const opt = document.createElement('option');
        opt.value = sc.id;
        opt.textContent = sc.nom + ' (Niv. ' + niveauRequis + ')';
        if (sc.id == courant) opt.selected = true;
        liste.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Les options initiales sont déjà rendues côté serveur (voir le select
    // ci-dessus) : on ne recharge via l'API que lors d'un vrai changement
    // de race/classe par l'utilisateur (onchange), pas au chargement de la
    // page, sinon un échec réseau viderait un sélecteur déjà correct.
    avertirChangementClasse();
});
</script>
@endpush
