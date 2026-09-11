<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nom', 'taille', 'vitesse', 'type_creature', 'description'])]
class Race extends Model
{
    public function sousRaces(): HasMany
    {
        return $this->hasMany(SousRace::class);
    }
}
