<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LimiteMagie extends Model
{
    protected $table = 'limite_magies';

    protected $fillable = [
        'TourMagie',
        'SortConnue',
        'Niveau1',
        'Niveau2',
        'Niveau3',
    ];
}
