<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LimiteMagie extends Model
{
    protected $table = 'limite_magies';

    protected $fillable = [
        'tours_de_magie',
        'sorts_prepares',
        'emplacements_niveau_1',
        'emplacements_niveau_2',
        'emplacements_niveau_3',
    ];
}
