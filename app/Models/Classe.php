<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nom', 'de_de_vie', 'description', 'niveau_sous_classe'])]
class Classe extends Model
{
    public function sousClasses(): HasMany
    {
        return $this->hasMany(SousClasse::class);
    }
}
