<div id="modal-suppression" class="modal-overlay">
    <div class="modal-box">
        <h2>Supprimer ce personnage ?</h2>
        <p id="modal-suppression-texte">Cette action est irréversible.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary btn-small" style="flex: 1;" onclick="fermerModalSuppression()">Annuler</button>
            <button type="button" class="btn btn-danger btn-small" style="flex: 1;" onclick="confirmerSuppressionPersonnage()">Supprimer</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let formulairePersonnageASupprimer = null;

    function ouvrirModalSuppression(idFormulaire, nomPersonnage) {
        formulairePersonnageASupprimer = document.getElementById(idFormulaire);
        document.getElementById('modal-suppression-texte').textContent =
            'Voulez-vous vraiment supprimer "' + nomPersonnage + '" ? Cette action est irréversible.';
        document.getElementById('modal-suppression').classList.add('active');
    }

    function fermerModalSuppression() {
        formulairePersonnageASupprimer = null;
        document.getElementById('modal-suppression').classList.remove('active');
    }

    function confirmerSuppressionPersonnage() {
        if (formulairePersonnageASupprimer) {
            formulairePersonnageASupprimer.submit();
        }
    }
</script>
@endpush
