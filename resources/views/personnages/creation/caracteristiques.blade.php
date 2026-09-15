@extends('layouts.app')

@section('title', 'Créer un personnage - Caractéristiques')

@section('content')
    <div class="card">
        <div class="text-center mb-3">
            <span class="text-gold">Créer un personnage</span>
        </div>

        <div class="progress-bar">
            <div class="progress-line">
                <div class="progress-fill" style="width: 60%;"></div>
            </div>
            <div class="progress-step completed">1</div>
            <div class="progress-step completed">2</div>
            <div class="progress-step completed">3</div>
            <div class="progress-step active">4</div>
            <div class="progress-step">5</div>
            <div class="progress-step">6</div>
        </div>

        <h2 class="title-section text-center">Répartissez vos caractéristiques</h2>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @php
            $labels = [
                'force' => 'Force',
                'dexterite' => 'Dextérité',
                'constitution' => 'Constitution',
                'intelligence' => 'Intelligence',
                'sagesse' => 'Sagesse',
                'charisme' => 'Charisme',
            ];
            $formatBonus = function (array $bonuses) use ($labels): array {
                $formatted = [];
                foreach ($bonuses as $key => $value) {
                    if ($key === 'au_choix' && is_array($value)) {
                        $formatted[] = $value['nombre'] . ' bonus de +' . $value['bonus'] . ' au choix';
                    } elseif (isset($labels[$key]) && is_numeric($value) && (int) $value !== 0) {
                        $formatted[] = $labels[$key] . ' +' . $value;
                    }
                }
                return $formatted;
            };
            $raceBonusLabels = $formatBonus($bonusAptitudes['race'] ?? []);
            $sousRaceBonusLabels = $formatBonus($bonusAptitudes['sous_race'] ?? []);
            $bonusParAptitude = array_fill_keys(array_keys($labels), 0);
            foreach ([$bonusAptitudes['race'] ?? [], $bonusAptitudes['sous_race'] ?? []] as $bonus) {
                foreach ($bonus as $key => $value) {
                    if (isset($bonusParAptitude[$key]) && is_numeric($value)) {
                        $bonusParAptitude[$key] += (int) $value;
                    }
                }
            }
        @endphp

        <div class="help-box mb-4">
            <div class="title">Bonus de race</div>
            <div class="content">
                {{ $raceBonusLabels ? implode(', ', $raceBonusLabels) : 'Aucun bonus' }}
                @if($sousRaceBonusLabels)
                    <br><strong>{{ $sousRace->nom }} :</strong> {{ implode(', ', $sousRaceBonusLabels) }}
                @endif
            </div>
        </div>

        @if($methode === 'points')
            @include('personnages.creation._caracteristiques_points', ['abilites' => $abilites])
        @elseif($methode === 'standard')
            @include('personnages.creation._caracteristiques_standard', ['abilites' => $abilites])
        @else
            @include('personnages.creation._caracteristiques_dice', ['abilites' => $abilites])
        @endif
    </div>
@endsection
