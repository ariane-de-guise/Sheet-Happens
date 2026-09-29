@auth
    <div class="auth-badge">
        <img src="{{ asset('images/logo_rmb.png') }}" alt="Sheet Happens" class="auth-logo">
        <div class="auth-info">
            <span class="auth-courriel">{{ Auth::user()->courriel }}</span>
            <a href="{{ route('utilisateurs.profil') }}" class="auth-lien">Afficher le profil</a>
        </div>
    </div>
@endauth
