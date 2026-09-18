@extends('layouts.app')

@section('title', 'Traits de ' . $personnage->nom . ' - Sheet Happens')

@section('content')
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <div class="text-gold" style="font-size: 1.3rem; font-weight: 700;">Traits de {{ $personnage->nom }}</div>
                <div class="text-muted" style="font-size: 0.85rem;">
                    {{ $personnage->classe->nom }} - {{ $personnage->race->nom }}
                    @if($personnage->sousRace)
                        ({{ $personnage->sousRace->nom }})
                    @endif
                </div>
            </div>
            <a href="{{ route('personnages.show', $personnage) }}" class="btn btn-secondary btn-small">← Retour à la fiche</a>
        </div>

        <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
            <div class="text-gold mb-2" style="font-size: 1.15rem; font-weight: 600;">Traits de race</div>
            @if($raceTraits->isEmpty())
                <div class="text-muted" style="font-size: 0.9rem;">Aucun trait n'est associé à cette race.</div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($raceTraits as $trait)
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.25rem;">
                                <span class="text-gold" style="font-weight: 600;">{{ $trait->nom }}</span>
                                <span class="text-muted" style="font-size: 0.8rem; white-space: nowrap;">Niveau {{ $trait->niveau }}</span>
                            </div>
                            <div class="text-muted" style="font-size: 0.9rem;">{{ $trait->description ?: 'Aucune description disponible.' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div style="background: rgba(25, 10, 50, 0.9); border: 1px solid #c49a3c; border-radius: 12px; padding: 1.25rem;">
            <div class="text-gold mb-2" style="font-size: 1.15rem; font-weight: 600;">Traits de sous-race</div>
            @if(!$personnage->sousRace)
                <div class="text-muted" style="font-size: 0.9rem;">Ce personnage n'a pas de sous-race.</div>
            @elseif($sousRaceTraits->isEmpty())
                <div class="text-muted" style="font-size: 0.9rem;">Aucun trait n'est associé à cette sous-race.</div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($sousRaceTraits as $trait)
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.25rem;">
                                <span class="text-gold" style="font-weight: 600;">{{ $trait->nom }}</span>
                                <span class="text-muted" style="font-size: 0.8rem; white-space: nowrap;">Niveau {{ $trait->niveau }}</span>
                            </div>
                            <div class="text-muted" style="font-size: 0.9rem;">{{ $trait->description ?: 'Aucune description disponible.' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
