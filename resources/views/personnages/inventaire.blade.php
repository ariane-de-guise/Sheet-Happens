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

        <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
            <div class="text-gold mb-2" style="font-weight: 600;">Équipement porté</div>
            @if($personnage->equipement && count($personnage->equipement) > 0)
                <ul style="list-style: disc; padding-left: 1.25rem; color: #b8a9c9; font-size: 0.9rem;">
                    @foreach($personnage->equipement as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <div class="text-muted" style="font-size: 0.9rem;">Aucun équipement enregistré.</div>
            @endif
        </div>

        <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
            <div class="text-gold mb-2" style="font-weight: 600;">Outil</div>
            <div class="text-muted" style="font-size: 0.9rem;">{{ $personnage->outil ?? 'Aucun' }}</div>
        </div>

        <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1.25rem;">
            <div class="text-gold mb-2" style="font-weight: 600;">Langues</div>
            @if($personnage->langues && count($personnage->langues) > 0)
                <div class="text-muted" style="font-size: 0.9rem;">{{ implode(', ', $personnage->langues) }}</div>
            @else
                <div class="text-muted" style="font-size: 0.9rem;">Aucune langue enregistrée.</div>
            @endif
        </div>
    </div>
@endsection
