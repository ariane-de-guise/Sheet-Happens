@extends('layouts.app')

@section('title', 'Traits de ' . $personnage->nom . ' - Sheet Happens')

@section('content')
    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 1.5rem;">
        <aside class="card" style="height: fit-content;">
            <div class="text-gold mb-3" style="font-size: 1.1rem;">Sheet Happens</div>
            <div style="border-top: 1px solid var(--or-bordure); padding-top: 1rem;">
                <a href="{{ route('personnages.show', $personnage) }}" class="text-muted" style="text-decoration: none; font-size: 0.85rem;">
                    ← Retour à la fiche du personnage
                </a>
            </div>
        </aside>

        <main class="card">
            <div style="margin-bottom: 2rem;">
                <h1 class="title-main" style="margin-bottom: 0.5rem;">Traits</h1>
                <p class="text-muted" style="margin: 0;">
                    {{ $personnage->nom }} · {{ $personnage->race->nom }}
                    @if($personnage->sousRace)
                        ({{ $personnage->sousRace->nom }})
                    @endif
                </p>
            </div>

            <section class="mb-4">
                <h2 class="title-section">Traits de race</h2>

                @if($raceTraits->isEmpty())
                    <div class="help-box">
                        <div class="content">Aucun trait n'est associé à cette race.</div>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        @foreach($raceTraits as $trait)
                            <article class="help-box" style="margin-top: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.5rem;">
                                    <h3 class="title" style="margin: 0;">{{ $trait->nom }}</h3>
                                    <span class="text-muted" style="font-size: 0.8rem; white-space: nowrap;">
                                        Niveau {{ $trait->niveau }}
                                    </span>
                                </div>
                                <div class="content">
                                    {{ $trait->description ?: 'Aucune description disponible.' }}
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <h2 class="title-section">Traits de sous-race</h2>

                @if(!$personnage->sousRace)
                    <div class="help-box">
                        <div class="content">Ce personnage n'a pas de sous-race.</div>
                    </div>
                @elseif($sousRaceTraits->isEmpty())
                    <div class="help-box">
                        <div class="content">Aucun trait n'est associé à cette sous-race.</div>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        @foreach($sousRaceTraits as $trait)
                            <article class="help-box" style="margin-top: 0;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.5rem;">
                                    <h3 class="title" style="margin: 0;">{{ $trait->nom }}</h3>
                                    <span class="text-muted" style="font-size: 0.8rem; white-space: nowrap;">
                                        Niveau {{ $trait->niveau }}
                                    </span>
                                </div>
                                <div class="content">
                                    {{ $trait->description ?: 'Aucune description disponible.' }}
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>
    </div>
@endsection
