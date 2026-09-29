<div id="modale-suppression" class="modale-voile">
    <div class="modale-boite">
        <h2>Supprimer ce personnage ?</h2>
        <p id="modale-suppression-texte">Cette action est irréversible.</p>
        <div class="modale-actions">
            <button type="button" class="bouton bouton-secondaire bouton-petit" style="flex: 1;" onclick="fermerModalSuppression()">Annuler</button>
            <button type="button" class="bouton bouton-danger bouton-petit" style="flex: 1;" onclick="confirmerSuppressionPersonnage()">Supprimer</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let formulairePersonnageASupprimer = null;

    function ouvrirModalSuppression(idFormulaire, nomPersonnage) {
        formulairePersonnageASupprimer = document.getElementById(idFormulaire);
        document.getElementById('modale-suppression-texte').textContent =
            'Voulez-vous vraiment supprimer "' + nomPersonnage + '" ? Cette action est irréversible.';
        document.getElementById('modale-suppression').classList.add('active');
    }

    function fermerModalSuppression() {
        formulairePersonnageASupprimer = null;
        document.getElementById('modale-suppression').classList.remove('active');
    }

    function confirmerSuppressionPersonnage() {
        if (formulairePersonnageASupprimer) {
            formulairePersonnageASupprimer.submit();
        }
    }
</script>
@endpush
