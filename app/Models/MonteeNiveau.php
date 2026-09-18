<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['personnage_id', 'niveau_depart', 'niveau_arrivee', 'choix', 'changements'])]
class MonteeNiveau extends Model
{
    protected $table = 'montees_niveau';

    protected function casts(): array
    {
        return ['choix' => 'array', 'changements' => 'array'];
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }
}
