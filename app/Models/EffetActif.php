<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['personnage_id', 'objet_id', 'nom', 'bonus', 'unite', 'tours_restants', 'expire_a'])]
class EffetActif extends Model
{
    protected $table = 'effets_actifs';

    protected function casts(): array
    {
        return [
            'bonus' => 'array',
            'tours_restants' => 'integer',
            'expire_a' => 'datetime',
        ];
    }

    /**
     * Un effet en minutes expire réellement (horodatage, vérifié à chaque
     * calcul) ; un effet en tours n'expire que lorsque le joueur en a
     * décompté le dernier lui-même (voir InventaireService::ajusterTours()) —
     * pas de correspondance fiable avec le temps réel écoulé à table.
     */
    public function estExpire(): bool
    {
        return $this->unite === 'tours'
            ? $this->tours_restants <= 0
            : $this->expire_a->isPast();
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    public function objet(): BelongsTo
    {
        return $this->belongsTo(Objet::class);
    }
}
