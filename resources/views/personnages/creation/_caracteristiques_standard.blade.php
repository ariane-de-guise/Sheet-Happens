<form action="{{ route('creation.caracteristiques.enregistrer') }}" method="POST" id="formulairePrincipal">
    @csrf
    <p class="indice-glisser marge-bas-2">Glissez une valeur sur la caractéristique de votre choix (ou cliquez sur une valeur pour l'attribuer automatiquement). Cliquez sur une caractéristique déjà remplie pour la vider, ou glissez-la à nouveau vers le pool.</p>
    <div class="conteneur-points-standard">
        <div class="stats-liste">
            @include('personnages.creation._caracteristiques_bonus')
            @php
                $stats = $libelles ?? [
                    'force' => 'Force',
                    'dexterite' => 'Dextérité',
                    'constitution' => 'Constitution',
                    'intelligence' => 'Intelligence',
                    'sagesse' => 'Sagesse',
                    'charisme' => 'Charisme',
                ];
            @endphp
            @foreach($stats as $cle => $libelle)
                <div class="stat-ligne">
                    <div class="stat-icone"></div>
                    <span class="stat-nom">{{ $libelle }}</span>
                    @php $bonusStat = $bonusParAptitude[$cle] ?? 0; @endphp
                    <span class="stat-bonus-total" data-key="{{ $cle }}" data-value="{{ $bonusStat }}">{{ $bonusStat > 0 ? '+' : '' }}{{ $bonusStat }}</span>
                    <span class="stat-valeur stat-points cible-depot {{ $abilites[$cle] > 0 ? 'remplie' : '' }}"
                          id="stat-{{ $cle }}" data-key="{{ $cle }}"
                          draggable="{{ $abilites[$cle] > 0 ? 'true' : 'false' }}"
                          ondragstart="debutGlisserCaracteristique(event, '{{ $cle }}')"
                          ondragend="finGlisserCaracteristique('{{ $cle }}')"
                          ondragover="survolerEmplacement(event, this)"
                          ondragleave="quitterEmplacement(event, this)"
                          ondrop="deposerSurEmplacement(event, this)"
                          onclick="viderEmplacement('{{ $cle }}')">{{ $abilites[$cle] > 0 ? $abilites[$cle] : 'X' }}</span>
                    <input type="hidden" name="{{ $cle }}" id="champ-{{ $cle }}" value="{{ $abilites[$cle] }}">
                </div>
            @endforeach
        </div>

        <div class="stats-cote">
            <div class="etiquette marge-bas-1" style="color:var(--or);">Valeurs disponibles</div>
            <div class="reserve-valeurs" id="reserveValeurs"
                 ondragover="survolerReserve(event)"
                 ondragleave="quitterReserve(event)"
                 ondrop="deposerSurReserve(event)">
                @foreach($repartitionStandard as $valeur)
                    <div class="jeton-valeur" draggable="true" data-value="{{ $valeur }}"
                         id="jeton-{{ $valeur }}"
                         ondragstart="debutGlisserJeton(event, {{ $valeur }})"
                         ondragend="finGlisserJeton(event)"
                         onclick="attribuerEmplacementLibreSuivant({{ $valeur }})">{{ $valeur }}</div>
                @endforeach
            </div>

            <!-- À côté de la boîte de caractéristiques (2e colonne), collée
                 en bas (voir .stats-side), pas en dessous de celle-ci. -->
            <div class="stats-actions">
                <button type="button" class="bouton bouton-secondaire" onclick="reinitialiserStandard()">Réinitialiser</button>
            </div>
        </div>
    </div>

    <input type="hidden" name="points_disponibles" id="pointsDisponibles">

    <div class="bouton-groupe-ligne">
        <a href="{{ route('creation.methode') }}" class="bouton bouton-secondaire">Retour</a>
        <button type="submit" class="bouton bouton-principal" id="boutonSuivant">Suivant</button>
    </div>
</form>

@push('scripts')
    <script>
        // Données injectées par le serveur pour cette page ; toute la logique
        // vit dans un fichier séparé (public/js/creation/caracteristiques-standard.js).
        window.DonneesCaracteristiquesStandard = {
            abilites: @json($abilites),
        };
    </script>
    <script src="{{ asset('js/creation/caracteristiques-standard.js') }}"></script>
@endpush
