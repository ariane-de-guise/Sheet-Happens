@extends('layouts.app')

@section('title', 'Ajouter un objet - ' . $personnage->nom)

@push('styles')
<style>
.inventaire-section {
    background: rgba(25, 10, 50, 0.9);
    border: 1px solid #c49a3c;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}

.toolbar {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: flex-end;
    margin-bottom: 1rem;
}

.toolbar-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.toolbar-field label {
    font-size: 0.75rem;
    color: #e6b84d;
    font-weight: 600;
}

.toolbar-field.recherche {
    flex: 1;
    min-width: 220px;
}

.form-input-small, .form-select-small {
    padding: 0.5rem 0.7rem;
    background: rgba(25, 10, 50, 0.9);
    border: 1px solid #c49a3c;
    border-radius: 6px;
    color: #e8d5b5;
    font-size: 0.85rem;
    outline: none;
}

.form-select-small { cursor: pointer; }

.grille-objets {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 1rem;
}

.carte-objet {
    background: rgba(15, 5, 30, 0.9);
    border: 1px solid #c49a3c;
    border-radius: 10px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.carte-objet-entete {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
}

.carte-objet-nom {
    color: #e8d5b5;
    font-weight: 700;
    font-size: 0.95rem;
}

.objet-meta {
    color: #b8a9c9;
    font-size: 0.75rem;
}

.objet-description {
    color: #b8a9c9;
    font-size: 0.8rem;
    font-style: italic;
    flex: 1;
}

.objet-bonus {
    color: #e6b84d;
    font-weight: 600;
}

.badge {
    display: inline-block;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
    white-space: nowrap;
}

.badge-magique {
    background: rgba(167, 139, 250, 0.25);
    color: #c4b5fd;
    border: 1px solid #a78bfa;
}

.aucun-resultat {
    color: #b8a9c9;
    font-size: 0.9rem;
    padding: 1rem 0;
}
</style>
@endpush

@section('content')
<div class="sheet-container" style="max-width: 1100px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem;">
        <div class="text-gold" style="font-size: 1.3rem; font-weight: 700;">Ajouter un objet à l'inventaire de {{ $personnage->nom }}</div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('personnages.inventaire.ajouter-custom-page', $personnage) }}" class="btn btn-primary btn-small">+ Objet custom</a>
            <a href="{{ route('personnages.inventaire.index', $personnage) }}" class="btn btn-secondary btn-small">← Retour à l'inventaire</a>
        </div>
    </div>

    @if($errors->any())
        <div class="inventaire-section" style="border-color: #d9534f;">
            @foreach($errors->all() as $erreur)
                <div>{{ $erreur }}</div>
            @endforeach
        </div>
    @endif

    <div class="inventaire-section">
        <div class="toolbar">
            <div class="toolbar-field recherche">
                <label for="rechercheObjet">Recherche</label>
                <input type="search" id="rechercheObjet" class="form-input-small" placeholder="Nom de l'objet..." oninput="filtrerCatalogue()">
            </div>
            <div class="toolbar-field">
                <label for="filtreType">Type</label>
                <select id="filtreType" class="form-select-small" onchange="filtrerCatalogue()">
                    <option value="">Tous</option>
                    @foreach(\App\Models\Objet::TYPES as $type)
                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="toolbar-field">
                <label for="filtreMagique">Magie</label>
                <select id="filtreMagique" class="form-select-small" onchange="filtrerCatalogue()">
                    <option value="">Tous</option>
                    <option value="1">Magique</option>
                    <option value="0">Non magique</option>
                </select>
            </div>
        </div>

        <div class="grille-objets" id="grilleObjets">
            @foreach($catalogue as $objetCatalogue)
                <div class="carte-objet"
                     data-nom="{{ mb_strtolower($objetCatalogue->nom) }}"
                     data-type="{{ $objetCatalogue->type }}"
                     data-magique="{{ $objetCatalogue->magique ? 1 : 0 }}">
                    <div class="carte-objet-entete">
                        <div class="carte-objet-nom">{{ $objetCatalogue->nom }}</div>
                        @if($objetCatalogue->utilisateur_id)
                            <span class="badge" style="background: rgba(196, 154, 60, 0.2); color: #e6b84d; border: 1px solid #c49a3c;">Custom</span>
                        @endif
                    </div>

                    @include('partials.objet-stats', ['objet' => $objetCatalogue])

                    <form action="{{ route('personnages.inventaire.ajouter', $personnage) }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="objet_id" value="{{ $objetCatalogue->id }}">
                        <input type="hidden" name="quantite" value="1">
                        <button type="submit" class="btn btn-primary btn-small" style="width: 100%;">Ajouter</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="aucun-resultat" id="aucunResultat" style="display: none;">Aucun objet ne correspond à ces filtres.</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function filtrerCatalogue() {
    const recherche = document.getElementById('rechercheObjet').value.trim().toLowerCase();
    const type = document.getElementById('filtreType').value;
    const magique = document.getElementById('filtreMagique').value;

    const cartes = document.querySelectorAll('#grilleObjets .carte-objet');
    let visibles = 0;

    cartes.forEach(carte => {
        const correspondNom = !recherche || carte.dataset.nom.includes(recherche);
        const correspondType = !type || carte.dataset.type === type;
        const correspondMagique = magique === '' || carte.dataset.magique === magique;
        const visible = correspondNom && correspondType && correspondMagique;

        carte.style.display = visible ? 'flex' : 'none';
        if (visible) visibles++;
    });

    document.getElementById('aucunResultat').style.display = visibles === 0 ? 'block' : 'none';
}
</script>
@endpush
