<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['classe_id', 'nom', 'description', 'niveau_sous_classe'])]
class SousClasse extends Model
{
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }
}