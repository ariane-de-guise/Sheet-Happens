<form action="{{ route('creation.caracteristiques.enregistrer') }}" method="POST" id="formulairePrincipal">
    @csrf
    <p class="indice-glisser marge-bas-2">4d6, on garde les 3 meilleurs — 6 lancers au total. Glissez ensuite chaque résultat sur la caractéristique de votre choix (ou glissez une valeur déjà placée vers le pool pour l'annuler).</p>

    <div class="stats-conteneur">
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
                          onclick="viderEmplacement('{{ $cle }}')">{{ $abilites[$cle] > 0 ? $abilites[$cle] : '0' }}</span>
                    <input type="hidden" name="{{ $cle }}" id="champ-{{ $cle }}" value="{{ $abilites[$cle] }}">
                </div>
            @endforeach
        </div>

        <div class="stats-cote">
            <div class="etiquette marge-bas-1" style="color:var(--or);">Résultats des lancers</div>
            <div class="reserve-valeurs" id="reserveValeurs"
                 ondragover="survolerReserve(event)"
                 ondragleave="quitterReserve(event)"
                 ondrop="deposerSurReserve(event)"></div>

            <!-- À côté de la boîte de caractéristiques (2e colonne), collée
                 en bas (voir .stats-side), pas en dessous de celle-ci. -->
            <div class="stats-actions">
                <button type="button" class="bouton bouton-principal" id="boutonLancer" onclick="lancerDes()">
                    {{ $reserveDes ? 'Relancer les dés' : 'Lancer les dés' }}
                </button>
                <button type="button" class="bouton bouton-secondaire" id="boutonReinitialiserAttributions" onclick="reinitialiserAttributions()">
                    Réinitialiser les choix
                </button>
            </div>
        </div>
    </div>

    <div class="bouton-groupe-ligne">
        <a href="{{ route('creation.methode') }}" class="bouton bouton-secondaire">Retour</a>
        <button type="submit" class="bouton bouton-principal" id="boutonSuivant">Suivant</button>
    </div>
</form>

@push('scripts')
    <script>
        // Données injectées par le serveur pour cette page ; toute la logique
        // vit dans un fichier séparé (public/js/creation/caracteristiques-des.js).
        window.DonneesCaracteristiquesDes = {
            abilites: @json($abilites),
            reserveDes: @json($reserveDes ?? []),
            urlLancer: '{{ route('creation.lancer-des') }}',
        };
    </script>
    <script src="{{ asset('js/creation/caracteristiques-des.js') }}"></script>
@endpush
