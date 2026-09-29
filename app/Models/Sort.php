<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sort extends Model
{
    protected $table = 'sorts';

    protected $fillable = [
        'degat_id',
        'nom',
        'nom_anglais',
        'description',
        'niveau',
        'temps_activation',
        'duree',
        'ecole',
        'distance',
        'attaque_sauvegarde',
        'composants',
    ];

    public function degat(): BelongsTo
    {
        return $this->belongsTo(Degat::class, 'degat_id');
    }
}
