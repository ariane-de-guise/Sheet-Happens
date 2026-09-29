@extends('layouts.app')

@section('titre', 'Traits de ' . $personnage->nom . ' - Sheet Happens')

@section('contenu')
    <div class="carte">
        <div class="traits-entete">
            <div>
                <div class="texte-or traits-titre">Traits de {{ $personnage->nom }}</div>
                <div class="texte-attenue traits-sous-titre">
                    {{ $personnage->classe->nom }} - {{ $personnage->race->nom }}
                    @if($personnage->sousRace)
                        ({{ $personnage->sousRace->nom }})
                    @endif
                </div>
            </div>
            <a href="{{ route('personnages.show', $personnage) }}" class="bouton bouton-secondaire bouton-petit">← Retour à la fiche</a>
        </div>

        <div class="traits-section">
            <div class="texte-or marge-bas-2 traits-section-titre">Traits de race</div>
            @if($raceTraits->isEmpty())
                <div class="texte-attenue traits-texte">Aucun trait n'est associé à cette race.</div>
            @else
                <div class="traits-liste">
                    @foreach($raceTraits as $trait)
                        <div>
                            <div class="traits-ligne">
                                <span class="texte-or traits-nom">{{ $trait->nom }}</span>
                                <span class="texte-attenue traits-niveau">Niveau {{ $trait->niveau }}</span>
                            </div>
                            <div class="texte-attenue traits-texte">{{ $trait->description ?: 'Aucune description disponible.' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="traits-section">
            <div class="texte-or marge-bas-2 traits-section-titre">Traits de sous-race</div>
            @if(!$personnage->sousRace)
                <div class="texte-attenue traits-texte">Ce personnage n'a pas de sous-race.</div>
            @elseif($sousRaceTraits->isEmpty())
                <div class="texte-attenue traits-texte">Aucun trait n'est associé à cette sous-race.</div>
            @else
                <div class="traits-liste">
                    @foreach($sousRaceTraits as $trait)
                        <div>
                            <div class="traits-ligne">
                                <span class="texte-or traits-nom">{{ $trait->nom }}</span>
                                <span class="texte-attenue traits-niveau">Niveau {{ $trait->niveau }}</span>
                            </div>
                            <div class="texte-attenue traits-texte">{{ $trait->description ?: 'Aucune description disponible.' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="traits-section">
            <div class="texte-or marge-bas-2 traits-section-titre">Capacités de classe</div>
            @if($capacites->isEmpty())
                <div class="texte-attenue traits-texte">Aucune capacité de classe acquise.</div>
            @else
                <div class="traits-liste">
                    @foreach($capacites as $capacite)
                        <div>
                            <div class="traits-ligne">
                                <span class="texte-or traits-nom">{{ $capacite->nom }}</span>
                                <span class="texte-attenue traits-niveau">{{ $capacite->sousClasse?->nom ?? $personnage->classe->nom }} · Niveau {{ $capacite->niveau }}</span>
                            </div>
                            <div class="texte-attenue traits-texte">{{ $capacite->description ?: 'Aucune description disponible.' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="traits-section-finale">
            <div class="texte-or marge-bas-2 traits-section-titre">Dons</div>
            @if($dons->isEmpty())
                <div class="texte-attenue traits-texte">Aucun don acquis.</div>
            @else
                <div class="traits-liste">
                    @foreach($dons as $don)
                        <div>
                            <div class="texte-or traits-nom-don">{{ $don->nom }}</div>
                            <div class="texte-attenue traits-texte">{{ $don->description ?: ($don->resume ?: 'Aucune description disponible.') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
