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
    'langues',
    'description',
    'bonus_abilite1',
    'bonus_abilite2',
    'bonus_abilite3',
])]
class Race extends Model
{
    protected function casts(): array
    {
        return ['langues' => 'array'];
    }

    public function sousRaces(): HasMany
    {
        return $this->hasMany(SousRace::class);
    }
}
