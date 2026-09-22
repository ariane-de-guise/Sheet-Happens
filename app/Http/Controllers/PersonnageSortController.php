<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use App\Models\PersonnageSort;
use App\Models\ClasseSort;
use App\Models\SousClasseSort;
use App\Models\Sort;
use App\Models\LimiteMagie;
use App\Models\LimiteMagieClasse;
use App\Models\LimiteMagieSousClasse;
use Illuminate\View\View;

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
            // si le personnage possède déjà ce sort, utiliser la valeur enregistrée
            if (isset($owned[$id])) {
                $prepare = (int) $owned[$id]->Prepare;
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
            ]);
        }

        // séparer les sorts de niveau 0 (cantrips) pour la boîte "Tour magie"
        $level0 = $available->filter(fn($s) => (int) ($s->niveau ?? 1) === 0)->values();

        // déterminer niveaux à afficher (ex : 1..max)
        $maxLevel = $available->max('niveau') ?: 3;
        $levels = range(1, max(3, (int) $maxLevel));

        // Calculer les limites de magie : on préfère la sous-classe si elle
        // définit des limites pour le niveau courant, sinon on prend la
        // limite de la classe.
        $limiteTour = null;
        $limiteSort = null;
        $slots = null;
        $niveauPerso = (int) $personnage->niveau;
        $lm = null;

        if ($personnage->sous_classe_id) {
            $sousLimite = LimiteMagieSousClasse::query()
                ->where('id_SousClasse', $personnage->sous_classe_id)
                ->where('Niveau', '<=', $niveauPerso)
                ->orderByDesc('Niveau')
                ->first();

            if ($sousLimite) {
                $lm = LimiteMagie::find($sousLimite->id_LimiteMagie);
            }
        }

        if (! $lm) {
            $classeLimite = LimiteMagieClasse::query()
                ->where('id_Classe', $personnage->classe_id)
                ->where('Niveau', '<=', $niveauPerso)
                ->orderByDesc('Niveau')
                ->first();

            if ($classeLimite) {
                $lm = LimiteMagie::find($classeLimite->id_LimiteMagie);
            }
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

        return view('personnages.sorts.index', [
            'personnage' => $personnage,
            'available' => $available,
            'levels' => $levels,
            'level0' => $level0,
            'limiteTour' => $limiteTour,
            'limiteSort' => $limiteSort,
            'slots' => $slots,
        ]);
    }
}
