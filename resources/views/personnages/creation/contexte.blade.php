@extends('layouts.app')

@section('titre', 'Créer un personnage - Contexte')

@section('contenu')
    <div class="carte">
        <div class="texte-centre marge-bas-3">
            <span class="texte-or">Créer un personnage</span>
        </div>

        <div class="barre-progression">
            <div class="ligne-progression">
                <div class="remplissage-progression" style="width: 100%;"></div>
            </div>
            <div class="etape-progression terminee">1</div>
            <div class="etape-progression terminee">2</div>
            <div class="etape-progression terminee">3</div>
            <div class="etape-progression terminee">4</div>
            <div class="etape-progression terminee">5</div>
            <div class="etape-progression active">6</div>
        </div>

        <h2 class="titre-section texte-centre">Contexte</h2>

        @if(session('erreur'))
            <div class="alerte alerte-erreur">{{ session('erreur') }}</div>
        @endif

        @if($errors->any())
            <div class="alerte alerte-erreur">
                @foreach($errors->all() as $erreur)
                    {{ $erreur }}<br>
                @endforeach
            </div>
        @endif

        <form action="{{ route('creation.contexte.enregistrer') }}" method="POST" id="contexteFormulaire" onsubmit="return validerFormulaireContexte()">
            @csrf

            <div class="formulaire-groupe marge-bas-3">
                <label class="formulaire-etiquette">Nom du personnage *</label>
                <input type="text" name="nom" value="{{ old('nom', $donnees['nom'] ?? '') }}"
                       class="formulaire-saisie" required>
            </div>

            <div class="formulaire-groupe marge-bas-3">
                <label class="formulaire-etiquette">Description / Backstory (optionnel)</label>
                <textarea name="description" class="formulaire-saisie" rows="3">{{ old('description', $donnees['description'] ?? '') }}</textarea>
            </div>

            <div class="section-contexte">
                <div class="entete">
                    <span class="titre">Historique *</span>
                    <span class="indice">Détermine votre équipement de départ</span>
                </div>
                <select name="historique_id" id="historique_liste" class="formulaire-liste marge-haut-2" onchange="changerHistorique(this.value)" required>
                    <option value="">-- Choisir un historique --</option>
                    @foreach($historiques as $h)
                        <option value="{{ $h->id }}" {{ (old('historique_id', $historiqueChoisi) == $h->id) ? 'selected' : '' }}>
                            {{ $h->nom }}
                        </option>
                    @endforeach
                </select>
                <div class="boite-aide marge-haut-2" id="historique_description" style="{{ $historiqueChoisiModele && $historiqueChoisiModele->description ? '' : 'display:none;' }}">
                    <div class="titre" id="historique_description_titre">{{ $historiqueChoisiModele->nom ?? '' }}</div>
                    <div class="contenu" id="historique_description_contenu">{{ $historiqueChoisiModele->description ?? '' }}</div>
                </div>
            </div>

            <div class="section-contexte">
                <div class="entete">
                    <span class="titre">Idéal *</span>
                </div>
                <select name="ideal_id" id="ideal_liste" class="formulaire-liste marge-haut-2" required onchange="majApercuAlignement()">
                    <option value="">-- Choisir un idéal --</option>
                    @foreach($historiques as $h)
                        @foreach($h->ideaux as $element)
                            <option value="{{ $element->id }}" data-historique="{{ $h->id }}" data-alignement="{{ $element->alignement }}" {{ (old('ideal_id', $idealChoisi) == $element->id) ? 'selected' : '' }}>
                                {{ $element->valeur }}{{ $element->alignement ? ' (' . $element->alignement . ')' : '' }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="section-contexte">
                <div class="entete">
                    <span class="titre">Défaut *</span>
                </div>
                <select name="defaut_id" id="defaut_liste" class="formulaire-liste marge-haut-2" required onchange="majApercuAlignement()">
                    <option value="">-- Choisir un défaut --</option>
                    @foreach($historiques as $h)
                        @foreach($h->defauts as $element)
                            <option value="{{ $element->id }}" data-historique="{{ $h->id }}" data-alignement="{{ $element->alignement }}" {{ (old('defaut_id', $defautChoisi) == $element->id) ? 'selected' : '' }}>
                                {{ $element->valeur }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="section-contexte">
                <div class="entete">
                    <span class="titre">Attachement *</span>
                </div>
                <select name="attachement_id" id="attachement_liste" class="formulaire-liste marge-haut-2" required onchange="majApercuAlignement()">
                    <option value="">-- Choisir un attachement --</option>
                    @foreach($historiques as $h)
                        @foreach($h->attachements as $element)
                            <option value="{{ $element->id }}" data-historique="{{ $h->id }}" data-alignement="{{ $element->alignement }}" {{ (old('attachement_id', $attachementChoisi) == $element->id) ? 'selected' : '' }}>
                                {{ $element->valeur }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="section-contexte">
                <div class="entete">
                    <span class="titre">Trait de personnalité *</span>
                </div>
                <select name="personnalite_id" id="personnalite_liste" class="formulaire-liste marge-haut-2" required onchange="majApercuAlignement()">
                    <option value="">-- Choisir une personnalité --</option>
                    @foreach($historiques as $h)
                        @foreach($h->personnalites as $element)
                            <option value="{{ $element->id }}" data-historique="{{ $h->id }}" data-alignement="{{ $element->alignement }}" {{ (old('personnalite_id', $personnaliteChoisie) == $element->id) ? 'selected' : '' }}>
                                {{ $element->valeur }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="section-contexte">
                <div class="entete">
                    <span class="titre">Alignement</span>
                    <span class="indice">Déterminé par vos 4 choix ci-dessus (idéal, défaut, attachement, personnalité)</span>
                </div>
                <ul class="elements marge-haut-2" id="apercuAlignement">
                    <li>Choisissez un idéal pour déterminer l'alignement.</li>
                </ul>
            </div>

            <div class="section-contexte">
                <div class="entete">
                    <span class="titre">Outil</span>
                    <span class="indice" id="outil_indice">Choisissez un historique pour voir les outils disponibles</span>
                </div>
                <select name="outil" id="outil_liste" class="formulaire-liste marge-haut-2" data-old="{{ old('outil', $outilChoisi ?? '') }}" disabled>
                    <option value="">-- Choisissez d'abord un historique --</option>
                </select>
            </div>

            <div class="section-contexte">
                <div class="entete">
                    <span class="titre">Langue</span>
                    <span class="indice">Déterminée par votre race</span>
                </div>
                <ul class="elements marge-haut-2">
                    @foreach($languesRace as $langue)
                        <li>{{ $langue }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="section-contexte">
                <div class="entete">
                    <span class="titre">Équipement</span>
                    <span class="indice">Fourni par votre historique (outils accordés automatiquement inclus)</span>
                </div>
                <ul class="elements marge-haut-2" id="apercuEquipement">
                    <li>Choisissez un historique pour voir votre équipement.</li>
                </ul>
            </div>

            <div class="bouton-groupe-ligne">
                <button type="submit" name="direction" value="back" formnovalidate class="bouton bouton-secondaire">Retour</button>
                <button type="submit" name="direction" value="next" class="bouton bouton-principal">Suivant</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            window.DonneesContexte = {
                historiques: @json($historiques),
                maxLangues: 2,
            };
        </script>
        <script src="{{ asset('js/creation/contexte.js') }}"></script>
    @endpush
@endsection
