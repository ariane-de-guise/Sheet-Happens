<form action="{{ route('creation.caracteristiques.enregistrer') }}" method="POST">
    @csrf
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
                    <input type="number" name="{{ $cle }}" value="{{ $abilites[$cle] }}"
                           min="8" max="15" class="stat-valeur-base" id="stat-{{ $cle }}"
                           data-key="{{ $cle }}">
                    <div class="stat-controles">
                        <button type="button" class="bouton bouton-principal bouton-icone"
                                onclick="modifierStat('{{ $cle }}', -1)">-</button>
                        <button type="button" class="bouton bouton-secondaire bouton-icone"
                                onclick="modifierStat('{{ $cle }}', 1)">+</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            <div class="points-boite">
                <div class="etiquette">Points restants</div>
                <div class="valeur-affichee" id="pointsRestants">27</div>
            </div>

            <div class="boite-aide">
                <div class="titre">Aide</div>
                <div class="contenu">
                    Chaque caractéristique commence à 8. Augmenter une valeur coûte des points croissants :
                    9(1), 10(2), 11(3), 12(4), 13(6), 14(8), 15(10).
                </div>
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
        const tableCouts = {
            8: 0, 9: 1, 10: 2, 11: 3, 12: 4, 13: 6, 14: 8, 15: 10
        };
        const budgetTotal = 27;

        function cout(valeur) {
            return tableCouts[valeur] ?? 0;
        }

        function coutTotal() {
            let total = 0;
            document.querySelectorAll('.stat-valeur-base').forEach(champ => {
                total += cout(parseInt(champ.value) || 8);
            });
            return total;
        }

        function majInterface() {
            const utilise = coutTotal();
            const restant = budgetTotal - utilise;
            const affichage = document.getElementById('pointsRestants');
            affichage.textContent = restant;
            affichage.style.color = restant < 0 ? 'var(--texte-negatif)' : 'var(--or)';
            document.getElementById('boutonSuivant').disabled = restant !== 0;
        }

        function modifierStat(cle, delta) {
            const champ = document.getElementById('stat-' + cle);
            let valeur = parseInt(champ.value) || 8;
            const nouveauCout = cout(valeur + delta);
            const coutActuel = cout(valeur);
            const utilise = coutTotal();

            if (delta > 0 && valeur >= 15) return;
            if (delta > 0 && utilise + (nouveauCout - coutActuel) > budgetTotal) return;
            if (delta < 0 && valeur <= 8) return;

            champ.value = valeur + delta;
            majInterface();
        }

        document.querySelectorAll('.stat-valeur-base').forEach(champ => {
            champ.addEventListener('change', function() {
                let val = parseInt(this.value);
                if (isNaN(val)) val = 8;
                if (val < 8) val = 8;
                if (val > 15) val = 15;
                this.value = val;
                majInterface();
            });
        });

        majInterface();
    </script>
@endpush
