<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SousClasseSort extends Model
{
    protected $table = 'sous_classe_sorts';

    protected $fillable = [
        'sous_classe_id',
        'sort_id',
    ];
}
