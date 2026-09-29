<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nom', 'resume', 'description', 'stat', 'choix_stat', 'effets'])]
class Don extends Model
{
    protected $table = 'dons';

    protected function casts(): array
    {
        return [
            'choix_stat' => 'array',
            'effets' => 'array',
        ];
    }

    public function personnages(): BelongsToMany
    {
        return $this->belongsToMany(Personnage::class, 'personnage_don')->withTimestamps();
    }
}
