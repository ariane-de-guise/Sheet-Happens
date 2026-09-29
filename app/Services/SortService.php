<?php

namespace App\Services;

use App\Models\Classe;
use App\Models\ClasseSort;
use App\Models\LimiteMagie;
use App\Models\LimiteMagieClasse;
use App\Models\LimiteMagieSousClasse;
use App\Models\Personnage;
use App\Models\SousClasseSort;
use Illuminate\Support\Facades\DB;

/**
 * Règles de magie d'un personnage : caractéristique d'incantation, limites
 * de sorts (tours de magie, sorts préparés, emplacements) et liste des
 * sorts auxquels il a accès.
 */
class SortService
{
    /** Caractéristique d'incantation des classes lanceuses de sorts (5e SRD). */
    public const CARACTERISTIQUE_INCANTATION_PAR_CLASSE = [
        'Clerc' => 'sagesse',
        'Magicien' => 'intelligence',
        'Druide' => 'sagesse',
        'Barde' => 'charisme',
        'Paladin' => 'charisme',
        'Rôdeur' => 'sagesse',
        'Ensorceleur' => 'charisme',
        'Occultiste' => 'charisme',
    ];

    /**
     * Sous-classes qui gagnent la magie sans liste de sorts propre : elles
     * puisent dans la liste du Magicien.
     */
    public const SOUS_CLASSES_LISTE_MAGICIEN = ['Filou Arcanique', 'Chevalier Eldritch'];

    /**
     * Limites de magie au niveau du personnage : celles de sa classe si elle
     * est magique, sinon celles de sa sous-classe.
     *
     * @return array{limiteTour: ?int, limiteSort: ?int, emplacements: ?array, abiliteMagie: ?string, abiliteSource: ?string}
     */
    public function limites(Personnage $personnage): array
    {
        $niveau = (int) $personnage->niveau;
        $ligne = null;
        $source = null;

        if (LimiteMagieClasse::query()->where('id_Classe', $personnage->classe_id)->exists()) {
            $ligne = LimiteMagieClasse::query()
                ->where('id_Classe', $personnage->classe_id)
                ->where('Niveau', '<=', $niveau)
                ->orderByDesc('Niveau')
                ->first();
            $source = 'classe';
        } elseif ($personnage->sous_classe_id) {
            $ligne = LimiteMagieSousClasse::query()
                ->where('id_SousClasse', $personnage->sous_classe_id)
                ->where('Niveau', '<=', $niveau)
                ->orderByDesc('Niveau')
                ->first();
            $source = 'sous_classe';
        }

        $limite = $ligne ? LimiteMagie::find($ligne->id_LimiteMagie) : null;

        if (! $limite) {
            return ['limiteTour' => null, 'limiteSort' => null, 'emplacements' => null, 'abiliteMagie' => null, 'abiliteSource' => null];
        }

        return [
            'limiteTour' => (int) $limite->TourMagie,
            'limiteSort' => $limite->SortConnue !== null ? (int) $limite->SortConnue : null,
            'emplacements' => [
                'Niveau1' => $limite->Niveau1,
                'Niveau2' => $limite->Niveau2,
                'Niveau3' => $limite->Niveau3,
            ],
            // AbiliteMagie est stockée sur la table de liaison, pas sur limite_magies.
            'abiliteMagie' => $ligne->AbiliteMagie,
            'abiliteSource' => $source,
        ];
    }

    /** Le personnage dispose-t-il d'au moins un tour de magie ou emplacement de sort ? */
    public function aDesSorts(Personnage $personnage): bool
    {
        $limites = $this->limites($personnage);

        return (int) $limites['limiteTour'] > 0 || array_sum($limites['emplacements'] ?? []) > 0;
    }

    /**
     * Caractéristique d'incantation (clé de la table `abilites`) : celle des
     * limites de magie en priorité (elle peut venir de la sous-classe),
     * sinon celle de la classe.
     */
    public function caracteristiqueIncantation(Personnage $personnage, array $limites): ?string
    {
        $nom = $limites['abiliteMagie'] ?? null;
        $nom = $nom ?: (self::CARACTERISTIQUE_INCANTATION_PAR_CLASSE[$personnage->classe?->nom] ?? null);

        return $nom ? mb_strtolower($nom) : null;
    }

    /** Modificateur de la caractéristique d'incantation (0 si aucune). */
    public function modificateurIncantation(Personnage $personnage, array $limites): int
    {
        $caracteristique = $this->caracteristiqueIncantation($personnage, $limites);
        $score = $caracteristique ? ($personnage->abilites?->{$caracteristique} ?? null) : null;

        return $score !== null ? (int) floor(($score - 10) / 2) : 0;
    }

    /**
     * Nombre de sorts de niveau 1 à 3 préparables : la limite enregistrée,
     * ou à défaut niveau + modificateur d'incantation (règle 5e).
     */
    public function limiteSortsPrepares(Personnage $personnage, array $limites): int
    {
        return $limites['limiteSort']
            ?? (int) $personnage->niveau + $this->modificateurIncantation($personnage, $limites);
    }

    /**
     * Message d'erreur si préparer un sort de ce niveau dépasserait la
     * limite du personnage, sinon null. À appeler dans une transaction :
     * les lignes comptées sont verrouillées pour éviter que deux requêtes
     * simultanées ne dépassent la limite ensemble.
     */
    public function depassementLimite(Personnage $personnage, int $niveauSort, array $limites): ?string
    {
        DB::table('personnages')->where('id', $personnage->id)->lockForUpdate()->first();

        if ($niveauSort === 0) {
            $limite = $limites['limiteTour'];
            $message = 'Limite de sorts cantrips préparés atteinte.';
        } elseif ($niveauSort <= 3) {
            $limite = $this->limiteSortsPrepares($personnage, $limites);
            $message = 'Limite de sorts préparés (niveaux 1-3) atteinte.';
        } else {
            return null;
        }

        if ($limite === null) {
            return null;
        }

        $prepares = DB::table('personnage_sorts')
            ->join('sorts', 'personnage_sorts.id_Sort', '=', 'sorts.id')
            ->where('personnage_sorts.id_Personnage', $personnage->id)
            ->where('personnage_sorts.Prepare', 1)
            ->when(
                $niveauSort === 0,
                fn ($requeteSql) => $requeteSql->where('sorts.Niveau', 0),
                fn ($requeteSql) => $requeteSql->whereBetween('sorts.Niveau', [1, 3]),
            )
            ->lockForUpdate()
            ->count();

        return $prepares >= $limite ? $message : null;
    }

    /**
     * Identifiants des sorts accessibles au personnage, séparés selon leur
     * origine (classe ou sous-classe).
     *
     * @return array{classe: array<int, int>, sous_classe: array<int, int>}
     */
    public function sortsAccessibles(Personnage $personnage): array
    {
        $classeIds = [$personnage->classe_id];

        $personnage->loadMissing('sousClasse');
        if (in_array($personnage->sousClasse?->nom, self::SOUS_CLASSES_LISTE_MAGICIEN, true)) {
            $magicienId = Classe::where('nom', 'Magicien')->value('id');
            if ($magicienId) {
                $classeIds[] = $magicienId;
            }
        }

        $sousClasseIds = $personnage->sous_classe_id
            ? SousClasseSort::query()->where('id_SousClasse', $personnage->sous_classe_id)->pluck('id_Sort')->unique()->values()->all()
            : [];

        return [
            'classe' => ClasseSort::query()->whereIn('id_Classe', $classeIds)->pluck('id_Sort')->unique()->values()->all(),
            'sous_classe' => $sousClasseIds,
        ];
    }
}
