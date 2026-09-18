@if(session('success'))
    <div id="popup-succes" class="modal-overlay active">
        <div class="modal-box">
            <h2>Succès</h2>
            <p>{{ session('success') }}</p>
            <div class="modal-actions">
                <button type="button" class="btn btn-primary btn-small" style="flex: 1;" onclick="fermerPopupSucces()">OK</button>
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
