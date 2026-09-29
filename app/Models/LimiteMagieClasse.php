<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LimiteMagieClasse extends Model
{
    protected $table = 'limite_magie_classes';

    protected $fillable = [
        'classe_id',
        'limite_magie_id',
        'niveau',
        'caracteristique_magie',
    ];
}
