<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TraitModel extends Model
{

    /**
     * Nom de la table associée au modèle.
     * Requis car Laravel déduirait "trait_models" au pluriel.
     */
    protected $table = 'traits';

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'niveau',
        'description',
    ];

    /**
     * Typage automatique des attributs.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'niveau' => 'integer',
    ];

    /**
     * Exemple de relation : races qui possèdent ce trait.
     */
    public function races(): BelongsToMany
    {
        return $this->belongsToMany(Race::class, 'race_trait', 'trait_id', 'race_id');
    }
}
