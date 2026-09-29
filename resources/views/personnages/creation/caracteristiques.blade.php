@extends('layouts.app')

@section('title', 'Créer un personnage - Caractéristiques')

@section('content')
    <div class="carte">
        <div class="texte-centre marge-bas-3">
            <span class="texte-or">Créer un personnage</span>
        </div>

        <div class="barre-progression">
            <div class="ligne-progression">
                <div class="remplissage-progression" style="width: 60%;"></div>
            </div>
            <div class="etape-progression terminee">1</div>
            <div class="etape-progression terminee">2</div>
            <div class="etape-progression terminee">3</div>
            <div class="etape-progression active">4</div>
            <div class="etape-progression">5</div>
            <div class="etape-progression">6</div>
        </div>

        <h2 class="titre-section texte-centre">Répartissez vos caractéristiques</h2>

        @if(session('erreur'))
            <div class="alerte alerte-erreur">{{ session('erreur') }}</div>
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
            $libelles = [
                'force' => 'Force',
                'dexterite' => 'Dextérité',
                'constitution' => 'Constitution',
                'intelligence' => 'Intelligence',
                'sagesse' => 'Sagesse',
                'charisme' => 'Charisme',
            ];
            $formaterBonusFixe = function (array $listeBonus) use ($libelles): array {
                $formate = [];
                foreach ($listeBonus as $caracteristique => $valeur) {
                    if (isset($libelles[$caracteristique]) && is_numeric($valeur) && (int) $valeur !== 0) {
                        $formate[] = $libelles[$caracteristique] . ' +' . $valeur;
                    }
                }
                return $formate;
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
                $fixes = $formaterBonusFixe($bonusAptitudes[$cle] ?? []);
                if ($fixes) {
                    $bonusFixeMentions[] = implode(', ', $fixes) . ' (' . $nomSource . ')';
                }
            }

            // Petit badge vert par caractéristique (+X / 0 / -X) : ne compte
            // que les bonus déjà fixés (race, sous-race, classe). Les bonus
            // "au choix" pas encore assignés ne sont pas comptés ici — une
            // fois choisis, le JS de _caracteristiques_bonus.blade.php met
            // ces badges à jour en direct (voir bonus_choix_au_choix.js).
            $bonusParAptitude = array_fill_keys(array_keys($libelles), 0);
            foreach ([$bonusAptitudes['race'] ?? [], $bonusAptitudes['sous_race'] ?? [], $bonusAptitudes['classe'] ?? []] as $bonus) {
                foreach ($bonus as $caracteristique => $valeur) {
                    if (isset($bonusParAptitude[$caracteristique]) && is_numeric($valeur)) {
                        $bonusParAptitude[$caracteristique] += (int) $valeur;
                    }
                }
            }
            // Les bonus "au choix" déjà sélectionnés (retour en arrière,
            // erreur de validation) comptent aussi dans le total affiché.
            foreach ($auChoixDisponibles as $source => $config) {
                foreach ($bonusChoix[$source] ?? [] as $caracteristique) {
                    if (isset($bonusParAptitude[$caracteristique])) {
                        $bonusParAptitude[$caracteristique] += (int) $config['bonus'];
                    }
                }
            }
        @endphp

        @if($methode === 'points')
            @include('personnages.creation._caracteristiques_points', [
                'abilites' => $abilites,
                'labels' => $libelles,
                'bonusFixeMentions' => $bonusFixeMentions,
                'auChoixDisponibles' => $auChoixDisponibles,
                'bonusChoix' => $bonusChoix,
                'bonusParAptitude' => $bonusParAptitude,
            ])
        @elseif($methode === 'standard')
            @include('personnages.creation._caracteristiques_standard', [
                'abilites' => $abilites,
                'labels' => $libelles,
                'bonusFixeMentions' => $bonusFixeMentions,
                'auChoixDisponibles' => $auChoixDisponibles,
                'bonusChoix' => $bonusChoix,
                'bonusParAptitude' => $bonusParAptitude,
            ])
        @else
            @include('personnages.creation._caracteristiques_des', [
                'abilites' => $abilites,
                'labels' => $libelles,
                'bonusFixeMentions' => $bonusFixeMentions,
                'auChoixDisponibles' => $auChoixDisponibles,
                'bonusChoix' => $bonusChoix,
                'bonusParAptitude' => $bonusParAptitude,
            ])
        @endif
    </div>
@endsection
