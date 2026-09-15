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
            // La maquette d'origine ne prévoit aucune mention de bonus sur
            // cet écran : plutôt que les 2 gros encadrés "Bonus de race" /
            // "Bonus de classe" affichés séparément au-dessus de la carte
            // (ce qui prenait toute la largeur disponible), on ne garde
            // qu'une mention compacte, placée en haut de la boîte de choix
            // des caractéristiques elle-même (voir
            // _caracteristiques_bonus.blade.php, inclus dans chacune des 3
            // méthodes ci-dessous).
            $labels = [
                'force' => 'Force',
                'dexterite' => 'Dextérité',
                'constitution' => 'Constitution',
                'intelligence' => 'Intelligence',
                'sagesse' => 'Sagesse',
                'charisme' => 'Charisme',
            ];
            $formatBonusFixe = function (array $bonuses) use ($labels): array {
                $formatted = [];
                foreach ($bonuses as $key => $value) {
                    if (isset($labels[$key]) && is_numeric($value) && (int) $value !== 0) {
                        $formatted[] = $labels[$key] . ' +' . $value;
                    }
                }
                return $formatted;
            };
            $sourcesBonus = [
                'race' => $race->nom ?? null,
                'sous_race' => $sousRace->nom ?? null,
                'classe' => $classe->nom ?? null,
            ];
            $bonusFixeMentions = [];
            foreach ($sourcesBonus as $cle => $nomSource) {
                if (! $nomSource) {
                    continue;
                }
                $fixes = $formatBonusFixe($bonusAptitudes[$cle] ?? []);
                if ($fixes) {
                    $bonusFixeMentions[] = implode(', ', $fixes) . ' (' . $nomSource . ')';
                }
            }

            // Petit badge vert par caractéristique (+X / 0 / -X) : ne compte
            // que les bonus déjà fixés (race, sous-race, classe). Les bonus
            // "au choix" pas encore assignés ne sont pas comptés ici — une
            // fois choisis, le JS de _caracteristiques_bonus.blade.php met
            // ces badges à jour en direct (voir bonus_choix_au_choix.js).
            $bonusParAptitude = array_fill_keys(array_keys($labels), 0);
            foreach ([$bonusAptitudes['race'] ?? [], $bonusAptitudes['sous_race'] ?? [], $bonusAptitudes['classe'] ?? []] as $bonus) {
                foreach ($bonus as $key => $value) {
                    if (isset($bonusParAptitude[$key]) && is_numeric($value)) {
                        $bonusParAptitude[$key] += (int) $value;
                    }
                }
            }
            // Les bonus "au choix" déjà sélectionnés (retour en arrière,
            // erreur de validation) comptent aussi dans le total affiché.
            foreach ($auChoixDisponibles as $source => $config) {
                foreach ($bonusChoix[$source] ?? [] as $key) {
                    if (isset($bonusParAptitude[$key])) {
                        $bonusParAptitude[$key] += (int) $config['bonus'];
                    }
                }
            }
        @endphp

        @if($methode === 'points')
            @include('personnages.creation._caracteristiques_points', [
                'abilites' => $abilites,
                'labels' => $labels,
                'bonusFixeMentions' => $bonusFixeMentions,
                'auChoixDisponibles' => $auChoixDisponibles,
                'bonusChoix' => $bonusChoix,
                'bonusParAptitude' => $bonusParAptitude,
            ])
        @elseif($methode === 'standard')
            @include('personnages.creation._caracteristiques_standard', [
                'abilites' => $abilites,
                'labels' => $labels,
                'bonusFixeMentions' => $bonusFixeMentions,
                'auChoixDisponibles' => $auChoixDisponibles,
                'bonusChoix' => $bonusChoix,
                'bonusParAptitude' => $bonusParAptitude,
            ])
        @else
            @include('personnages.creation._caracteristiques_dice', [
                'abilites' => $abilites,
                'labels' => $labels,
                'bonusFixeMentions' => $bonusFixeMentions,
                'auChoixDisponibles' => $auChoixDisponibles,
                'bonusChoix' => $bonusChoix,
                'bonusParAptitude' => $bonusParAptitude,
            ])
        @endif
    </div>
@endsection
