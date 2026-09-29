<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClasseSort extends Model
{
    protected $table = 'classe_sorts';

    protected $fillable = [
        'id_Classe',
        'id_Sort',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'id_Classe');
    }

    public function sort()
    {
        return $this->belongsTo(Sort::class, 'id_Sort');
    }
}
