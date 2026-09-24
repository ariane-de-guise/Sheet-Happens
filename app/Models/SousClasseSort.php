<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SousClasseSort extends Model
{
    protected $table = 'sousclasse_sorts';

    protected $fillable = [
        'id_SousClasse',
        'id_Sort',
    ];
}
