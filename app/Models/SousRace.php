<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'race_id',
    'nom',
    'description',
    'bonus_abilite1',
    'bonus_abilite2',
])]
class SousRace extends Model
{
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}