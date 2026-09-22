<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Table pivot de `Personnage::objets()`, mais avec sa propre clé primaire
 * `id` (plutôt qu'une clé composite) : elle est donc aussi manipulée comme
 * un modèle à part entière (voir InventaireController), pour cibler une
 * ligne d'inventaire précise par son id dans les routes/formulaires.
 */
#[Fillable(['personnage_id', 'objet_id', 'quantite', 'equipe'])]
class PersonnageObjet extends Pivot
{
    protected $table = 'personnage_objets';

    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'equipe' => 'boolean',
        ];
    }

    public function personnage(): BelongsTo
    {
        return $this->belongsTo(Personnage::class);
    }

    public function objet(): BelongsTo
    {
        return $this->belongsTo(Objet::class);
    }
}
