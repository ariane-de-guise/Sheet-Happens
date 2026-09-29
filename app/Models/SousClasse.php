<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['classe_id', 'nom', 'description'])]
class SousClasse extends Model
{
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function capacites(): HasMany
    {
        return $this->hasMany(Capacite::class);
    }
}
