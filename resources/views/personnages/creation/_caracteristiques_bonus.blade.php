{{-- Mention des bonus (race, sous-race, classe) en haut de la boîte de
     choix des caractéristiques. Absente de la maquette d'origine, ajoutée
     ici plutôt qu'en encadrés séparés au-dessus de la carte (voir
     caracteristiques.blade.php). Les bonus "au choix" (Humain Variant,
     Demi-elfe, ...) demandent au joueur de choisir la caractéristique
     concernée via un select, plutôt que d'être appliqués automatiquement. --}}
@php
    $auChoixSourceLabels = ['race' => 'race', 'sous_race' => 'sous-race', 'classe' => 'classe'];
@endphp
@if(!empty($bonusFixeMentions) || !empty($auChoixDisponibles))
    <div class="stats-bonus-mention">
        @if(!empty($bonusFixeMentions))
            <div class="bonus-fixe">Bonus : {{ implode(', ', $bonusFixeMentions) }}</div>
        @endif
        @foreach($auChoixDisponibles as $source => $config)
            @for($i = 0; $i < $config['nombre']; $i++)
                <div class="bonus-choix-row">
                    <label for="bonus_choix_{{ $source }}_{{ $i }}">
                        Bonus au choix ({{ $auChoixSourceLabels[$source] ?? $source }}, +{{ $config['bonus'] }}) :
                    </label>
                    <select name="bonus_choix_{{ $source }}[]" id="bonus_choix_{{ $source }}_{{ $i }}"
                            class="bonus-choix-select" data-bonus="{{ $config['bonus'] }}" required>
                        <option value="">-- Choisir --</option>
                        @foreach($labels as $key => $label)
                            <option value="{{ $key }}" {{ (($bonusChoix[$source][$i] ?? null) === $key) ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endfor
        @endforeach
    </div>
@endif

@push('scripts')
    <script>
        // Met à jour en direct le petit badge vert "+X / 0 / -X" à côté de
        // chaque caractéristique quand le joueur choisit (ou change) où placer
        // un bonus "au choix", sans attendre la soumission du formulaire.
        (function () {
            function adjustBonusBadge(key, delta) {
                if (!key || !delta) return;
                var badge = document.querySelector('.stat-bonus-total[data-key="' + key + '"]');
                if (!badge) return;
                var value = (parseInt(badge.dataset.value, 10) || 0) + delta;
                badge.dataset.value = value;
                badge.textContent = value > 0 ? ('+' + value) : String(value);
            }

            document.querySelectorAll('.bonus-choix-select').forEach(function (select) {
                select.dataset.current = select.value;
                select.addEventListener('change', function () {
                    var bonus = parseInt(select.dataset.bonus, 10) || 0;
                    adjustBonusBadge(select.dataset.current, -bonus);
                    adjustBonusBadge(select.value, bonus);
                    select.dataset.current = select.value;
                });
            });
        })();
    </script>
@endpush
