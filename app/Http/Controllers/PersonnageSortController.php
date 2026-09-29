<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\PersonnageSort;
use App\Models\Sort;
use App\Services\SortService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PersonnageSortController extends Controller
{
    /**
     * Valeurs de `personnage_sorts.Prepare` : 0 = non préparé, 1 = préparé,
     * 2 = accordé par la sous-classe (ou, pour le Magicien, inscrit au grimoire).
     */
    private const NON_PREPARE = 0;

    private const PREPARE = 1;

    private const SPECIAL = 2;

    public function __construct(private readonly SortService $sorts) {}

    public function index(Personnage $personnage): View
    {
        $this->verifierProprietaire($personnage);

        $appris = PersonnageSort::query()
            ->where('id_Personnage', $personnage->id)
            ->get()
            ->keyBy('id_Sort');

        $accessibles = $this->sorts->sortsAccessibles($personnage);
        $ids = array_values(array_unique(array_merge($accessibles['classe'], $accessibles['sous_classe'])));
        $modeles = Sort::query()->whereIn('id', $ids)->get()->keyBy('id');

        $disponibles = collect();
        foreach ($ids as $id) {
            $sort = $modeles->get($id);
            if (! $sort) {
                continue;
            }

            $lien = $appris->get($id);
            $deSousClasse = in_array($id, $accessibles['sous_classe'], true);

            $disponibles->push((object) [
                'sort' => $sort,
                'origine' => $lien ? 'personnage' : ($deSousClasse ? 'sous_classe' : 'classe'),
                // Les sorts de sous-classe sont toujours préparés (2) tant que
                // le personnage ne les a pas encore enregistrés.
                'prepare' => $lien ? (int) $lien->Prepare : ($deSousClasse ? self::SPECIAL : self::NON_PREPARE),
                'niveau' => $sort->Niveau,
                'personnage_sort_id' => $lien?->id,
            ]);
        }

        $limites = $this->sorts->limites($personnage);
        $caracteristique = $this->sorts->caracteristiqueIncantation($personnage, $limites);

        return view('personnages.sorts.index', [
            'personnage' => $personnage,
            'disponibles' => $disponibles,
            'niveaux' => range(1, max(3, (int) $disponibles->max('niveau'))),
            'niveau0' => $disponibles->filter(fn ($s) => (int) $s->niveau === 0)->values(),
            'limiteTour' => $limites['limiteTour'],
            'limiteSort' => $limites['limiteSort'],
            'limiteSortsPrepares' => $this->sorts->limiteSortsPrepares($personnage, $limites),
            'emplacements' => $limites['emplacements'],
            'abiliteMagie' => $limites['abiliteMagie'],
            'abiliteSource' => $limites['abiliteSource'],
            'caracteristique' => $caracteristique,
            'scoreCaracteristique' => $caracteristique ? $personnage->abilites?->{$caracteristique} : null,
            'modificateurIncantation' => $this->sorts->modificateurIncantation($personnage, $limites),
        ]);
    }

    public function modifierPreparation(Request $requete, Personnage $personnage, PersonnageSort $personnageSort): JsonResponse
    {
        $this->verifierProprietaire($personnage);

        if ((int) $personnageSort->id_Personnage !== (int) $personnage->id) {
            return response()->json(['message' => 'Mauvaise ressource'], 403);
        }

        $estMagicien = $this->estMagicien($personnage);
        $valeur = $this->normaliserPreparation($requete->input('prepare', self::NON_PREPARE), $estMagicien);

        // Hors Magicien, 2 = sort accordé par la sous-classe : non modifiable.
        if (! $estMagicien && (int) $personnageSort->Prepare === self::SPECIAL) {
            return response()->json(['message' => 'Ce sort est préparé par la classe et ne peut pas être modifié.'], 403);
        }

        $niveauSort = (int) $personnageSort->sort->Niveau;
        if ($estMagicien && $valeur === self::PREPARE && $niveauSort > 0 && (int) $personnageSort->Prepare === self::NON_PREPARE) {
            return response()->json(['message' => 'Ce sort doit être inscrit dans le grimoire pour être préparé.'], 400);
        }

        return DB::transaction(function () use ($personnage, $personnageSort, $valeur, $niveauSort) {
            if ($valeur === self::PREPARE) {
                $erreur = $this->sorts->depassementLimite($personnage, $niveauSort, $this->sorts->limites($personnage));
                if ($erreur) {
                    return response()->json(['message' => $erreur], 400);
                }
            }

            $personnageSort->Prepare = $valeur;
            $personnageSort->save();

            return response()->json(['succes' => true, 'prepare' => $personnageSort->Prepare]);
        });
    }

    public function ajouterPreparation(Request $requete, Personnage $personnage): JsonResponse
    {
        $this->verifierProprietaire($personnage);

        $sortId = (int) $requete->input('sort_id');
        if (! $sortId) {
            return response()->json(['message' => 'Identifiant de sort invalide.'], 400);
        }

        $estMagicien = $this->estMagicien($personnage);
        $valeur = $this->normaliserPreparation($requete->input('prepare', self::NON_PREPARE), $estMagicien);

        $lien = PersonnageSort::query()
            ->where('id_Personnage', $personnage->id)
            ->where('id_Sort', $sortId)
            ->first();
        $niveauSort = (int) (Sort::find($sortId)?->Niveau ?? 0);

        if ($estMagicien && $valeur === self::PREPARE && $niveauSort > 0 && (! $lien || (int) $lien->Prepare === self::NON_PREPARE)) {
            return response()->json(['message' => 'Ce sort doit être inscrit dans le grimoire pour être préparé.'], 400);
        }

        if ($lien && ! $estMagicien && (int) $lien->Prepare === self::SPECIAL) {
            return response()->json(['message' => 'Ce sort est préparé par la classe et ne peut pas être modifié.'], 403);
        }

        return DB::transaction(function () use ($personnage, $lien, $sortId, $valeur, $niveauSort) {
            if ($valeur === self::PREPARE) {
                $erreur = $this->sorts->depassementLimite($personnage, $niveauSort, $this->sorts->limites($personnage));
                if ($erreur) {
                    return response()->json(['message' => $erreur], 400);
                }
            }

            $lien ??= new PersonnageSort(['id_Personnage' => $personnage->id, 'id_Sort' => $sortId]);
            $lien->Prepare = $valeur;
            $lien->save();

            return response()->json(['succes' => true, 'personnage_sort_id' => $lien->id, 'prepare' => $lien->Prepare]);
        });
    }

    private function verifierProprietaire(Personnage $personnage): void
    {
        abort_unless($personnage->utilisateur_id === Auth::id(), 403);
    }

    private function estMagicien(Personnage $personnage): bool
    {
        return $personnage->classe?->nom === 'Magicien';
    }

    /** 2 (grimoire) n'a de sens que pour le Magicien ; toute autre valeur devient 0. */
    private function normaliserPreparation(mixed $valeur, bool $estMagicien): int
    {
        $valeur = (int) $valeur;

        return match (true) {
            $valeur === self::PREPARE => self::PREPARE,
            $valeur === self::SPECIAL && $estMagicien => self::SPECIAL,
            default => self::NON_PREPARE,
        };
    }
}
