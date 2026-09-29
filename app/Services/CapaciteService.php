<?php

namespace App\Services;

use App\Models\Capacite;
use App\Models\Personnage;
use Illuminate\Database\Eloquent\Collection;

/**
 * Les capacités (traits de classe) découlent entièrement de la classe, de
 * la sous-classe et du niveau du personnage : ce service calcule celles
 * auxquelles il a droit et aligne la table personnage_capacite dessus.
 */
class CapaciteService
{
    /** Capacités de la classe (et de la sous-classe, si choisie) jusqu'au niveau donné. */
    public function capacitesAttendues(Personnage $personnage, ?int $niveau = null): Collection
    {
        return $this->requete($personnage->classe_id, $personnage->sous_classe_id)
            ->where('niveau', '<=', $niveau ?? $personnage->niveau)
            ->get();
    }

    /** Capacités obtenues exactement au niveau donné. */
    public function capacitesDuNiveau(int $classeId, ?int $sousClasseId, int $niveau): Collection
    {
        return $this->requete($classeId, $sousClasseId)->where('niveau', $niveau)->get();
    }

    /**
     * Rattache au personnage les capacités auxquelles il a droit et retire
     * celles qui ne correspondent plus (changement de classe, de
     * sous-classe ou baisse de niveau).
     *
     * @return array{attached: array, detached: array, updated: array}
     */
    public function synchroniser(Personnage $personnage): array
    {
        return $personnage->capacites()->sync($this->capacitesAttendues($personnage)->modelKeys());
    }

    private function requete(int $classeId, ?int $sousClasseId)
    {
        return Capacite::query()
            ->where('classe_id', $classeId)
            ->where(function ($requeteSql) use ($sousClasseId) {
                $requeteSql->whereNull('sous_classe_id');
                if ($sousClasseId) {
                    $requeteSql->orWhere('sous_classe_id', $sousClasseId);
                }
            })
            ->orderBy('niveau')
            ->orderBy('id');
    }
}
