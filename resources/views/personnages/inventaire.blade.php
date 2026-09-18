@extends('layouts.app')

@section('title', 'Inventaire de ' . $personnage->nom)

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <div class="text-gold" style="font-size: 1.3rem; font-weight: 700;">Inventaire de {{ $personnage->nom }}</div>
                <div class="text-muted" style="font-size: 0.85rem;">{{ $personnage->classe->nom }} - {{ $personnage->race->nom }}</div>
            </div>
            <a href="{{ route('personnages.show', $personnage) }}" class="btn btn-secondary btn-small">← Retour à la fiche</a>
        </div>

        <div style="background: var(--panneau); border: 1px solid var(--or-bordure); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
            <div class="text-gold mb-2" style="font-weight: 600;">Objets</div>
            @if(count($equipementClasse['objets']) > 0)
                <ul style="list-style: disc; padding-left: 1.25rem; color: var(--texte-secondaire); font-size: 0.9rem;">
                    @foreach($equipementClasse['objets'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <div class="text-muted" style="font-size: 0.9rem;">Aucun objet enregistré.</div>
            @endif
        </div>

        <div style="background: var(--panneau); border: 1px solid var(--or-bordure); border-radius: 12px; padding: 1.25rem;">
            <div class="text-gold mb-2" style="font-weight: 600;">Armure (équipement)</div>
            @if(count($equipementClasse['armure']) > 0)
                <ul style="list-style: disc; padding-left: 1.25rem; color: var(--texte-secondaire); font-size: 0.9rem;">
                    @foreach($equipementClasse['armure'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <div class="text-muted" style="font-size: 0.9rem;">Aucune armure enregistrée.</div>
            @endif
        </div>
    </div>
@endsection
