<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nom', 'description', 'or_depart', 'competences', 'langues', 'equipement_depart'])]
class Historique extends Model
{
    protected $table = 'historiques';

    protected function casts(): array
    {
        return [
            'competences' => 'array',
            'langues' => 'array',
            'equipement_depart' => 'array',
        ];
    }

    public function ideaux(): HasMany
    {
        return $this->hasMany(Ideal::class, 'historique_id');
    }

    public function defauts(): HasMany
    {
        return $this->hasMany(Defaut::class, 'historique_id');
    }

    public function attachements(): HasMany
    {
        return $this->hasMany(Attachement::class, 'historique_id');
    }

    public function personnalites(): HasMany
    {
        return $this->hasMany(Personnalite::class, 'historique_id');
    }
}
