<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nom',
    'taille',
    'vitesse',
    'type_creature',
    'description',
    'bonus_abilite1',
    'bonus_abilite2',
    'bonus_abilite3',
])]
class Race extends Model
{
    public function sousRaces(): HasMany
    {
        return $this->hasMany(SousRace::class);
    }
}
