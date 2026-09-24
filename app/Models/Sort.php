<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sort extends Model
{
    protected $table = 'sorts';

    protected $fillable = [
        'id_Degat',
        'Nom',
        'NomAnglais',
        'Description',
        'Niveau',
        'TempsActivation',
        'Duree',
        'Ecole',
        'Distance',
        'AttaqueSauvegarde',
        'Composants',
    ];

    public function degat(): BelongsTo
    {
        return $this->belongsTo(Degat::class, 'id_Degat');
    }
}
