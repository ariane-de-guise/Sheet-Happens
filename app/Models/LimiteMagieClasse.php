<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LimiteMagieClasse extends Model
{
    protected $table = 'limite_magie_classes';

    protected $fillable = [
        'id_Classe',
        'id_LimiteMagie',
        'Niveau',
    ];
}
