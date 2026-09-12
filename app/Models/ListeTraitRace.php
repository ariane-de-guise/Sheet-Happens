<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListeTraitRace extends Model
{
    protected $table = 'liste_traits';

    // The list contains many traits
    public function traitsRace(): BelongsToMany
    {
        return $this->belongsToMany(
            TraitRace::class,
            'liste_trait_items',
            'liste_trait_id',
            'id_traits'
        );
    }
}
