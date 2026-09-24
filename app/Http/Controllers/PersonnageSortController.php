<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\PersonnageSort;
use App\Models\ClasseSort;
use App\Models\SousClasseSort;
use App\Models\Sort;
use App\Models\Classe;
use App\Models\LimiteMagie;
use App\Models\LimiteMagieClasse;
use App\Models\LimiteMagieSousClasse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonnageSortController extends Controller
{
    public function index(Personnage $personnage): View
    {
       // $this->authorizeOwner($personnage);

        // Sorts déjà appris par le personnage
        $owned = PersonnageSort::query()
            ->where('id_Personnage', $personnage->id)
            ->with('sort')
            ->get()
            ->keyBy('id_Sort');

        // Sorts de la classe
        $classeSortIds = ClasseSort::query()
            ->where('id_Classe', $personnage->classe_id)
            ->pluck('id_Sort')
            ->unique()
            ->values()
            ->toArray();

        // Sorts de la sous-classe (si applicable)
        $sousSortIds = [];
        if ($personnage->sous_classe_id) {
            $sousSortIds = SousClasseSort::query()
                ->where('id_SousClasse', $personnage->sous_classe_id)
                ->pluck('id_Sort')
                ->unique()
                ->values()
                ->toArray();
        }

        // Certaines sous-classes qui gagnent la magie (ex: Filou Arcanique, Chevalier Eldritch)
        // n'ont pas de liste fixe de sorts et doivent utiliser la liste du Magicien.
        $subclassesUsingWizardList = [12, 23];
        if ($personnage->sous_classe_id && in_array((int) $personnage->sous_classe_id, $subclassesUsingWizardList, true)) {
            $magicienId = Classe::where('nom', 'Magicien')->value('id');
            if ($magicienId) {
                $magicienSortIds = ClasseSort::query()
                    ->where('id_Classe', $magicienId)
                    ->pluck('id_Sort')
                    ->unique()
                    ->values()
                    ->toArray();

                // merge wizard sorts into class sorts so they appear available
                $classeSortIds = array_values(array_unique(array_merge($classeSortIds, $magicienSortIds)));
            }
        }

        $allIds = array_values(array_unique(array_merge($classeSortIds, $sousSortIds)));

        $sortModels = Sort::query()->whereIn('id', $allIds)->get()->keyBy('id');

        // Construire une collection de sorts disponibles avec origine et valeur de préparation
        $available = collect();
        foreach ($allIds as $id) {
            $sort = $sortModels->get($id);
            if (! $sort) {
                continue;
            }

            $origine = in_array($id, $sousSortIds, true) ? 'sous_classe' : 'classe';

            $prepare = 0;
            $personnageSortId = null;
            // si le personnage possède déjà ce sort, utiliser la valeur enregistrée
            if (isset($owned[$id])) {
                $ownedModel = $owned[$id];
                $prepare = (int) $ownedModel->Prepare;
                $personnageSortId = $ownedModel->id;
                $origine = 'personnage';
            } elseif ($origine === 'sous_classe') {
                // Les sorts obtenus via une sous-classe sont préparés par défaut à 2
                $prepare = 2;
            }

            $available->push((object) [
                'sort' => $sort,
                'origine' => $origine,
                'prepare' => $prepare,
                'niveau' => $sort->Niveau ?? 1,
                'personnage_sort_id' => $personnageSortId,
            ]);
        }

        // séparer les sorts de niveau 0 (cantrips) pour la boîte "Tour magie"
        $level0 = $available->filter(fn($s) => (int) ($s->niveau ?? 1) === 0)->values();

        // déterminer niveaux à afficher (ex : 1..max)
        $maxLevel = $available->max('niveau') ?: 3;
        $levels = range(1, max(3, (int) $maxLevel));

        // Limites de magie : limite_magie_classes si la classe est magique,
        // sinon limite_magie_sous_classes si la sous-classe l'est.
        $limitsForView = $this->getLimitsForPersonnage($personnage);
        $limiteTour = $limitsForView['limiteTour'];
        $limiteSort = $limitsForView['limiteSort'];
        $slots = $limitsForView['slots'];
        $abiliteMagie = $limitsForView['abiliteMagie'] ?? null;
        $abiliteSource = $limitsForView['abiliteSource'] ?? null;

        return view('personnages.sorts.index', [
            'personnage' => $personnage,
            'available' => $available,
            'levels' => $levels,
            'level0' => $level0,
            'limiteTour' => $limiteTour,
            'limiteSort' => $limiteSort,
            'slots' => $slots,
            'abiliteMagie' => $abiliteMagie,
            'abiliteSource' => $abiliteSource,
        ]);
    }

    public function updatePrepare(Request $request, Personnage $personnage, PersonnageSort $personnageSort)
    {
        // Vérifier que le PersonnageSort appartient bien au personnage
        if ((int) $personnageSort->id_Personnage !== (int) $personnage->id) {
            return response()->json(['message' => 'Mauvaise ressource'], 403);
        }

        $isMagicien = $this->isMagicien($personnage);
        $value = $this->normalizePrepare($request->input('prepare', 0), $isMagicien);

        // Do not allow changing items prepared by subclass (2) — for Magicien, 2 = inscrit au grimoire
        if (! $isMagicien && (int) $personnageSort->Prepare === 2) {
            return response()->json(['message' => 'Ce sort est préparé par la classe et ne peut pas être modifié.'], 403);
        }

        $niveauSort = (int) ($personnageSort->sort->Niveau ?? 0);
        if ($isMagicien && $value === 1 && $niveauSort > 0 && (int) $personnageSort->Prepare === 0) {
            return response()->json(['message' => 'Ce sort doit être inscrit dans le grimoire pour être préparé.'], 400);
        }

        // If enabling prepare, check limits with pessimistic lock to avoid races
        if ($value === 1) {
                // lock personnage row to serialize concurrent prepare changes
                DB::table('personnages')->where('id', $personnage->id)->lockForUpdate()->first();
            $limits = $this->getLimitsForPersonnage($personnage);
            // fallback: if limiteSort is null, compute as niveau + modificateur abilite
            if ($limits['limiteSort'] === null) {
                $incantationMap = [
                    'Clerc' => 'sagesse',
                    'Magicien' => 'intelligence',
                    'Druide' => 'sagesse',
                    'Barde' => 'charisme',
                    'Paladin' => 'charisme',
                    'Rôdeur' => 'sagesse',
                    'Ensorceleur' => 'charisme',
                    'Occultiste' => 'charisme',
                ];
                // prefer ability provided by limits (may come from subclass)
                $abiliteNom = $limits['abiliteMagie'] ?? null;
                if (empty($abiliteNom)) {
                    $nomClasse = $personnage->classe?->nom ?? null;
                    $abiliteNom = $incantationMap[$nomClasse] ?? null;
                }
                $abiliteKey = $abiliteNom ? strtolower($abiliteNom) : null;
                $abiliteScore = $abiliteKey ? ($personnage->abilites?->{$abiliteKey} ?? null) : null;
                $modificateur = $abiliteScore !== null ? (int) floor(($abiliteScore - 10) / 2) : 0;
                $limits['limiteSort'] = (int) $personnage->niveau + $modificateur;
            }
            $niveau = (int) ($personnageSort->sort->Niveau ?? 0);

            return DB::transaction(function () use ($personnage, $personnageSort, $value, $limits, $niveau) {
                if ($niveau === 0) {
                    if ($limits['limiteTour'] !== null) {
                        $count = DB::table('personnage_sorts')
                            ->join('sorts', 'personnage_sorts.id_Sort', '=', 'sorts.id')
                            ->where('personnage_sorts.id_Personnage', $personnage->id)
                            ->where('personnage_sorts.Prepare', 1)
                            ->where('sorts.Niveau', 0)
                            ->lockForUpdate()
                            ->count();

                        if ($count >= $limits['limiteTour']) {
                            return response()->json(['message' => 'Limite de sorts cantrips préparés atteinte.'], 400);
                        }
                    }
                } else {
                    if (in_array($niveau, [1,2,3], true) && $limits['limiteSort'] !== null) {
                        $count = DB::table('personnage_sorts')
                            ->join('sorts', 'personnage_sorts.id_Sort', '=', 'sorts.id')
                            ->where('personnage_sorts.id_Personnage', $personnage->id)
                            ->where('personnage_sorts.Prepare', 1)
                            ->whereBetween('sorts.Niveau', [1,3])
                            ->lockForUpdate()
                            ->count();

                        if ($count >= $limits['limiteSort']) {
                            return response()->json(['message' => 'Limite de sorts préparés (niveaux 1-3) atteinte.'], 400);
                        }
                    }
                }

                $personnageSort->Prepare = $value;
                $personnageSort->save();

                return response()->json(['success' => true, 'prepare' => $personnageSort->Prepare]);
            });
        }

        $personnageSort->Prepare = $value;
        $personnageSort->save();

        return response()->json(['success' => true, 'prepare' => $personnageSort->Prepare]);
    }

    public function storePrepare(Request $request, Personnage $personnage)
    {
        $sortId = (int) $request->input('sort_id');
        $isMagicien = $this->isMagicien($personnage);
        $prepare = $this->normalizePrepare($request->input('prepare', 0), $isMagicien);

        if (! $sortId) {
            return response()->json(['message' => 'Invalid sort_id'], 400);
        }

        // Find existing or create new PersonnageSort
        $ps = PersonnageSort::query()
            ->where('id_Personnage', $personnage->id)
            ->where('id_Sort', $sortId)
            ->first();

        // If creating/updating to prepared, enforce limits
        $sort = \App\Models\Sort::find($sortId);
        $limits = $this->getLimitsForPersonnage($personnage);
        if ($limits['limiteSort'] === null) {
            $incantationMap = [
                'Clerc' => 'sagesse',
                'Magicien' => 'intelligence',
                'Druide' => 'sagesse',
                'Barde' => 'charisme',
                'Paladin' => 'charisme',
                'Rôdeur' => 'sagesse',
                'Ensorceleur' => 'charisme',
                'Occultiste' => 'charisme',
            ];
            // prefer ability provided by limits (may come from subclass)
            $abiliteNom = $limits['abiliteMagie'] ?? null;
            if (empty($abiliteNom)) {
                $nomClasse = $personnage->classe?->nom ?? null;
                $abiliteNom = $incantationMap[$nomClasse] ?? null;
            }
            $abiliteKey = $abiliteNom ? strtolower($abiliteNom) : null;
            $abiliteScore = $abiliteKey ? ($personnage->abilites?->{$abiliteKey} ?? null) : null;
            $modificateur = $abiliteScore !== null ? (int) floor(($abiliteScore - 10) / 2) : 0;
            $limits['limiteSort'] = (int) $personnage->niveau + $modificateur;
        }

        $niveau = (int) ($sort->Niveau ?? 0);

        if ($isMagicien && $prepare === 1 && $niveau > 0 && (! $ps || (int) $ps->Prepare === 0)) {
            return response()->json(['message' => 'Ce sort doit être inscrit dans le grimoire pour être préparé.'], 400);
        }

        if ($prepare === 1) {
            // use transaction + lock to avoid concurrent inserts exceeding limit
            $result = DB::transaction(function () use ($personnage, $prepare, $niveau, $limits) {
                    // lock personnage row to serialize concurrent prepare changes
                    DB::table('personnages')->where('id', $personnage->id)->lockForUpdate()->first();
                if ($niveau === 0) {
                    if ($limits['limiteTour'] !== null) {
                        $count = DB::table('personnage_sorts')
                            ->join('sorts', 'personnage_sorts.id_Sort', '=', 'sorts.id')
                            ->where('personnage_sorts.id_Personnage', $personnage->id)
                            ->where('personnage_sorts.Prepare', 1)
                            ->where('sorts.Niveau', 0)
                            ->lockForUpdate()
                            ->count();

                        if ($count >= $limits['limiteTour']) {
                            return response()->json(['message' => 'Limite de sorts cantrips préparés atteinte.'], 400);
                        }
                    }
                } else {
                    if (in_array($niveau, [1,2,3], true) && $limits['limiteSort'] !== null) {
                        $count = DB::table('personnage_sorts')
                            ->join('sorts', 'personnage_sorts.id_Sort', '=', 'sorts.id')
                            ->where('personnage_sorts.id_Personnage', $personnage->id)
                            ->where('personnage_sorts.Prepare', 1)
                            ->whereBetween('sorts.Niveau', [1,3])
                            ->lockForUpdate()
                            ->count();

                        if ($count >= $limits['limiteSort']) {
                            return response()->json(['message' => 'Limite de sorts préparés (niveaux 1-3) atteinte.'], 400);
                        }
                    }
                }

                return null;
            });

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }
        }

        if (! $ps) {
            $ps = PersonnageSort::create([
                'id_Personnage' => $personnage->id,
                'id_Sort' => $sortId,
                'Prepare' => $prepare,
            ]);
        } else {
            // protect subclass-prepared (Magicien: 2 = grimoire, modifiable)
            if (! $isMagicien && (int) $ps->Prepare === 2) {
                return response()->json(['message' => 'Ce sort est préparé par la classe et ne peut pas être modifié.'], 403);
            }

            $ps->Prepare = $prepare;
            $ps->save();
        }

        return response()->json(['success' => true, 'personnage_sort_id' => $ps->id, 'prepare' => $ps->Prepare]);
    }

    private function isMagicien(Personnage $personnage): bool
    {
        return ($personnage->classe?->nom ?? null) === 'Magicien';
    }

    /**
     * 0 = non préparé, 1 = préparé, 2 = inscrit au grimoire (Magicien uniquement).
     */
    private function normalizePrepare($input, bool $isMagicien): int
    {
        $value = (int) $input;

        if ($value === 1) {
            return 1;
        }

        return ($isMagicien && $value === 2) ? 2 : 0;
    }

    private function getLimitsForPersonnage(Personnage $personnage): array
    {
        $limiteTour = null;
        $limiteSort = null;
        $abiliteSource = null;

        $slots = null;
        $abiliteMagie = null;

        $niveauPerso = (int) $personnage->niveau;

        $lm = null;
        $limiteRow = null;

        // 1) La classe est magique : utiliser limite_magie_classes
        $classeEstMagique = LimiteMagieClasse::query()
            ->where('id_Classe', $personnage->classe_id)
            ->exists();

        if ($classeEstMagique) {
            $limiteRow = LimiteMagieClasse::query()
                ->where('id_Classe', $personnage->classe_id)
                ->where('Niveau', '<=', $niveauPerso)
                ->orderByDesc('Niveau')
                ->first();

            if ($limiteRow) {
                $abiliteSource = 'classe';
            }
        } elseif ($personnage->sous_classe_id) {
            // 2) Classe non magique mais sous-classe magique : utiliser limite_magie_sous_classes
            $limiteRow = LimiteMagieSousClasse::query()
                ->where('id_SousClasse', $personnage->sous_classe_id)
                ->where('Niveau', '<=', $niveauPerso)
                ->orderByDesc('Niveau')
                ->first();

            if ($limiteRow) {
                $abiliteSource = 'sous_classe';
            }
        }

        if ($limiteRow) {
            $lm = LimiteMagie::find($limiteRow->id_LimiteMagie);
            // AbiliteMagie est stockée sur la table de liaison, pas sur limite_magies
            $abiliteMagie = $limiteRow->AbiliteMagie ?? null;
        }

        if ($lm) {
            $limiteTour = $lm->TourMagie;
            $limiteSort = $lm->SortConnue;
            $slots = [
                'Niveau1' => $lm->Niveau1,
                'Niveau2' => $lm->Niveau2,
                'Niveau3' => $lm->Niveau3,
            ];
        }

        return [
            'limiteTour' => $limiteTour !== null ? (int) $limiteTour : null,
            'limiteSort' => $limiteSort !== null ? (int) $limiteSort : null,
            'slots' => $slots,
            'abiliteMagie' => $abiliteMagie,
            'abiliteSource' => $abiliteSource,
        ];
    }
}
