<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nom', 'de_de_vie', 'description', 'niveau_sous_classe', 'competences_disponibles', 'nombre_competences'])]
class Classe extends Model
{
    protected function casts(): array
    {
        return ['competences_disponibles' => 'array'];
    }

    public function sousClasses(): HasMany
    {
        return $this->hasMany(SousClasse::class);
    }
}
