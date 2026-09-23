@extends('layouts.app')

@section('title', 'Objet custom - ' . $personnage->nom)

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
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--or-ligne-moyenne);
        }

        .form-row {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1;
            min-width: 200px;
        }

        .form-field-narrow {
            flex: 0 0 130px;
            min-width: 130px;
        }

        .form-field label {
            font-size: 0.8rem;
            color: var(--or);
            font-weight: 600;
        }

        .form-input-small, .form-select-small, .textarea-small {
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

        .form-select-small { cursor: pointer; }

        .bonus-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 0.6rem;
        }

        .bonus-grid label {
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

        .checkbox-row {
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

        .form-actions {
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
        }
    </style>
@endpush

@section('content')
    <div class="sheet-container" style="max-width: 900px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
            <div class="text-gold" style="font-size: 1.3rem; font-weight: 700;">Créer un objet custom</div>
            <a href="{{ route('personnages.inventaire.ajouter-page', $personnage) }}" class="btn btn-secondary btn-small">← Retour au catalogue</a>
        </div>

        @if($errors->any())
            <div class="inventaire-section" style="border-color: var(--alerte-erreur-bordure);">
                @foreach($errors->all() as $erreur)
                    <div>{{ $erreur }}</div>
                @endforeach
            </div>
        @endif

        <div class="inventaire-section">
            <form action="{{ route('personnages.inventaire.ajouter-custom', $personnage) }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-field">
                        <label for="customNom">Nom</label>
                        <input type="text" name="nom" id="customNom" class="form-input-small" required maxlength="100">
                    </div>
                    <div class="form-field">
                        <label for="customType">Type</label>
                        <select name="type" id="customType" class="form-select-small" required onchange="majChampsSelonType()">
                            <option value="autre">Autre</option>
                            <option value="arme">Arme</option>
                            <option value="armure">Armure</option>
                            <option value="bouclier">Bouclier</option>
                            <option value="potion">Potion</option>
                            <option value="outil">Outil</option>
                        </select>
                    </div>
                    <div class="form-field" id="customCategorieWrapper" style="display: none;">
                        <label for="customCategorie">Catégorie</label>
                        <select name="categorie" id="customCategorie" class="form-select-small"></select>
                    </div>
                </div>

                <div class="champ-groupe">
                    <label for="customDescription">Description</label>
                    <textarea name="description" id="customDescription" class="textarea-small" rows="2" maxlength="1000"></textarea>
                </div>

                {{-- Magie : rareté 5e --}}
                <label class="checkbox-row">
                    <input type="checkbox" name="magique" id="customMagique" value="1" onchange="majChampsSelonType()">
                    Objet magique
                </label>
                <div class="form-row" id="customRareteWrapper" style="display: none;">
                    <div class="form-field">
                        <label for="customRarete">Rareté</label>
                        <select name="rarete" id="customRarete" class="form-select-small">
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
                <div id="customArmeWrapper" style="display: none;">
                    <div class="form-row">
                        <div class="form-field form-field-narrow">
                            <label for="customDegats">Dégâts</label>
                            <input type="text" name="degats" id="customDegats" class="form-input-small" placeholder="ex. 1d8" maxlength="20">
                        </div>
                        <div class="form-field form-field-narrow">
                            <label for="customTypeDegats">Type de dégâts</label>
                            <select name="type_degats" id="customTypeDegats" class="form-select-small">
                                <option value="tranchant">Tranchant</option>
                                <option value="perforant">Perforant</option>
                                <option value="contondant">Contondant</option>
                            </select>
                        </div>
                        <div class="form-field form-field-narrow">
                            <label for="customCaracteristiqueDegats">Caractéristique</label>
                            <select name="caracteristique_degats" id="customCaracteristiqueDegats" class="form-select-small">
                                <option value="force">Force</option>
                                <option value="dexterite">Dextérité</option>
                            </select>
                        </div>
                        <div class="form-field form-field-narrow">
                            <label for="customCoutMains">Mains nécessaires</label>
                            <select name="cout_mains" id="customCoutMains" class="form-select-small">
                                <option value="1">Une main</option>
                                <option value="2">Deux mains</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Armure : CA de base --}}
                <div id="customArmureWrapper" style="display: none;">
                    <div class="form-row">
                        <div class="form-field form-field-narrow">
                            <label for="customCaBase">CA de base</label>
                            <input type="number" name="ca_base" id="customCaBase" class="form-input-small" min="1" max="30" placeholder="ex. 14">
                        </div>
                    </div>
                </div>

                {{-- Potion : effet instantané + durée purement informative --}}
                <div id="customPotionWrapper" style="display: none;">
                    <div class="form-row">
                        <div class="form-field form-field-narrow">
                            <label for="customEffetPv">Soin instantané (PV)</label>
                            <input type="number" name="effet_pv" id="customEffetPv" value="0" min="-100" max="100" class="form-input-small">
                        </div>
                        <div class="form-field form-field-narrow">
                            <label for="customDureeValeur">Durée (facultatif)</label>
                            <input type="number" name="duree_valeur" id="customDureeValeur" min="1" max="999" class="form-input-small">
                        </div>
                        <div class="form-field form-field-narrow">
                            <label for="customDureeUnite">Unité</label>
                            <select name="duree_unite" id="customDureeUnite" class="form-select-small">
                                <option value="tours">Tours</option>
                                <option value="minutes">Minutes</option>
                            </select>
                        </div>
                    </div>
                    <div class="objet-description" style="margin-top: -0.5rem;" id="customDureeNote">
                        Si une durée et au moins un bonus ci-dessous sont renseignés, le bonus s'applique dès l'utilisation. En minutes, il disparaît tout seul une fois le temps réel écoulé. En tours, il n'y a pas d'horloge fiable en combat : vous décomptez vous-même les tours restants depuis la section "Effets actifs" de l'inventaire.
                    </div>
                </div>

                {{-- Bonus : d'équipement pour les autres types, temporaire à l'utilisation pour une potion --}}
                <div id="customBonusWrapper">
                    <div class="champ-groupe">
                        <label id="customBonusLabel">Bonus de caractéristiques (facultatif, actif si équipé)</label>
                        <div class="bonus-grid">
                            @foreach(['force' => 'Force', 'dexterite' => 'Dex', 'constitution' => 'Con', 'intelligence' => 'Int', 'sagesse' => 'Sag', 'charisme' => 'Cha', 'ca' => 'CA', 'bonus_attaque' => 'Att.', 'bonus_degats' => 'Dégâts', 'bonus_jets_sauvegarde' => 'Jets sauv.', 'bonus_competences' => 'Compétences'] as $cle => $label)
                                <div>
                                    <label>{{ $label }}</label>
                                    <input type="number" name="bonus[{{ $cle }}]" value="0" min="-20" max="20" class="form-input-small">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <label class="checkbox-row" id="customToujoursActifRow">
                        <input type="checkbox" name="bonus_toujours_actif" value="1">
                        Bonus appliqué en tout temps (sinon, seulement si équipé)
                    </label>
                </div>

                <div class="form-row">
                    <div class="form-field form-field-narrow">
                        <label for="customQuantite">Quantité</label>
                        <input type="number" name="quantite" id="customQuantite" value="1" min="1" max="999" class="form-input-small">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Créer et ajouter</button>
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
            const categorie = document.getElementById('customCategorie').value;
            const coutMainsSelect = document.getElementById('customCoutMains');

            if (categorie === 'une_main') {
                coutMainsSelect.value = '1';
            } else if (categorie === 'deux_mains') {
                coutMainsSelect.value = '2';
            }
        }

        function majChampsSelonType() {
            const type = document.getElementById('customType').value;

            const categorieWrapper = document.getElementById('customCategorieWrapper');
            const categorieSelect = document.getElementById('customCategorie');
            const categories = CATEGORIES_PAR_TYPE[type];

            if (categories) {
                categorieWrapper.style.display = 'flex';
                categorieSelect.innerHTML = categories.map(([value, label]) => `<option value="${value}">${label}</option>`).join('');
                categorieSelect.onchange = majCoutMainsSelonCategorie;
            } else {
                categorieWrapper.style.display = 'none';
                categorieSelect.innerHTML = '';
            }

            if (type === 'arme') {
                majCoutMainsSelonCategorie();
            }

            const estPotion = type === 'potion';
            document.getElementById('customPotionWrapper').style.display = estPotion ? 'block' : 'none';
            document.getElementById('customArmeWrapper').style.display = type === 'arme' ? 'block' : 'none';
            document.getElementById('customArmureWrapper').style.display = type === 'armure' ? 'block' : 'none';

            // Le bonus reste utile pour une potion (effet temporaire à
            // l'utilisation), mais "toujours actif" n'a pas de sens pour un objet
            // qui se consomme plutôt que de s'équiper.
            document.getElementById('customToujoursActifRow').style.display = estPotion ? 'none' : 'flex';
            document.getElementById('customBonusLabel').textContent = estPotion
                ? 'Bonus temporaire à l\'utilisation (facultatif, voir durée ci-dessus)'
                : 'Bonus de caractéristiques (facultatif, actif si équipé)';

            const magique = document.getElementById('customMagique').checked;
            document.getElementById('customRareteWrapper').style.display = magique ? 'flex' : 'none';
        }

        document.addEventListener('DOMContentLoaded', majChampsSelonType);
    </script>
@endpush
