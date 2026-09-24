<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnageSort extends Model
{
    protected $table = 'personnage_sorts';

    protected $fillable = [
        'id_Personnage',
        'id_Sort',
        'Prepare',
    ];

    public function sort()
    {
        return $this->belongsTo(Sort::class, 'id_Sort');
    }

    public function personnage()
    {
        return $this->belongsTo(Personnage::class, 'id_Personnage');
    }
}
