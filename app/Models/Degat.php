<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Degat extends Model
{
    protected $table = 'degats';

    protected $fillable = [
        'nom',
    ];

    public function sorts(): HasMany
    {
        return $this->hasMany(Sort::class, 'degat_id');
    }
}
