{{--
    Libellé affichant une info-bulle au survol (ou au focus clavier).
    Paramètres : $libelle, $titre, $description, $meta (optionnel).
--}}
<span class="info-bulle" tabindex="0">
    <span class="info-bulle-cible">{{ $libelle }}</span>
    <span class="info-bulle-contenu" role="tooltip">
        <span class="info-bulle-titre">{{ $titre }}</span>
        @if (!empty($meta))
            <span class="info-bulle-meta">{{ $meta }}</span>
        @endif
        <span class="info-bulle-desc">{{ $description ?: 'Aucune description disponible.' }}</span>
    </span>
</span>
