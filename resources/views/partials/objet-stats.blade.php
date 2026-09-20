{{-- Bloc de stats complet d'un objet (type, dégâts, CA, magie, bonus,
     description) : partagé entre la fiche d'inventaire et le catalogue
     d'ajout pour ne pas dupliquer la logique d'affichage. Attend une
     variable $objet (App\Models\Objet) et suppose que les classes
     .objet-meta / .objet-description / .badge / .badge-magique sont
     définies par la page qui l'inclut. --}}
@php
    $libellesBonus = [
        'force' => 'Force', 'dexterite' => 'Dextérité', 'constitution' => 'Constitution',
        'intelligence' => 'Intelligence', 'sagesse' => 'Sagesse', 'charisme' => 'Charisme',
        'ca' => 'CA', 'bonus_attaque' => 'au toucher', 'bonus_degats' => 'aux dégâts',
        'bonus_jets_sauvegarde' => 'à tous les jets de sauvegarde', 'bonus_competences' => 'à toutes les compétences',
    ];
    $texteBonus = fn ($bonus) => collect($bonus ?? [])
        ->map(fn ($v, $k) => ($v >= 0 ? '+' : '').$v.' '.($libellesBonus[$k] ?? $k))
        ->implode(', ');
    $bonusTexte = $texteBonus($objet->bonus ?? []);
    $bonusUtilisation = $texteBonus($objet->effet_utilisation['bonus'] ?? []);
@endphp

<div class="objet-meta">
    {{ ucfirst($objet->type) }}@if($objet->categorie) · {{ str_replace('_', ' ', $objet->categorie) }}@endif
    @if($objet->magique)
        · <span class="badge badge-magique">{{ $objet->rarete ? ucfirst(str_replace('_', ' ', $objet->rarete)) : 'Magique' }}</span>
    @endif
</div>

@if($objet->degats)
    <div class="objet-meta">Dégâts : {{ $objet->degats }} {{ $objet->type_degats }} ({{ $objet->caracteristique_degats === 'dexterite' ? 'Dextérité' : 'Force' }})</div>
@endif

@if($objet->type === 'armure' && $objet->ca_base)
    <div class="objet-meta">
        CA de base : {{ $objet->ca_base }}
        @if($objet->categorie === 'legere') + mod. Dextérité
        @elseif($objet->categorie === 'intermediaire') + mod. Dextérité (max +2)
        @endif
    </div>
@endif

@if($bonusTexte)
    <div class="objet-meta objet-bonus">
        Bonus{{ $objet->bonus_toujours_actif ? ' (toujours actif)' : (in_array($objet->type, ['arme', 'armure', 'bouclier']) ? ' (si équipé)' : '') }} : {{ $bonusTexte }}
    </div>
@endif

@if($objet->est_consommable)
    <div class="objet-meta">
        Consommable
        @if(! empty($objet->effet_utilisation['pv']))
            · Soin instantané : {{ $objet->effet_utilisation['pv'] >= 0 ? '+' : '' }}{{ $objet->effet_utilisation['pv'] }} PV
        @endif
    </div>
    @if($bonusUtilisation)
        <div class="objet-meta objet-bonus">
            Bonus temporaire à l'utilisation ({{ $objet->effet_utilisation['duree_valeur'] ?? '?' }} {{ $objet->effet_utilisation['duree_unite'] ?? 'tours' }}) : {{ $bonusUtilisation }}
        </div>
    @endif
@endif

@if($objet->description)
    <div class="objet-description">{{ $objet->description }}</div>
@endif
