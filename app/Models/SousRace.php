<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'race_id',
    'nom',
    'description',
    'bonus_aptitudes',
])]
class SousRace extends Model
{
    protected function casts(): array
    {
        return ['bonus_aptitudes' => 'array'];
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function traits(): BelongsToMany
    {
        return $this->belongsToMany(TraitModel::class, 'sous_race_trait', 'sous_race_id', 'trait_id');
    }
}
