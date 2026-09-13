<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['force', 'dexterite', 'constitution', 'intelligence', 'sagesse', 'charisme'])]
class Abilite extends Model {}
