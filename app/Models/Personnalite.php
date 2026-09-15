<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['historique_id', 'valeur', 'description'])]
class Personnalite extends Model
{
    protected $table = 'personnalites';

    public function historique(): BelongsTo
    {
        return $this->belongsTo(Historique::class);
    }
}
