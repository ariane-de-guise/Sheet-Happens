@auth
<div class="auth-badge">
    <span class="auth-status-dot dot-online"></span>
    <div class="auth-info">
        <span class="auth-email">{{ Auth::user()->courriel }}</span>
        <span class="auth-id">ID: {{ Auth::id() }}</span>
        <a href="{{ route('utilisateurs.profil') }}" class="auth-link">Mon profil</a>
    </div>
</div>
@endauth
