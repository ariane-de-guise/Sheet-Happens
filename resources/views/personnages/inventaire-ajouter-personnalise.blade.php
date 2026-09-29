@extends('layouts.app')

@section('titre', 'Objet custom - ' . $personnage->nom)

@push('styles')
    <style>
        .inventaire-section {
            background: var(--panneau);
            border: 1px solid var(--or-bordure);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .inventaire-section-titre {
            font-size: 1rem;
            font-weight: 600;
            color: var(--or);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--or-ligne-moyenne);
        }

        .formulaire-ligne {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .formulaire-champ {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1;
            min-width: 200px;
        }

        .formulaire-champ-etroit {
            flex: 0 0 130px;
            min-width: 130px;
        }

        .formulaire-champ label {
            font-size: 0.8rem;
            color: var(--or);
            font-weight: 600;
        }

        .formulaire-saisie-petit, .formulaire-liste-petit, .zone-texte-petite {
            width: 100%;
            padding: 0.5rem 0.7rem;
            background: var(--panneau);
            border: 1px solid var(--or-bordure);
            border-radius: 6px;
            color: var(--or-doux);
            font-size: 0.85rem;
            outline: none;
            resize: vertical;
        }

        .formulaire-liste-petit { cursor: pointer; }

        .bonus-grille {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 0.6rem;
        }

        .bonus-grille label {
            display: block;
            font-size: 0.7rem;
            color: var(--texte-secondaire);
            margin-bottom: 0.25rem;
        }

        .champ-groupe {
            margin-bottom: 1rem;
        }

        .champ-groupe > label {
            display: block;
            font-size: 0.8rem;
            color: var(--or);
            font-weight: 600;
            margin-bottom: 0.35rem;
        }

        .ligne-case {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 0.5rem;
            color: var(--or-doux);
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .objet-description {
            color: var(--texte-secondaire);
            font-size: 0.8rem;
            margin-top: 0.75rem;
            font-style: italic;
        }

        .formulaire-actions {
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
        }
    </style>
@endpush

@section('contenu')
    <div class="fiche-conteneur" style="max-width: 900px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
            <div class="texte-or" style="font-size: 1.3rem; font-weight: 700;">Créer un objet custom</div>
            <a href="{{ route('personnages.inventaire.ajouter-page', $personnage) }}" class="bouton bouton-secondaire bouton-petit">← Retour au catalogue</a>
        </div>

        @if($errors->any())
            <div class="inventaire-section" style="border-color: var(--alerte-erreur-bordure);">
                @foreach($errors->all() as $erreur)
                    <div>{{ $erreur }}</div>
                @endforeach
            </div>
        @endif

        <div class="inventaire-section">
            <form action="{{ route('personnages.inventaire.ajouter-personnalise', $personnage) }}" method="POST">
                @csrf

                <div class="formulaire-ligne">
                    <div class="formulaire-champ">
                        <label for="persoNom">Nom</label>
                        <input type="text" name="nom" id="persoNom" class="formulaire-saisie-petit" required maxlength="100">
                    </div>
                    <div class="formulaire-champ">
                        <label for="persoType">Type</label>
                        <select name="type" id="persoType" class="formulaire-liste-petit" required onchange="majChampsSelonType()">
                            <option value="autre">Autre</option>
                            <option value="arme">Arme</option>
                            <option value="armure">Armure</option>
                            <option value="bouclier">Bouclier</option>
                            <option value="potion">Potion</option>
                            <option value="outil">Outil</option>
                        </select>
                    </div>
                    <div class="formulaire-champ" id="persoCategorieConteneur" style="display: none;">
                        <label for="persoCategorie">Catégorie</label>
                        <select name="categorie" id="persoCategorie" class="formulaire-liste-petit"></select>
                    </div>
                </div>

                <div class="champ-groupe">
                    <label for="persoDescription">Description</label>
                    <textarea name="description" id="persoDescription" class="zone-texte-petite" rows="2" maxlength="1000"></textarea>
                </div>

                {{-- Magie : rareté 5e --}}
                <label class="ligne-case">
                    <input type="checkbox" name="magique" id="persoMagique" value="1" onchange="majChampsSelonType()">
                    Objet magique
                </label>
                <div class="formulaire-ligne" id="persoRareteConteneur" style="display: none;">
                    <div class="formulaire-champ">
                        <label for="persoRarete">Rareté</label>
                        <select name="rarete" id="persoRarete" class="formulaire-liste-petit">
                            <option value="commun">Commun</option>
                            <option value="peu_commun">Peu commun</option>
                            <option value="rare">Rare</option>
                            <option value="tres_rare">Très rare</option>
                            <option value="legendaire">Légendaire</option>
                            <option value="artefact">Artéfact</option>
                        </select>
                    </div>
                </div>

                {{-- Arme : dégâts --}}
                <div id="persoArmeConteneur" style="display: none;">
                    <div class="formulaire-ligne">
                        <div class="formulaire-champ formulaire-champ-etroit">
                            <label for="persoDegats">Dégâts</label>
                            <input type="text" name="degats" id="persoDegats" class="formulaire-saisie-petit" placeholder="ex. 1d8" maxlength="20">
                        </div>
                        <div class="formulaire-champ formulaire-champ-etroit">
                            <label for="persoTypeDegats">Type de dégâts</label>
                            <select name="type_degats" id="persoTypeDegats" class="formulaire-liste-petit">
                                <option value="tranchant">Tranchant</option>
                                <option value="perforant">Perforant</option>
                                <option value="contondant">Contondant</option>
                            </select>
                        </div>
                        <div class="formulaire-champ formulaire-champ-etroit">
                            <label for="persoCaracteristiqueDegats">Caractéristique</label>
                            <select name="caracteristique_degats" id="persoCaracteristiqueDegats" class="formulaire-liste-petit">
                                <option value="force">Force</option>
                                <option value="dexterite">Dextérité</option>
                            </select>
                        </div>
                        <div class="formulaire-champ formulaire-champ-etroit">
                            <label for="persoCoutMains">Mains nécessaires</label>
                            <select name="cout_mains" id="persoCoutMains" class="formulaire-liste-petit">
                                <option value="1">Une main</option>
                                <option value="2">Deux mains</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Armure : CA de base --}}
                <div id="persoArmureConteneur" style="display: none;">
                    <div class="formulaire-ligne">
                        <div class="formulaire-champ formulaire-champ-etroit">
                            <label for="persoCaBase">CA de base</label>
                            <input type="number" name="ca_base" id="persoCaBase" class="formulaire-saisie-petit" min="1" max="30" placeholder="ex. 14">
                        </div>
                    </div>
                </div>

                {{-- Potion : effet instantané + durée purement informative --}}
                <div id="persoPotionConteneur" style="display: none;">
                    <div class="formulaire-ligne">
                        <div class="formulaire-champ formulaire-champ-etroit">
                            <label for="persoEffetPv">Soin instantané (PV)</label>
                            <input type="number" name="effet_pv" id="persoEffetPv" value="0" min="-100" max="100" class="formulaire-saisie-petit">
                        </div>
                        <div class="formulaire-champ formulaire-champ-etroit">
                            <label for="persoDureeValeur">Durée (facultatif)</label>
                            <input type="number" name="duree_valeur" id="persoDureeValeur" min="1" max="999" class="formulaire-saisie-petit">
                        </div>
                        <div class="formulaire-champ formulaire-champ-etroit">
                            <label for="persoDureeUnite">Unité</label>
                            <select name="duree_unite" id="persoDureeUnite" class="formulaire-liste-petit">
                                <option value="tours">Tours</option>
                                <option value="minutes">Minutes</option>
                            </select>
                        </div>
                    </div>
                    <div class="objet-description" style="margin-top: -0.5rem;" id="persoDureeNote">
                        Si une durée et au moins un bonus ci-dessous sont renseignés, le bonus s'applique dès l'utilisation. En minutes, il disparaît tout seul une fois le temps réel écoulé. En tours, il n'y a pas d'horloge fiable en combat : vous décomptez vous-même les tours restants depuis la section "Effets actifs" de l'inventaire.
                    </div>
                </div>

                {{-- Bonus : d'équipement pour les autres types, temporaire à l'utilisation pour une potion --}}
                <div id="persoBonusConteneur">
                    <div class="champ-groupe">
                        <label id="persoBonusEtiquette">Bonus de caractéristiques (facultatif, actif si équipé)</label>
                        <div class="bonus-grille">
                            @foreach(['force' => 'Force', 'dexterite' => 'Dex', 'constitution' => 'Con', 'intelligence' => 'Int', 'sagesse' => 'Sag', 'charisme' => 'Cha', 'ca' => 'CA', 'bonus_attaque' => 'Att.', 'bonus_degats' => 'Dégâts', 'bonus_jets_sauvegarde' => 'Jets sauv.', 'bonus_competences' => 'Compétences'] as $cle => $libelle)
                                <div>
                                    <label>{{ $libelle }}</label>
                                    <input type="number" name="bonus[{{ $cle }}]" value="0" min="-20" max="20" class="formulaire-saisie-petit">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <label class="ligne-case" id="persoToujoursActifLigne">
                        <input type="checkbox" name="bonus_toujours_actif" value="1">
                        Bonus appliqué en tout temps (sinon, seulement si équipé)
                    </label>
                </div>

                <div class="formulaire-ligne">
                    <div class="formulaire-champ formulaire-champ-etroit">
                        <label for="persoQuantite">Quantité</label>
                        <input type="number" name="quantite" id="persoQuantite" value="1" min="1" max="999" class="formulaire-saisie-petit">
                    </div>
                    <div class="formulaire-actions">
                        <button type="submit" class="bouton bouton-principal">Créer et ajouter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const CATEGORIES_PAR_TYPE = {
            arme: [
                ['une_main', 'Une main'],
                ['deux_mains', 'Deux mains'],
                ['distance', 'Distance'],
            ],
            armure: [
                ['legere', 'Légère'],
                ['intermediaire', 'Intermédiaire'],
                ['lourde', 'Lourde'],
            ],
        };

        // Une catégorie mêlée impose son nombre de mains (une_main = 1, deux_mains
        // = 2) ; seule une arme à distance varie réellement (arbalète de poing =
        // 1 main, arc long = 2 mains), donc son champ reste librement modifiable.
        function majCoutMainsSelonCategorie() {
            const categorie = document.getElementById('persoCategorie').value;
            const choixCoutMains = document.getElementById('persoCoutMains');

            if (categorie === 'une_main') {
                choixCoutMains.value = '1';
            } else if (categorie === 'deux_mains') {
                choixCoutMains.value = '2';
            }
        }

        function majChampsSelonType() {
            const type = document.getElementById('persoType').value;

            const conteneurCategorie = document.getElementById('persoCategorieConteneur');
            const choixCategorie = document.getElementById('persoCategorie');
            const categories = CATEGORIES_PAR_TYPE[type];

            if (categories) {
                conteneurCategorie.style.display = 'flex';
                choixCategorie.innerHTML = categories.map(([valeur, label]) => `<option value="${valeur}">${label}</option>`).join('');
                choixCategorie.onchange = majCoutMainsSelonCategorie;
            } else {
                conteneurCategorie.style.display = 'none';
                choixCategorie.innerHTML = '';
            }

            if (type === 'arme') {
                majCoutMainsSelonCategorie();
            }

            const estPotion = type === 'potion';
            document.getElementById('persoPotionConteneur').style.display = estPotion ? 'block' : 'none';
            document.getElementById('persoArmeConteneur').style.display = type === 'arme' ? 'block' : 'none';
            document.getElementById('persoArmureConteneur').style.display = type === 'armure' ? 'block' : 'none';

            // Le bonus reste utile pour une potion (effet temporaire à
            // l'utilisation), mais "toujours actif" n'a pas de sens pour un objet
            // qui se consomme plutôt que de s'équiper.
            document.getElementById('persoToujoursActifLigne').style.display = estPotion ? 'none' : 'flex';
            document.getElementById('persoBonusEtiquette').textContent = estPotion
                ? 'Bonus temporaire à l\'utilisation (facultatif, voir durée ci-dessus)'
                : 'Bonus de caractéristiques (facultatif, actif si équipé)';

            const magique = document.getElementById('persoMagique').checked;
            document.getElementById('persoRareteConteneur').style.display = magique ? 'flex' : 'none';
        }

        document.addEventListener('DOMContentLoaded', majChampsSelonType);
    </script>
@endpush
