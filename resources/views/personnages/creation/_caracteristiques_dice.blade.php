<form action="{{ route('creation.caracteristiques.save') }}" method="POST" id="mainForm">
    @csrf
    <p class="drag-hint mb-2">4d6, on garde les 3 meilleurs — 6 lancers au total. Glissez ensuite chaque résultat sur la caractéristique de votre choix (ou glissez une valeur déjà placée vers le pool pour l'annuler).</p>

    <div class="stats-container">
        <div class="stats-list">
            @include('personnages.creation._caracteristiques_bonus')
            @php
                $stats = $labels ?? [
                    'force' => 'Force',
                    'dexterite' => 'Dextérité',
                    'constitution' => 'Constitution',
                    'intelligence' => 'Intelligence',
                    'sagesse' => 'Sagesse',
                    'charisme' => 'Charisme',
                ];
            @endphp
            @foreach($stats as $key => $label)
                <div class="stat-row">
                    <div class="stat-icon"></div>
                    <span class="stat-name">{{ $label }}</span>
                    @php $bonusStat = $bonusParAptitude[$key] ?? 0; @endphp
                    <span class="stat-bonus-total" data-key="{{ $key }}" data-value="{{ $bonusStat }}">{{ $bonusStat > 0 ? '+' : '' }}{{ $bonusStat }}</span>
                    <span class="stat-value stat-points drop-target {{ $abilites[$key] > 0 ? 'filled' : '' }}"
                          id="stat-{{ $key }}" data-key="{{ $key }}"
                          draggable="{{ $abilites[$key] > 0 ? 'true' : 'false' }}"
                          ondragstart="onStatDragStart(event, '{{ $key }}')"
                          ondragend="onStatDragEnd('{{ $key }}')"
                          ondragover="onSlotDragOver(event, this)"
                          ondragleave="onSlotDragLeave(event, this)"
                          ondrop="onSlotDrop(event, this)"
                          onclick="clearSlot('{{ $key }}')">{{ $abilites[$key] > 0 ? $abilites[$key] : '0' }}</span>
                    <input type="hidden" name="{{ $key }}" id="input-{{ $key }}" value="{{ $abilites[$key] }}">
                </div>
            @endforeach
        </div>

        <div class="stats-side">
            <div class="label mb-1" style="color:var(--or);">Résultats des lancers</div>
            <div class="value-pool" id="valuePool"
                 ondragover="onPoolDragOver(event)"
                 ondragleave="onPoolDragLeave(event)"
                 ondrop="onPoolDrop(event)"></div>

            <!-- À côté de la boîte de caractéristiques (2e colonne), collée
                 en bas (voir .stats-side), pas en dessous de celle-ci. -->
            <div class="stats-actions">
                <button type="button" class="btn btn-primary" id="btnRoll" onclick="rollDice()">
                    {{ $dicePool ? 'Relancer les dés' : 'Lancer les dés' }}
                </button>
                <button type="button" class="btn btn-secondary" id="btnResetAssignments" onclick="resetAssignments()">
                    Réinitialiser les choix
                </button>
            </div>
        </div>
    </div>

    <div class="btn-group-row">
        <a href="{{ route('creation.methode') }}" class="btn btn-secondary">Retour</a>
        <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
    </div>
</form>

@push('scripts')
    <script>
        // Données injectées par le serveur pour cette page ; toute la logique
        // vit dans un fichier séparé (public/js/creation/caracteristiques-dice.js).
        window.CaracteristiquesDiceData = {
            abilites: @json($abilites),
            dicePool: @json($dicePool ?? []),
            rollUrl: '{{ route('creation.roll') }}',
        };
    </script>
    <script src="{{ asset('js/creation/caracteristiques-dice.js') }}"></script>
@endpush
