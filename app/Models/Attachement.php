<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['historique_id', 'valeur', 'alignement', 'description'])]
class Attachement extends Model
{
    protected $table = 'attachements';

    public function historique(): BelongsTo
    {
        return $this->belongsTo(Historique::class);
    }
}
