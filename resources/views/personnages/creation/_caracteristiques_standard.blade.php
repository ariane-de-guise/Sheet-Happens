<form action="{{ route('creation.caracteristiques.save') }}" method="POST" id="mainForm">
    @csrf
    <p class="drag-hint mb-2">Glissez une valeur sur la caractéristique de votre choix (ou cliquez sur une valeur pour l'attribuer automatiquement). Cliquez sur une caractéristique déjà remplie pour la vider, ou glissez-la à nouveau vers le pool.</p>
    <div class="standard-points-container">
        <div class="stats-list">
            <div class="stat-columns-header">
                <span>Points d'aptitudes</span>
                <span>Bonus de race</span>
                <span>Total</span>
            </div>
            @php
                $stats = [
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
                    <span class="stat-value stat-total stat-points drop-target {{ $abilites[$key] > 0 ? 'filled' : '' }}"
                          id="stat-{{ $key }}" data-key="{{ $key }}"
                          draggable="{{ $abilites[$key] > 0 ? 'true' : 'false' }}"
                          ondragstart="onStatDragStart(event, '{{ $key }}')"
                          ondragend="onStatDragEnd('{{ $key }}')"
                          ondragover="onSlotDragOver(event, this)"
                          ondragleave="onSlotDragLeave(event, this)"
                          ondrop="onSlotDrop(event, this)"
                          onclick="clearSlot('{{ $key }}')">{{ $abilites[$key] > 0 ? $abilites[$key] : 'X' }}</span>
                    <span class="stat-bonus">+{{ $bonusParAptitude[$key] }}</span>
                    <span class="stat-value stat-total stat-computed" data-key="{{ $key }}" data-bonus="{{ $bonusParAptitude[$key] }}">{{ $abilites[$key] > 0 ? (int) $abilites[$key] + $bonusParAptitude[$key] : 'X' }}</span>
                    <input type="hidden" name="{{ $key }}" id="input-{{ $key }}" value="{{ $abilites[$key] }}">
                </div>
            @endforeach
        </div>

        <div>
            <div class="label mb-1" style="color:#e6b84d;">Valeurs disponibles</div>
            <div class="value-pool" id="valuePool"
                 ondragover="onPoolDragOver(event)"
                 ondragleave="onPoolDragLeave(event)"
                 ondrop="onPoolDrop(event)">
                @foreach($standardArray as $value)
                    <div class="value-chip" draggable="true" data-value="{{ $value }}"
                         id="chip-{{ $value }}"
                         ondragstart="onChipDragStart(event, {{ $value }})"
                         ondragend="onChipDragEnd(event)"
                         onclick="assignToNextEmpty({{ $value }})">{{ $value }}</div>
                @endforeach
            </div>
        </div>
    </div>

    <input type="hidden" name="points_disponibles" id="pointsDisponibles">

    <div class="btn-group-row">
        <div>
            <button type="button" class="btn btn-secondary" onclick="resetStandard()">Réinitialiser</button>
            <button type="submit" class="btn btn-primary" id="btnSuivant">Suivant</button>
        </div>
        <a href="{{ route('creation.methode') }}" class="btn btn-secondary">Retour</a>
    </div>
</form>

@push('scripts')
    <script>
        // Données injectées par le serveur pour cette page ; toute la logique
        // vit dans un fichier séparé (public/js/creation/caracteristiques-standard.js).
        window.CaracteristiquesStandardData = {
            abilites: @json($abilites),
        };
    </script>
    <script src="{{ asset('js/creation/caracteristiques-standard.js') }}"></script>
@endpush
