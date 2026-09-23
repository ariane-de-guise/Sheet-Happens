<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nom',
    'taille',
    'vitesse',
    'type_creature',
    'langues',
    'description',
    'bonus_aptitudes',
])]
class Race extends Model
{
    protected function casts(): array
    {
        return [
            'langues' => 'array',
            'bonus_aptitudes' => 'array',
        ];
    }

    public function sousRaces(): HasMany
    {
        return $this->hasMany(SousRace::class);
    }

    public function traits(): BelongsToMany
    {
        return $this->belongsToMany(TraitModel::class, 'race_trait', 'race_id', 'trait_id');
    }
}
