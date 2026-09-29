<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nom', 'de_de_vie', 'description', 'niveau_sous_classe', 'competences_disponibles', 'nombre_competences', 'bonus_aptitudes'])]
class Classe extends Model
{
    protected function casts(): array
    {
        return [
            'competences_disponibles' => 'array',
            'bonus_aptitudes' => 'array',
        ];
    }

    public function sousClasses(): HasMany
    {
        return $this->hasMany(SousClasse::class);
    }

    /** Capacités propres à la classe (hors sous-classes). */
    public function capacites(): HasMany
    {
        return $this->hasMany(Capacite::class)->whereNull('sous_classe_id');
    }
}
