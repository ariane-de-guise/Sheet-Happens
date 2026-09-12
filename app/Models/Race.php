<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nom', 'taille', 'vitesse', 'type_creature', 'description', 'bonus_aptitudes'])]
class Race extends Model
{
    protected function casts(): array
    {
        return ['bonus_aptitudes' => 'array'];
    }


    public function sousRaces(): HasMany
    {
        return $this->hasMany(SousRace::class);
    }
}
