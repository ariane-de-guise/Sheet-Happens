<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Capacité (trait de classe) accordée par une classe, ou par une
 * sous-classe lorsque sous_classe_id est renseigné, à partir d'un niveau.
 */
#[Fillable(['classe_id', 'sous_classe_id', 'nom', 'niveau', 'description'])]
class Capacite extends Model
{
    protected $table = 'capacites';

    protected function casts(): array
    {
        return [
            'niveau' => 'integer',
        ];
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function sousClasse(): BelongsTo
    {
        return $this->belongsTo(SousClasse::class);
    }

    public function personnages(): BelongsToMany
    {
        return $this->belongsToMany(Personnage::class, 'personnage_capacite')->withTimestamps();
    }
}
