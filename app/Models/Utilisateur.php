<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    protected $fillable = ['courriel', 'mot_de_passe'];

    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'mot_de_passe' => 'hashed',
        ];
    }

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }
}
