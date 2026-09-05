<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['competences'])]
class ListeCompetence extends Model
{
    protected function casts(): array
    {
        return ['competences' => 'array'];
    }
}