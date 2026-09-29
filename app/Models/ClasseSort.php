<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClasseSort extends Model
{
    protected $table = 'classe_sorts';

    protected $fillable = [
        'classe_id',
        'sort_id',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function sort()
    {
        return $this->belongsTo(Sort::class, 'sort_id');
    }
}
