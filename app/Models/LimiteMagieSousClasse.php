<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LimiteMagieSousClasse extends Model
{
    protected $table = 'limite_magie_sous_classes';

    protected $fillable = [
        'id_SousClasse',
        'id_LimiteMagie',
        'Niveau',
        'AbiliteMagie',
    ];
}
