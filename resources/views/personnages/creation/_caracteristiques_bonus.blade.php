{{-- Mention des bonus (race, sous-race, classe) en haut de la boîte de
     choix des caractéristiques. Absente de la maquette d'origine, ajoutée
     ici plutôt qu'en encadrés séparés au-dessus de la carte (voir
     caracteristiques.blade.php). Les bonus "au choix" (Humain Variant,
     Demi-elfe, ...) demandent au joueur de choisir la caractéristique
     concernée via un select, plutôt que d'être appliqués automatiquement. --}}
@php
    $libellesSourcesAuChoix = ['race' => 'race', 'sous_race' => 'sous-race', 'classe' => 'classe'];
@endphp
@if(!empty($bonusFixeMentions) || !empty($auChoixDisponibles))
    <div class="stats-bonus-mention">
        @if(!empty($bonusFixeMentions))
            <div class="bonus-fixe">Bonus : {{ implode(', ', $bonusFixeMentions) }}</div>
        @endif
        @foreach($auChoixDisponibles as $source => $config)
            @for($i = 0; $i < $config['nombre']; $i++)
                <div class="bonus-choix-ligne">
                    <label for="bonus_choix_{{ $source }}_{{ $i }}">
                        Bonus au choix ({{ $libellesSourcesAuChoix[$source] ?? $source }}, +{{ $config['bonus'] }}) :
                    </label>
                    <select name="bonus_choix_{{ $source }}[]" id="bonus_choix_{{ $source }}_{{ $i }}"
                            class="bonus-choix-liste" data-bonus="{{ $config['bonus'] }}" required>
                        <option value="">-- Choisir --</option>
                        @foreach($libelles as $cle => $libelle)
                            <option value="{{ $cle }}" {{ (($bonusChoix[$source][$i] ?? null) === $cle) ? 'selected' : '' }}>{{ $libelle }}</option>
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
            function ajusterBadgeBonus(cle, delta) {
                if (!cle || !delta) return;
                var badge = document.querySelector('.stat-bonus-total[data-key="' + cle + '"]');
                if (!badge) return;
                var valeur = (parseInt(badge.dataset.value, 10) || 0) + delta;
                badge.dataset.value = valeur;
                badge.textContent = valeur > 0 ? ('+' + valeur) : String(valeur);
            }

            document.querySelectorAll('.bonus-choix-liste').forEach(function (liste) {
                liste.dataset.current = liste.value;
                liste.addEventListener('change', function () {
                    var bonus = parseInt(liste.dataset.bonus, 10) || 0;
                    ajusterBadgeBonus(liste.dataset.current, -bonus);
                    ajusterBadgeBonus(liste.value, bonus);
                    liste.dataset.current = liste.value;
                });
            });
        })();
    </script>
@endpush
