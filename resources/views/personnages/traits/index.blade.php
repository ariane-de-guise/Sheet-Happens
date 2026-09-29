@extends('layouts.app')

@section('title', 'Traits de ' . $personnage->nom . ' - Sheet Happens')

@section('content')
    <div class="carte">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <div class="texte-or" style="font-size: 1.3rem; font-weight: 700;">Traits de {{ $personnage->nom }}</div>
                <div class="texte-attenue" style="font-size: 0.85rem;">
                    {{ $personnage->classe->nom }} - {{ $personnage->race->nom }}
                    @if($personnage->sousRace)
                        ({{ $personnage->sousRace->nom }})
                    @endif
                </div>
            </div>
            <a href="{{ route('personnages.show', $personnage) }}" class="bouton bouton-secondaire bouton-petit">← Retour à la fiche</a>
        </div>

        <div style="background: var(--panneau); border: 1px solid var(--or-bordure); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
            <div class="texte-or marge-bas-2" style="font-size: 1.15rem; font-weight: 600;">Traits de race</div>
            @if($raceTraits->isEmpty())
                <div class="texte-attenue" style="font-size: 0.9rem;">Aucun trait n'est associé à cette race.</div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($raceTraits as $trait)
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.25rem;">
                                <span class="texte-or" style="font-weight: 600;">{{ $trait->nom }}</span>
                                <span class="texte-attenue" style="font-size: 0.8rem; white-space: nowrap;">Niveau {{ $trait->niveau }}</span>
                            </div>
                            <div class="texte-attenue" style="font-size: 0.9rem;">{{ $trait->description ?: 'Aucune description disponible.' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div style="background: var(--panneau); border: 1px solid var(--or-bordure); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
            <div class="texte-or marge-bas-2" style="font-size: 1.15rem; font-weight: 600;">Traits de sous-race</div>
            @if(!$personnage->sousRace)
                <div class="texte-attenue" style="font-size: 0.9rem;">Ce personnage n'a pas de sous-race.</div>
            @elseif($sousRaceTraits->isEmpty())
                <div class="texte-attenue" style="font-size: 0.9rem;">Aucun trait n'est associé à cette sous-race.</div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($sousRaceTraits as $trait)
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.25rem;">
                                <span class="texte-or" style="font-weight: 600;">{{ $trait->nom }}</span>
                                <span class="texte-attenue" style="font-size: 0.8rem; white-space: nowrap;">Niveau {{ $trait->niveau }}</span>
                            </div>
                            <div class="texte-attenue" style="font-size: 0.9rem;">{{ $trait->description ?: 'Aucune description disponible.' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div style="background: var(--panneau); border: 1px solid var(--or-bordure); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
            <div class="texte-or marge-bas-2" style="font-size: 1.15rem; font-weight: 600;">Capacités de classe</div>
            @if($capacites->isEmpty())
                <div class="texte-attenue" style="font-size: 0.9rem;">Aucune capacité de classe acquise.</div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($capacites as $capacite)
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.25rem;">
                                <span class="texte-or" style="font-weight: 600;">{{ $capacite->nom }}</span>
                                <span class="texte-attenue" style="font-size: 0.8rem; white-space: nowrap;">{{ $capacite->sousClasse?->nom ?? $personnage->classe->nom }} · Niveau {{ $capacite->niveau }}</span>
                            </div>
                            <div class="texte-attenue" style="font-size: 0.9rem;">{{ $capacite->description ?: 'Aucune description disponible.' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div style="background: var(--panneau); border: 1px solid var(--or-bordure); border-radius: 12px; padding: 1.25rem;">
            <div class="texte-or marge-bas-2" style="font-size: 1.15rem; font-weight: 600;">Dons</div>
            @if($dons->isEmpty())
                <div class="texte-attenue" style="font-size: 0.9rem;">Aucun don acquis.</div>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($dons as $don)
                        <div>
                            <div class="texte-or" style="font-weight: 600; margin-bottom: 0.25rem;">{{ $don->nom }}</div>
                            <div class="texte-attenue" style="font-size: 0.9rem;">{{ $don->description ?: ($don->resume ?: 'Aucune description disponible.') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
