@if(session('succes'))
    <div id="popup-succes" class="modale-voile active">
        <div class="modale-boite">
            <h2>Succès</h2>
            <p>{{ session('succes') }}</p>
            <div class="modale-actions">
                <button type="button" class="bouton bouton-principal bouton-petit" style="flex: 1;" onclick="fermerPopupSucces()">OK</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function fermerPopupSucces() {
            const popup = document.getElementById('popup-succes');
            if (popup) {
                popup.classList.remove('active');
            }
        }

        setTimeout(fermerPopupSucces, 4000);
    </script>
    @endpush
@endif
