<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['utilisateur_id', 'nom', 'description', 'type', 'magique', 'rarete', 'categorie', 'cout_mains', 'ca_base', 'bonus', 'bonus_toujours_actif', 'est_consommable', 'effet_utilisation', 'degats', 'caracteristique_degats', 'type_degats'])]
class Objet extends Model
{
    /** Types d'objets reconnus par le catalogue. */
    public const TYPES = ['arme', 'armure', 'bouclier', 'potion', 'outil', 'autre'];

    /** Raretés reconnues pour un objet magique (5e SRD). */
    public const RARETES = ['commun', 'peu_commun', 'rare', 'tres_rare', 'legendaire', 'artefact'];

    protected function casts(): array
    {
        return [
            'magique' => 'boolean',
            'bonus' => 'array',
            'bonus_toujours_actif' => 'boolean',
            'est_consommable' => 'boolean',
            'effet_utilisation' => 'array',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}
