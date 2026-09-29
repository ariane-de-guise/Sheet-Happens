<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LimiteMagieSousClasse extends Model
{
    protected $table = 'limite_magie_sous_classes';

    protected $fillable = [
        'sous_classe_id',
        'limite_magie_id',
        'niveau',
        'caracteristique_magie',
    ];
}
