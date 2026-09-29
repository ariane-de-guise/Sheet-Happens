<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnageSort extends Model
{
    protected $table = 'personnage_sorts';

    protected $fillable = [
        'personnage_id',
        'sort_id',
        'preparation',
    ];

    public function sort()
    {
        return $this->belongsTo(Sort::class, 'sort_id');
    }

    public function personnage()
    {
        return $this->belongsTo(Personnage::class, 'personnage_id');
    }
}
